<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Payments\Contracts;

use Illuminate\Support\Uri;
use Nezasa\Checkout\Payments\Dtos\PaymentInit;

interface RedirectPaymentContract extends PaymentContract
{
    /**
     * The url to the payment gateway.
     */
    public function getRedirectUrl(PaymentInit $init): Uri;
}
