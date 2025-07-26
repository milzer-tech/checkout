<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses;

use Nezasa\Checkout\Dtos\BaseDto;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities\PromoCodeResponseEntity;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Shared\Price;

class ApplyPromoCodeResponse extends BaseDto
{
    /**
     * Create a new instance of ApplyPromoCodeResponse.
     */
    public function __construct(
        public Price $discountedPackagePrice,
        public Price $packagePrice,
        public ?PromoCodeResponseEntity $promoCode = null,
    ) {}

    /**
     * Calculate the percentage decrease in price due to the promo code.
     */
    public function decreasePercent(): float
    {
        return (($this->packagePrice->amount - $this->discountedPackagePrice->amount) / $this->packagePrice->amount) * 100;
    }

    /**
     * Calculate the amount decreased by the promo code.
     */
    public function decreaseAmount(): float
    {
        return $this->packagePrice->amount - $this->discountedPackagePrice->amount;
    }
}
