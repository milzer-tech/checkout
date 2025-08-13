<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Payments\Contracts;

use Nezasa\Checkout\Integrations\Nezasa\Dtos\Shared\Price;
use Nezasa\Checkout\Models\Checkout;
use Nezasa\Checkout\Payments\Dtos\PaymentAsset;
use Nezasa\Checkout\Payments\Dtos\PaymentInit;

interface PaymentInitiation
{
    public function prepare(Checkout $checkout, Price $price): PaymentInit;

    public function getAssets(PaymentInit $paymentInit, string $returnUrl): PaymentAsset;
}
