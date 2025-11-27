<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Payments\Contracts;

use Illuminate\Http\Request;
use Nezasa\Checkout\Dtos\BaseDto;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\CreatePaymentTransactionPayload as NezasaPayload;
use Nezasa\Checkout\Payments\Dtos\PaymentInit;
use Nezasa\Checkout\Payments\Dtos\PaymentOutput;
use Nezasa\Checkout\Payments\Dtos\PaymentPrepareData;
use Nezasa\Checkout\Payments\Dtos\PaymentResult;

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
     * Handles the callback from the payment gateway.
     *
     * @param  array<string, mixed>|BaseDto  $persistentData
     */
    public function verify(Request $request, array|BaseDto $persistentData): PaymentResult;

    /**
     * Shows the result of the payment process to the user.
     */
    public function output(PaymentResult $result, PaymentOutput $output): PaymentOutput;
}
