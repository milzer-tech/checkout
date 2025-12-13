<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Actions\Checkout;

use Nezasa\Checkout\Dtos\Checkout\CheckoutParamsDto;
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
    public function run(CheckoutParamsDto $params): ?Checkout
    {
        $model = Checkout::query()
            ->where('checkout_id', $params->checkoutId)
            ->where('itinerary_id', $params->itineraryId)
            ->first();

        if ($model) {
            throw_if(
                condition: $model->transactions()->whereStatus(PaymentStatusEnum::Succeeded)->exists(),
                exception: AlreadyPaidException::class
            );
        }

        return $model;
    }
}
