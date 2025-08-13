<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Payments\Contracts;

use Illuminate\Http\Request;
use Nezasa\Checkout\Dtos\BaseDto;
use Nezasa\Checkout\Payments\Dtos\PaymentOutput;
use Nezasa\Checkout\Payments\Dtos\PaymentResult;

interface PaymentCallBack
{
    public function check(Request $request, array|BaseDto $persistentData): PaymentResult;

    public function show(PaymentResult $result, PaymentOutput $output): PaymentOutput;
}
