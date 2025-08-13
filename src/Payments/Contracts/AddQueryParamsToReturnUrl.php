<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Payments\Contracts;

use Nezasa\Checkout\Payments\Dtos\PaymentInit;

interface AddQueryParamsToReturnUrl
{
    public function addQueryParamsToReturnUrl(PaymentInit $paymentInit): array;
}
