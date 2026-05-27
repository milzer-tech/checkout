<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Insurances\Handlers;

use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Nezasa\Checkout\Actions\Insurance\GetActiveInsuranceAction;
use Nezasa\Checkout\Actions\Payment\CreateNezasaTransactionAction;
use Nezasa\Checkout\Dtos\Planner\ItinerarySummary;
use Nezasa\Checkout\Insurances\Contracts\InsuranceContract;
use Nezasa\Checkout\Insurances\Dtos\BookInsuranceOfferDto;
use Nezasa\Checkout\Insurances\Dtos\CreateInsuranceOffersDto;
use Nezasa\Checkout\Insurances\Dtos\InsuranceBookOfferResult;
use Nezasa\Checkout\Insurances\Dtos\InsuranceOfferDto;
use Nezasa\Checkout\Insurances\Dtos\InsuranceOffersResult;
use Nezasa\Checkout\Insurances\Dtos\InsurancePaymentFieldDto;
use Nezasa\Checkout\Insurances\InsuranceCheckoutData;
use Nezasa\Checkout\Integrations\Nezasa\Connectors\NezasaConnector;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\AddCustomInsurancePayload;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\CreatePaymentTransactionPayload;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Shared\Price;
use Nezasa\Checkout\Integrations\Nezasa\Enums\AvailabilityEnum;
use Nezasa\Checkout\Models\Checkout;
use Nezasa\Checkout\Models\Transaction;

