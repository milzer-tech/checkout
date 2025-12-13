<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Actions\Checkout;

use Nezasa\Checkout\Dtos\Checkout\CheckoutParamsDto;
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
    public function run(CheckoutParamsDto $params, PaxAllocationResponseEntity $allocatedPax): Checkout
    {
        $model = resolve(FindCheckoutModelAction::class)->run(params: $params);

        if (! $model) {
            $model = Checkout::query()->create($params->all());

            $this->firstConfiguration($allocatedPax, $model);
        } else {
            $this->visitedConfiguration($model);
        }

        AvailabilityFacade::clearCache(params: $params);

        return $model;
    }

    private function visitedConfiguration(Checkout $model): void
    {
        $model->updateData(['insurance' => null]);

        $model->updateData(['status' => $this->buildSectionStatus()]);
    }

    /**
     * Initialize the checkout data on first creation.
     */
    private function firstConfiguration(PaxAllocationResponseEntity $allocatedPax, Checkout $checkout): void
    {
        $checkout->update([
            'data' => [
                'paxInfo' => [],
                'contact' => [],
                'activityAnswers' => [],
                'acceptedTerms' => [],
                'numberOfPax' => $this->countPaxes($allocatedPax),
                'allocatedPax' => $allocatedPax,
                'status' => $this->buildSectionStatus(),
                'insurance' => null,
            ],
        ]);
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

    /**
     * Create the status array for the sections.
     *
     * @return array<string, array<string, bool>>
     */
    private function buildSectionStatus(): array
    {
        $data = [];

        foreach (Section::cases() as $section) {
            $data[$section->value] = ['isExpanded' => false, 'isCompleted' => false];

            if ($section->isContact()) {
                $data[$section->value] = ['isExpanded' => true, 'isCompleted' => false];
            }

            if ($section->isSummary()) {
                $data[$section->value] = ['isExpanded' => true, 'isCompleted' => true];
            }
        }

        return $data;
    }
}
