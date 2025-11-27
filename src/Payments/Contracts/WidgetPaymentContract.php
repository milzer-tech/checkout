<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Payments\Contracts;

use Nezasa\Checkout\Payments\Dtos\PaymentAsset;
use Nezasa\Checkout\Payments\Dtos\PaymentInit;

interface WidgetPaymentContract extends PaymentContract
{
    /**
     * Returns the assets required for the payment initiation process.
     */
    public function getAssets(PaymentInit $paymentInit): PaymentAsset;
}
