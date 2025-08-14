<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Payments\Handlers;

use Illuminate\Http\Request;
use Nezasa\Checkout\Models\Checkout;
use Nezasa\Checkout\Payments\Contracts\PaymentCallBack;
use Nezasa\Checkout\Payments\Contracts\ReturnUrlHasInvalidQueryParamsForValidation;
use Nezasa\Checkout\Payments\Dtos\PaymentOutput;
use Nezasa\Checkout\Payments\Dtos\PaymentResult;
use Nezasa\Checkout\Payments\Enums\PaymentGatewayEnum;
use Nezasa\Checkout\Payments\Gateways\Oppwa\OppwaCallBack;

class WidgetCallBackHandler
{
    /**
     * Implementations of payment gateways.
     *
     * @var array<int, class-string<PaymentCallBack>>
     */
    private array $implementations = [
        PaymentGatewayEnum::Oppwa->value => OppwaCallBack::class,
    ];

    public function run(Checkout $model, Request $request): PaymentOutput
    {
        $this->validateGateway($model->lastestTransaction->gateway);

        $callback = new $this->implementations[$model->lastestTransaction->gateway->value];

        $this->validateReturnUrl($callback, $request);

        $result = $callback->check(request(), $model->lastestTransaction->prepare_data);

        $this->storeResult($result, $model);

        return $callback->show(
            result: $result,
            output: new PaymentOutput($result->gateway, $result->status, $result->data)
        );
    }

    private function validateGateway(PaymentGatewayEnum $gateway): void
    {
        if (! array_key_exists($gateway->value, $this->implementations)) {
            throw new \InvalidArgumentException('The payment gateway is not supported.');
        }

        if (! in_array(PaymentCallBack::class, class_implements($this->implementations[$gateway->value]))) {
            throw new \InvalidArgumentException('The payment callback is not implemented correctly.');
        }
    }

    /**
     * Validate the return URL signature.
     */
    private function validateReturnUrl(mixed $callback, Request $request): void
    {
        $igonreQuery = $callback instanceof ReturnUrlHasInvalidQueryParamsForValidation
            ? $callback->addedParamsToReturnedUrl($request)
            : [];

        if (! $request->hasValidSignatureWhileIgnoring($igonreQuery)) {
            abort(403, 'Invalid signature');
        }
    }

    /**
     * Store the result of the payment callback in the transaction.
     */
    private function storeResult(PaymentResult $result, Checkout $model): void
    {
        $model->lastestTransaction->update([
            'result_data' => $result->persistentData,
            'status' => $result->status->value,
        ]);
    }
}
