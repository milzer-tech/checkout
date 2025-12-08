<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Actions\Checkout;

use Nezasa\Checkout\Enums\Section;
use Nezasa\Checkout\Facades\AvailabilityFacade;
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

        AvailabilityFacade::clearCache($checkoutId);

        return $model;
    }

    /**
     * Initialize the checkout data on first creation.
     */
    private function firstConfiguration(PaxAllocationResponseEntity $allocatedPax, Checkout $checkout): void
    {
        $data = [
            'paxInfo' => [],
            'contact' => [],
            'activityAnswers' => [],
            'acceptedTerms' => [],
            'numberOfPax' => $this->countPaxes($allocatedPax),
            'allocatedPax' => $allocatedPax,
            'status' => [],
        ];

        foreach (Section::cases() as $section) {
            $data['status'][$section->value] = ['isExpanded' => false, 'isCompleted' => false];

            if ($section->isContact()) {
                $data['status'][$section->value] = ['isExpanded' => true, 'isCompleted' => false];
            }

            if ($section->isSummary()) {
                $data['status'][$section->value] = ['isExpanded' => true, 'isCompleted' => true];
            }
        }

        $checkout->fill(['data' => $data])->save();
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
