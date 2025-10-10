<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Payments\Gateways\Oppwa;

use Illuminate\Http\Request;
use Nezasa\Checkout\Dtos\BaseDto;
use Nezasa\Checkout\Integrations\Oppwa\Connectors\OppwaConnector;
use Nezasa\Checkout\Payments\Contracts\ReturnUrlHasInvalidQueryParamsForValidation;
use Nezasa\Checkout\Payments\Contracts\WidgetPaymentCallBack;
use Nezasa\Checkout\Payments\Dtos\PaymentOutput;
use Nezasa\Checkout\Payments\Dtos\PaymentResult;
use Nezasa\Checkout\Payments\Enums\PaymentStatusEnum;
use Throwable;

final class OppwaCallBackWidget implements ReturnUrlHasInvalidQueryParamsForValidation, WidgetPaymentCallBack
{
    /**
     * Check the payment status after returning from the payment gateway.
     *
     * @param  BaseDto|array<string, mixed>  $persistentData  Data that was stored before redirecting to the payment gateway.
     */
    public function check(Request $request, BaseDto|array $persistentData): PaymentResult
    {
        try {
            $response = OppwaConnector::make()->checkout()->status($request->query('resourcePath'));

            return new PaymentResult(
                gatewayName: OppwaInitiationWidget::name(),
                status: $response->failed() ? PaymentStatusEnum::Failed : PaymentStatusEnum::Succeeded,
                persistentData: (array) $response->array(),
            );
        } catch (Throwable $exception) {
            report($exception);

            return new PaymentResult(gatewayName: OppwaInitiationWidget::name(), status: PaymentStatusEnum::Failed);
        }
    }

    /**
     * Show the payment result to the user.
     */
    public function show(PaymentResult $result, PaymentOutput $output): PaymentOutput
    {
        return $output;
    }

    /**
     * Returns the list of query parameters that were added to the return URL after payment.
     *
     * @return array<string>
     */
    public function addedParamsToReturnedUrl(Request $request): array
    {
        return [
            'resourcePath',
            'id',
        ];
    }
}
