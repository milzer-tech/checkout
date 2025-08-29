<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Payments\Gateways\Oppwa;

use Illuminate\Http\Request;
use Nezasa\Checkout\Dtos\BaseDto;
use Nezasa\Checkout\Integrations\Oppwa\Connectors\OppwaConnector;
use Nezasa\Checkout\Payments\Contracts\PaymentCallBack;
use Nezasa\Checkout\Payments\Contracts\ReturnUrlHasInvalidQueryParamsForValidation;
use Nezasa\Checkout\Payments\Dtos\PaymentOutput;
use Nezasa\Checkout\Payments\Dtos\PaymentResult;
use Nezasa\Checkout\Payments\Enums\PaymentGatewayEnum;
use Nezasa\Checkout\Payments\Enums\PaymentStatusEnum;
use Throwable;

final class OppwaCallBack implements PaymentCallBack, ReturnUrlHasInvalidQueryParamsForValidation
{
    public function check(Request $request, BaseDto|array $persistentData): PaymentResult
    {
        try {
            $response = OppwaConnector::make()->checkout()->status($request->query('resourcePath'));

            return new PaymentResult(
                gateway: PaymentGatewayEnum::Oppwa,
                status: $response->failed() ? PaymentStatusEnum::Failed : PaymentStatusEnum::Succeeded,
                persistentData: (array) $response->array(),
            );
        } catch (Throwable $exception) {
            report($exception);

            return new PaymentResult(gateway: PaymentGatewayEnum::Oppwa, status: PaymentStatusEnum::Failed);
        }
    }

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
