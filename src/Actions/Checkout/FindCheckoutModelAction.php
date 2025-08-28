<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Actions\Checkout;

use Nezasa\Checkout\Exceptions\AlreadyPaidException;
use Nezasa\Checkout\Models\Checkout;
use Nezasa\Checkout\Payments\Enums\PaymentStatusEnum;

class FindCheckoutModelAction
{
    /**
     * Find existing checkout model or throw exception if already paid
     *
     * @throws AlreadyPaidException
     */
    public function run(string $checkoutId, string $itineraryId): ?Checkout
    {
        $model = Checkout::whereCheckoutId($checkoutId)->whereItineraryId($itineraryId)->first();

        if ($model) {
            throw_if(
                condition: $model->transactions()->whereStatus(PaymentStatusEnum::Succeeded),
                exception: AlreadyPaidException::class
            );
        }

        return $model;
    }
}
