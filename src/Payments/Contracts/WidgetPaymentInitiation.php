<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Payments\Contracts;

use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\CreatePaymentTransactionPayload as NezasaPayload;
use Nezasa\Checkout\Payments\Dtos\PaymentAsset;
use Nezasa\Checkout\Payments\Dtos\PaymentInit;
use Nezasa\Checkout\Payments\Dtos\PaymentPrepareData;

interface WidgetPaymentInitiation
{
    /**
     * Returns whether the payment gateway is active.
     */
    public static function isActive(): bool;

    /**
     * Returns the name of the payment gateway.
     */
    public static function name(): string;

    /**
     * Returns the description of the payment gateway.
     * if null, no description will be shown.
     */
    public static function description(): ?string;

    /**
     * Prepares the payment initiation process.
     */
    public function prepare(PaymentPrepareData $data): PaymentInit;

    /**
     * Returns the assets required for the payment initiation process.
     */
    public function getAssets(PaymentInit $paymentInit, string $returnUrl): PaymentAsset;

    /**
     * Returns the payload required for creating a transaction in Nezasa.
     */
    public function getNezasaTransactionPayload(PaymentPrepareData $data, PaymentInit $paymentInit): NezasaPayload;
}
