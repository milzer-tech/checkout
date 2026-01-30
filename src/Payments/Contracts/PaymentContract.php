<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Payments\Contracts;

use Illuminate\Http\Request;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\CreatePaymentTransactionPayload as NezasaPayload;
use Nezasa\Checkout\Payments\Dtos\AbortResult;
use Nezasa\Checkout\Payments\Dtos\AuthorizationResult;
use Nezasa\Checkout\Payments\Dtos\CaptureResult;
use Nezasa\Checkout\Payments\Dtos\PaymentInit;
use Nezasa\Checkout\Payments\Dtos\PaymentPrepareData;

interface PaymentContract
{
    /**
     * Returns whether the payment gateway is active.
     */
    public static function isActive(): bool;

    /**
     * Returns the name of the payment gateway.
     *
     * Important: This name will be used to identify the payment gateway in the checkout process
     * and it has to be unique, please check the previous gateways' names,
     */
    public static function name(): string;

    /**
     * Prepares the payment initiation process.
     */
    public function prepare(PaymentPrepareData $data): PaymentInit;

    /**
     * Returns the payload required for creating a transaction in Nezasa.
     */
    public function makeNezasaTransactionPayload(PaymentPrepareData $data, PaymentInit $paymentInit): NezasaPayload;

    /**
     * Handles the callback from the payment gateway to authorize the payment.
     *
     * Persistent data is the data that is returned from paymentInit in the prepare method.
     *
     * @param  array<string, mixed>  $persistentData
     */
    public function authorize(Request $request, array $persistentData): AuthorizationResult;

    /**
     * Capture the authorized payment. This method is called after the payment is authorized
     * and booking itinerary call is successful.
     *
     * Persistent data is the data returned from paymentInit in the prepare method.
     *
     * @param  array<string, mixed>  $persistentData
     *
     * Result data is the data returned from AuthorizationResult's resultData property.
     * @param  array<string, mixed>  $resultData
     */
    public function capture(Request $request, array $persistentData, array $resultData): CaptureResult;

    /**
     * Abort the payment process. This method is called when the booking itinerary call fails.
     *
     * Persistent data is the data returned from paymentInit in the prepare method.
     *
     * @param  array<string, mixed>  $persistentData
     *
     * Result data is the data returned from AuthorizationResult's resultData property.
     * @param  array<string, mixed>  $resultData
     */
    public function abort(Request $request, array $persistentData, array $resultData): AbortResult;
}
