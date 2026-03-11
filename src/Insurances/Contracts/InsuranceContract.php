<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Insurances\Contracts;

use Nezasa\Checkout\Insurances\Dtos\CreateInsuranceOffersDto;
use Nezasa\Checkout\Insurances\Dtos\InsuranceOffersResult;

interface InsuranceContract
{
    public static function isActive(): bool;

    public function getOffers(CreateInsuranceOffersDto $createOffersDto): InsuranceOffersResult;
}
