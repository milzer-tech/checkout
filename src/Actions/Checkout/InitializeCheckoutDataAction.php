<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Actions\Checkout;

use Nezasa\Checkout\Enums\Section;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities\PaxAllocationResponseEntity;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities\RoomAllocationResponseEntity;
use Nezasa\Checkout\Models\Checkout;

class InitializeCheckoutDataAction
{
    /**
     * Create or find existing checkout model and initialize the data if created.
     */
    public function run(string $checkoutId, string $itineraryId, PaxAllocationResponseEntity $allocatedPax): Checkout
    {
        $model = resolve(FindCheckoutModelAction::class)->run($checkoutId, $itineraryId);

        if (! $model) {
            $model = Checkout::create(['checkout_id' => $checkoutId, 'itinerary_id' => $itineraryId]);

            $this->firstConfiguration($allocatedPax, $model);
        }

        return $model;
    }

    /**
     * Initialize the checkout data on first creation.
     */
    private function firstConfiguration(PaxAllocationResponseEntity $allocatedPax, Checkout $checkout): void
    {
        $checkout->data = [
            'paxInfo' => [],
            'contact' => [],
            'activityAnswers' => [],
            'numberOfPax' => $this->countPaxes($allocatedPax),
            'allocatedPax' => $allocatedPax,
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
                Section::Insurance->value => [
                    'isExpanded' => false,
                    'isCompleted' => false,
                ],
                Section::Activity->value => [
                    'isExpanded' => false,
                    'isCompleted' => false,
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
            fn (RoomAllocationResponseEntity $room): int => $room->adults + $room->childAges->count()
        );
    }
}
