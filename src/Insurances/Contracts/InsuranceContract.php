<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Insurances\Contracts;

use Nezasa\Checkout\Insurances\Dtos\BookInsuranceOfferDto;
use Nezasa\Checkout\Insurances\Dtos\CreateInsuranceOffersDto;
use Nezasa\Checkout\Insurances\Dtos\InsuranceBookOfferResult;
use Nezasa\Checkout\Insurances\Dtos\InsuranceOfferDto;
use Nezasa\Checkout\Insurances\Dtos\InsuranceOffersResult;
use Nezasa\Checkout\Insurances\Dtos\InsurancePaymentFieldDto;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\AddCustomInsurancePayload;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\CreatePaymentTransactionPayload;
use Nezasa\Checkout\Models\Transaction;

interface InsuranceContract
{
    /**
     * Indicates if the insurance is active.
     */
    public static function isActive(): bool;

    /**
     * The name of the insurance provider.
     * We use this name to populate the Nezasa payload or display it in the checkout process.
     */
    public static function getName(): string;

    /**
     * The logo URL or data URI for the insurance provider.
     */
    public static function getLogo(): ?string;

    /**
     * Payment data fields required before booking this provider's insurance offer.
     *
     * @return array<int, InsurancePaymentFieldDto>
     */
    public function getPaymentFields(): array;

    /**
     * Text shown when the customer declines insurance for the trip.
     */
    public function getNoSelectionText(): string;

    /**
     * Indicates if the selected insurance offer price is paid through the main payment gateway.
     */
    public function shouldAddOfferPriceToPayment(): bool;

    /**
     * Optional notice shown in the trip summary when this offer is paid outside the main checkout payment.
     */
    public function getSeparatePaymentNotice(InsuranceOfferDto $selectedOffer): ?string;

    /**
     * Builds a separate Nezasa payment transaction when the offer price is not paid through the main payment gateway.
     */
    public function makeNezasaPaymentTransactionPayload(
        Transaction $transaction,
        InsuranceOfferDto $selectedOffer,
        InsuranceBookOfferResult $result
    ): ?CreatePaymentTransactionPayload;

    /**
     * Creates insurance offers.
     */
    public function getOffers(CreateInsuranceOffersDto $createOffersDto): InsuranceOffersResult;

    /**
     * Book an insurance offer.
     */
    public function bookOffer(BookInsuranceOfferDto $bookOfferDto): InsuranceBookOfferResult;

    /**
     * In case if you need to customize data sending to Nezasa, you can do it here.
     */
    public function getNezasaPayload(AddCustomInsurancePayload $payload): AddCustomInsurancePayload;
}
