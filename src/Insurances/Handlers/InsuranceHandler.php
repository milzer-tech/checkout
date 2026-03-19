<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Insurances\Handlers;

use Exception;
use Nezasa\Checkout\Actions\Insurance\GetActiveInsuranceAction;
use Nezasa\Checkout\Dtos\Planner\ItinerarySummary;
use Nezasa\Checkout\Insurances\Contracts\InsuranceContract;
use Nezasa\Checkout\Insurances\Dtos\BookInsuranceOfferDto;
use Nezasa\Checkout\Insurances\Dtos\CreateInsuranceOffersDto;
use Nezasa\Checkout\Insurances\Dtos\InsuranceBookOfferResult;
use Nezasa\Checkout\Insurances\Dtos\InsuranceOfferDto;
use Nezasa\Checkout\Insurances\Dtos\InsuranceOffersResult;
use Nezasa\Checkout\Integrations\Nezasa\Connectors\NezasaConnector;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\AddCustomInsurancePayload;
use Nezasa\Checkout\Integrations\Nezasa\Enums\AvailabilityEnum;
use Nezasa\Checkout\Models\Checkout;
use Nezasa\Checkout\Models\Transaction;

final class InsuranceHandler
{
    public function __construct(private GetActiveInsuranceAction $getActiveInsuranceAction) {}

    /**
     * Indicate if any insurance provider is active.
     *
     * @throws Exception
     */
    public function isAvailable(): bool
    {
        return $this->getActiveInsuranceAction->run() instanceof InsuranceContract;
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
            destinationCountries: $itinerary->destinationCountries,
        );

        try {
            $result = $this->getActiveInsuranceAction->run()->getOffers($createOffersDto);

            $model->updateData([
                'insurance_meta' => $result->meta,
                'insurance_create_offer' => $createOffersDto->toArray(),
            ]);

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
        $data = $transaction->checkout->data;

        $selectedOffer = InsuranceOfferDto::from($data['insurance']);
        $result = $this->getActiveInsuranceAction->run()->bookOffer(
            bookOfferDto: new BookInsuranceOfferDto(
                selectedOffer: $selectedOffer,
                createdOfferDto: CreateInsuranceOffersDto::from($data['insurance_create_offer']),
                meta: $data['insurance_meta'],
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
            $nezasa = NezasaConnector::make()->checkout()->addCustomInsurance(
                checkoutId: $transaction->checkout->checkout_id,
                payload: $this->getActiveInsuranceAction->run()->getNezasaPayload(
                    payload: new AddCustomInsurancePayload(
                        name: $selectedOffer->title,
                        netPrice: $selectedOffer->price,
                        salesPrice: $selectedOffer->price,
                        bookingStatus: AvailabilityEnum::Booked,
                        supplierName: $this->getActiveInsuranceAction->run()->getName(),
                        supplierConfirmationNumber: $result->confirmationId,
                    )
                )
            );

            $transaction->pushToResultData(['nezasa_insurance_response' => $nezasa->array()]);
        }
    }
}
