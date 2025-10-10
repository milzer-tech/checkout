<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Payments\Contracts;

use Illuminate\Http\Request;
use Nezasa\Checkout\Dtos\BaseDto;
use Nezasa\Checkout\Payments\Dtos\PaymentOutput;
use Nezasa\Checkout\Payments\Dtos\PaymentResult;

interface WidgetPaymentCallBack
{
    /**
     * Handles the callback from the payment gateway.
     *
     * @param  array<string, mixed>|BaseDto  $persistentData
     */
    public function check(Request $request, array|BaseDto $persistentData): PaymentResult;

    /**
     * Shows the result of the payment process to the user.
     */
    public function show(PaymentResult $result, PaymentOutput $output): PaymentOutput;
}
