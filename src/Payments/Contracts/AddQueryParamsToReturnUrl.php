<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Payments\Contracts;

use Nezasa\Checkout\Payments\Dtos\PaymentInit;

interface AddQueryParamsToReturnUrl
{
    /**
     * Adds query parameters to the return URL after payment.
     *
     * @return array<string, string>
     */
    public function addQueryParamsToReturnUrl(PaymentInit $paymentInit): array;
}
