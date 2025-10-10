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
