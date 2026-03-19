<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Insurances\Contracts;

use Nezasa\Checkout\Insurances\Dtos\BookInsuranceOfferDto;
use Nezasa\Checkout\Insurances\Dtos\CreateInsuranceOffersDto;
use Nezasa\Checkout\Insurances\Dtos\InsuranceBookOfferResult;
use Nezasa\Checkout\Insurances\Dtos\InsuranceOffersResult;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\AddCustomInsurancePayload;

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
