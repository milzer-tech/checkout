<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Actions\Checkout;

use Nezasa\Checkout\Enums\Section;
use Nezasa\Checkout\Exceptions\AlreadyPaidException;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities\PaxAllocationResponseEntity;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities\RoomAllocationResponseEntity;
use Nezasa\Checkout\Models\Checkout;
use Nezasa\Checkout\Payments\Enums\PaymentStatusEnum;

class InitializeCheckoutDataAction
{
    public function run(string $checkoutId, string $itineraryId, PaxAllocationResponseEntity $allocatedPax): Checkout
    {
        $model = Checkout::whereCheckoutId($checkoutId)->whereItineraryId($itineraryId)->first();

        if ($model) {
            throw_if(
                condition: $model->transactions()->whereStatus(PaymentStatusEnum::Succeeded),
                exception: AlreadyPaidException::class
            );
        } else {
            $model = Checkout::create(['checkout_id' => $checkoutId, 'itinerary_id' => $itineraryId]);

            $this->firstConfiguration($allocatedPax, $model);
        }

        return $model;
    }

    private function firstConfiguration(PaxAllocationResponseEntity $allocatedPax, Checkout $checkout): void
    {
        $checkout->data = [
            'paxInfo' => [],
            'contact' => [],
            'numberOfPax' => $this->countPaxes($allocatedPax),
            'status' => [
                Section::Contact->value => [
                    'isExpanded' => true,
                    'isCompleted' => false,
                ],
                Section::Traveller->value => [
                    'isExpanded' => false,
                    'isCompleted' => false,
                ],
                Section::Promo->value => [
                    'isExpanded' => false,
                    'isCompleted' => false,
                ],
                Section::AdditionalService->value => [
                    'isExpanded' => false,
                    'isCompleted' => false,
                ],
                Section::Summary->value => [
                    'isExpanded' => true,
                    'isCompleted' => true,
                ],
                Section::PaymentOptions->value => [
                    'isExpanded' => false,
                    'isCompleted' => false,
                ],
            ],
        ];

        $checkout->save();
    }

    /**
     *  Count the total number of passengers allocated in the response.
     */
    private function countPaxes(PaxAllocationResponseEntity $allocatedPax): int
    {
        return $allocatedPax->rooms->sum(
            /** @phpstan-ignore-next-line */
            fn (RoomAllocationResponseEntity $room) => $room->adults + $room->childAges->count()
        );
    }
}
