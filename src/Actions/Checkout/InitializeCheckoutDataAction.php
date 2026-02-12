<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Actions\Checkout;

use Nezasa\Checkout\Dtos\Checkout\CheckoutParamsDto;
use Nezasa\Checkout\Facades\AvailabilityFacade;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities\PaxAllocationResponseEntity;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities\RoomAllocationResponseEntity;
use Nezasa\Checkout\Models\Checkout;

class InitializeCheckoutDataAction
{
    /**
     * Create a new instance of InitializeCheckoutDataAction.
     */
    public function __construct(private readonly FindCheckoutModelAction $findCheckoutModelAction) {}

    /**
     * Create or find existing checkout model and initialize the data if created.
     */
    public function run(CheckoutParamsDto $params, PaxAllocationResponseEntity $allocatedPax): Checkout
    {
        $model = $this->findCheckoutModelAction->run(params: $params);

        if (! $model instanceof Checkout) {
            $model = Checkout::query()->create($params->mapToModel());

            $this->firstConfiguration($allocatedPax, $model);
        } else {
            $this->visitedConfiguration($model);
        }

        AvailabilityFacade::clearCache(params: $params);

        return $model;
    }

    private function visitedConfiguration(Checkout $model): void
    {
        $data = $model->data;

        $data['insurance'] = null;
        $data['status'] = Checkout::buildSectionStatus();

        $model->update(['data' => $data]);
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
                'status' => Checkout::buildSectionStatus(),
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
}
