<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Actions\Checkout;

use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities\PaxAllocationResponseEntity;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities\RoomAllocationResponseEntity;
use Nezasa\Checkout\Models\Checkout;

class InitializeCheckoutDataAction
{
    public function run(
        string $checkoutId,
        PaxAllocationResponseEntity $allocatedPax
    ): void {
        $model = Checkout::query()->firstOrCreate(['checkout_id' => $checkoutId]);

        $numberOfPax = $allocatedPax->rooms->sum(
            fn (RoomAllocationResponseEntity $room) => $room->adults + $room->childAges->count()
        );

        if (! $model->data) {
            $model->data = [];
        }

        $model->data['numberOfPax'] = $numberOfPax;

        $model->save();
    }
}