final readonly class InsuranceHandler
{
    public function __construct(
        private GetActiveInsuranceAction $getActiveInsuranceAction,
        private CreateNezasaTransactionAction $createNezasaTransactionAction,
    ) {}

    /**
     * Indicate if any insurance provider is active.
     *
     * @throws Exception
     */
    public function isAvailable(): bool
    {
        return $this->getActiveInsuranceAction->run() instanceof InsuranceContract
            || Config::boolean('checkout.insurance.vertical.active');
    }

    /**
     * Get payment data fields required by the active insurance provider.
     *
     * @return array<int, InsurancePaymentFieldDto>
     */
    public function getPaymentFields(): array
    {
        return $this->getActiveInsuranceAction->run()?->getPaymentFields() ?? [];
    }

    public function getNoSelectionText(): string
    {
        return $this->getActiveInsuranceAction->run()?->getNoSelectionText()
            ?? trans('checkout::page.trip_details.insurance_no_insurance_option');
    }

    public function getProviderName(): ?string
    {
        $insurance = $this->getActiveInsuranceAction->run();

        return $insurance instanceof InsuranceContract
            ? $insurance::getName()
            : null;
    }

    public function getProviderLogo(): ?string
    {
        $insurance = $this->getActiveInsuranceAction->run();

        return $insurance instanceof InsuranceContract
            ? $insurance::getLogo()
            : null;
    }

    /**
     * Indicates if the active provider's selected offer price is paid through the main payment gateway.
     */
    public function shouldAddOfferPriceToPayment(): bool
    {
        if (Config::boolean('checkout.insurance.vertical.active')) {
            return false;
        }

        return $this->getActiveInsuranceAction->run()?->shouldAddOfferPriceToPayment() ?? false;
    }

    /**
     * Add the selected insurance offer price to a payment amount only when the active provider
     * collects the offer through the main checkout payment.
     *
     * @param  Collection<string, mixed>|array<string, mixed>  $checkoutData
     */
    public function paymentPriceWithSelectedOffer(Price $paymentPrice, Collection|array $checkoutData): Price
    {
        $offer = InsuranceCheckoutData::getOffer(InsuranceCheckoutData::checkoutDataArray($checkoutData));
        if (! data_get($offer, 'price') || ! $this->shouldAddOfferPriceToPayment()) {
            return new Price($paymentPrice->amount, $paymentPrice->currency);
        }

        return new Price(
            amount: $paymentPrice->amount + Price::from($offer['price'])->amount,
            currency: $paymentPrice->currency,
        );
    }

    /**
     * Get the provider-owned notice for a selected offer paid outside the main checkout payment.
     */
    public function separatePaymentNoticeForSelectedOffer(InsuranceOfferDto $selectedOffer): ?string
    {
        if (Config::boolean('checkout.insurance.vertical.active') || $this->shouldAddOfferPriceToPayment()) {
            return null;
        }

        return $this->getActiveInsuranceAction->run()?->getSeparatePaymentNotice($selectedOffer);
    }

    /**
     * Create insurance offers.
     */
    public function createOffers(Checkout $model, ItinerarySummary $itinerary): InsuranceOffersResult
    {
        $createOffersDto = new CreateInsuranceOffersDto(
            startDate: $itinerary->startDate->toImmutable(),
            endDate: $itinerary->endDate->toImmutable(),
            totalPrice: $itinerary->price->showTotalPrice,
            contact: $model->getContact(),
            paxInfo: $model->getPaxInfo(),
            destinationCountries: $itinerary->destinationCountries instanceof Collection
                ? $itinerary->destinationCountries
                : collect($itinerary->destinationCountries),
        );

        try {
            $result = $this->getActiveInsuranceAction->run()->getOffers($createOffersDto);

            $checkoutArr = InsuranceCheckoutData::checkoutDataArray($model->data);
            $bucket = InsuranceCheckoutData::getNormalizedInsuranceBucket($checkoutArr)
                ?? InsuranceCheckoutData::emptyInsuranceBucket();
            $bucket[InsuranceCheckoutData::META] = $result->meta;
            $bucket[InsuranceCheckoutData::CREATE_OFFER] = $createOffersDto->toArray();

            $model->updateData(InsuranceCheckoutData::prepareInsuranceUpdate($bucket));

            return $result;
        } catch (Exception $e) {
            report($e);

            return new InsuranceOffersResult(isSuccessful: false);
        }
    }

    /**
     * Book the selected insurance offer.
     */
    public function bookOffer(Transaction $transaction): void
    {
        $data = InsuranceCheckoutData::checkoutDataArray($transaction->checkout->data);

        $offerArr = InsuranceCheckoutData::getOffer($data);
        if (! is_array($offerArr)) {
            throw new \RuntimeException('Checkout insurance offer is missing.');
        }

        $createArr = InsuranceCheckoutData::getCreateOffer($data);
        if (! is_array($createArr)) {
            throw new \RuntimeException('Checkout insurance create_offer context is missing.');
        }

        $rawMeta = InsuranceCheckoutData::getMeta($data);

        $selectedOffer = InsuranceOfferDto::from($offerArr);
        $result = $this->getActiveInsuranceAction->run()->bookOffer(
            bookOfferDto: new BookInsuranceOfferDto(
                selectedOffer: $selectedOffer,
                createdOfferDto: CreateInsuranceOffersDto::from($createArr),
                meta: is_array($rawMeta) ? $rawMeta : [],
                payment: InsuranceCheckoutData::getPayment($data),
            )
        );

        $transaction->pushToResultData(['insurance' => $result->toArray()]);

        $this->recordInsuranceBooking($result, $transaction, $selectedOffer);
    }

    /**
     * Record the insurance booking in Nezasa.
     */
    public function recordInsuranceBooking(InsuranceBookOfferResult $result, Transaction $transaction, InsuranceOfferDto $selectedOffer): void
    {
        if ($result->isSuccessful) {
            $insurance = $this->getActiveInsuranceAction->run();
            $nezasa = NezasaConnector::make()->checkout()->addCustomInsurance(
                checkoutId: $transaction->checkout->checkout_id,
                payload: $insurance->getNezasaPayload(
                    payload: new AddCustomInsurancePayload(
                        name: $selectedOffer->title,
                        netPrice: $selectedOffer->price,
                        salesPrice: $selectedOffer->price,
                        bookingStatus: AvailabilityEnum::Booked,
                        supplierName: $insurance->getName(),
                        supplierConfirmationNumber: $result->confirmationId,
                    )
                )
            );

            $transaction->pushToResultData([
                'nezasa_insurance_request' => (array) $nezasa->getPendingRequest()->body()->all(),
                'nezasa_insurance_response' => $nezasa->array(),
            ]);

            if (! $insurance->shouldAddOfferPriceToPayment()) {
                $payload = $insurance->makeNezasaPaymentTransactionPayload($transaction, $selectedOffer, $result);

                if ($payload instanceof CreatePaymentTransactionPayload) {
                    $paymentTransaction = $this->createNezasaTransactionAction->run(
                        checkoutId: $transaction->checkout->checkout_id,
                        payload: $payload,
                    );

                    $transaction->pushToResultData([
                        'nezasa_insurance_payment_request' => $payload->toArray(),
                        'nezasa_insurance_payment_response' => $paymentTransaction,
                    ]);
                }
            }
        }
    }
}
