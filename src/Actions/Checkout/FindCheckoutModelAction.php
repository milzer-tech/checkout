<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Actions\Checkout;

use Nezasa\Checkout\Dtos\Checkout\CheckoutParamsDto;
use Nezasa\Checkout\Exceptions\AlreadyPaidException;
use Nezasa\Checkout\Exceptions\CheckoutInUseException;
use Nezasa\Checkout\Models\Checkout;
use Nezasa\Checkout\Payments\Enums\TransactionStatusEnum;
use Nezasa\Checkout\Support\CheckoutOwnership;

class FindCheckoutModelAction
{
    /**
     * Create a new instance of FindCheckoutModelAction.
     */
    public function __construct(private readonly CheckoutOwnership $ownership) {}

    /**
     * Find existing checkout model or throw exception if already paid or open in another browser
     *
     * @throws AlreadyPaidException|CheckoutInUseException|\Throwable
     */
    public function run(CheckoutParamsDto $params): ?Checkout
    {
        $model = Checkout::query()
            ->where('checkout_id', $params->checkoutId)
            ->where('itinerary_id', $params->itineraryId)
            ->where('rest_payment', $params->restPayment)
            ->first();

        if ($model) {
            throw_if(
                condition: $model->transactions()->whereStatus(TransactionStatusEnum::Captured)->exists(),
                exception: AlreadyPaidException::class
            );

            throw_if(
                condition: $this->ownership->isInUseElsewhere($model),
                exception: CheckoutInUseException::class
            );
        }

        return $model;
    }
}
