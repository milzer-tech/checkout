<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Payments\Contracts;

use Nezasa\Checkout\Payments\Dtos\PaymentAsset;
use Nezasa\Checkout\Payments\Dtos\PaymentInit;
use Nezasa\Checkout\Payments\Dtos\PaymentPrepareData;

interface PaymentInitiation
{
    public function prepare(PaymentPrepareData $data): PaymentInit;

    public function getAssets(PaymentInit $paymentInit, string $returnUrl): PaymentAsset;
}
