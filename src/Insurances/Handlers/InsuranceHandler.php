<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Insurances\Handlers;

use Exception;
use Nezasa\Checkout\Actions\Insurance\GetActiveInsuranceAction;
use Nezasa\Checkout\Dtos\Planner\ItinerarySummary;
use Nezasa\Checkout\Insurances\Contracts\InsuranceContract;
use Nezasa\Checkout\Insurances\Dtos\CreateInsuranceOffersDto;
use Nezasa\Checkout\Insurances\Dtos\InsuranceOfferDto;
use Nezasa\Checkout\Integrations\Nezasa\Connectors\NezasaConnector;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\AddCustomInsurancePayload;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Shared\Price;
use Nezasa\Checkout\Integrations\Nezasa\Enums\AvailabilityEnum;
use Nezasa\Checkout\Models\Checkout;
use Nezasa\Checkout\Models\Transaction;
use Throwable;

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
     * @return false|array<int, InsuranceOfferDto>
     */
    public function createOffers(Checkout $model, ItinerarySummary $itinerary): false|array
    {
        $createOffersDto = new CreateInsuranceOffersDto(
            startDate: $itinerary->startDate->toImmutable(),
            endDate: $itinerary->endDate->toImmutable(),
            totalPrice: $itinerary->price->showTotalPrice,
            contact: $model->getContact(),
            paxInfo: $model->getPaxInfo(),
            destinationCountries: $itinerary->destinationCountries,
        );

        $result = $this->getActiveInsuranceAction->run()->getOffers($createOffersDto);

        $model->updateData([
            'insurance_meta' => $result->meta,
            'insurance_create_offer' => $createOffersDto->toArray(),
        ]);

        return $result->isSuccessful ? $result->offers : false;
    }

    public function bookOffer(Transaction $transaction) {}

    private function saveInsuranceOnNezasa(Transaction $transaction): void
    {
        try {
            $insurance = data_get($transaction->result_data, 'insurance_purchase');

            if (isset($insurance['id'])) {
                $response = NezasaConnector::make()->checkout()->addCustomInsurance(
                    checkoutId: $transaction->checkout->checkout_id,
                    payload: new AddCustomInsurancePayload(
                        name: $insurance['product']['promotional_header'],
                        netPrice: new Price(intval($insurance['total']) / 100, $insurance['currency']),
                        salesPrice: new Price(intval($insurance['total']) / 100, $insurance['currency']),
                        bookingStatus: AvailabilityEnum::Booked,
                        supplierName: 'ViCoverage',
                        supplierConfirmationNumber: $insurance['policy_number'],
                        description: $insurance['policy_number']
                    )
                );

                $transaction->update([
                    'result_data' => $transaction->result_data + ['nezasa_insurance_response' => $response->array()],
                ]);
            }
        } catch (Throwable $e) {
            $transaction->update([
                'result_data' => $transaction->result_data + ['nezasa_insurance_response' => 'could not be saved'],
            ]);

            throw $e;
        }
    }
}
