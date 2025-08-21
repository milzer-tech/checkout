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

final class OppwaCallBack implements PaymentCallBack, ReturnUrlHasInvalidQueryParamsForValidation
{
    public function check(Request $request, BaseDto|array $persistentData): PaymentResult
    {
        $response = OppwaConnector::make()->checkout()->status($request->query('resourcePath'));

        return new PaymentResult(
            gateway: PaymentGatewayEnum::Oppwa,
            status: $response->failed() ? PaymentStatusEnum::Failed : PaymentStatusEnum::Succeeded,
            persistentData: $response->array() ?? [],
        );
    }

    public function show(PaymentResult $result, PaymentOutput $output): PaymentOutput
    {
        return $output;
    }

    public function addedParamsToReturnedUrl(Request $request): array
    {
        return [
            'resourcePath',
            'id',
        ];
    }
}
