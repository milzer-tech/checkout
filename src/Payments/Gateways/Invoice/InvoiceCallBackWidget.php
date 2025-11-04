<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Payments\Gateways\Invoice;

use Illuminate\Http\Request;
use Nezasa\Checkout\Dtos\BaseDto;
use Nezasa\Checkout\Payments\Contracts\WidgetPaymentCallBack;
use Nezasa\Checkout\Payments\Dtos\PaymentOutput;
use Nezasa\Checkout\Payments\Dtos\PaymentResult;
use Nezasa\Checkout\Payments\Enums\PaymentStatusEnum;

final class InvoiceCallBackWidget implements WidgetPaymentCallBack
{
    public function check(Request $request, BaseDto|array $persistentData): PaymentResult
    {
        return new PaymentResult(
            status: $request->input('key') === $persistentData['id']
                ? PaymentStatusEnum::Succeeded
                : PaymentStatusEnum::Failed,
            persistentData: $persistentData
        );
    }

    public function show(PaymentResult $result, PaymentOutput $output): PaymentOutput
    {
        return $output;
    }
}
