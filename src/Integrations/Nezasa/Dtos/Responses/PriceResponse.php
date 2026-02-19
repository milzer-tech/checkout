<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses;

use Nezasa\Checkout\Dtos\BaseDto;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities\ExternallyPaidChargesResponseEntity;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities\PromoCodeResponseEntity;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Shared\Price;

class PriceResponse extends BaseDto
{
    /**
     * Create a new instance of ApplyPromoCodeResponse.
     */
    public function __construct(
        public Price $discountedPackagePrice,
        public Price $packagePrice,
        public Price $totalPackagePrice,
        public Price $downPayment,
        public Price $openAmount,
        public ExternallyPaidChargesResponseEntity $externallyPaidCharges,
        public ?PromoCodeResponseEntity $promoCode = null,

        // These two properties are used to show the total price and the payment price in the checkout page.
        // As the total price can be sum up with the insurance and other prices out of Nezasa's control, we need to have
        // a way to show the correct price in the checkout page.
        public ?Price $showTotalPrice = null,
        public ?Price $showPaymentPrice = null,
    ) {
        if (! $this->showTotalPrice instanceof Price) {
            $this->showTotalPrice = $this->discountedPackagePrice;
        }

        if (! $this->showPaymentPrice instanceof Price) {
            $this->showPaymentPrice = $this->downPayment;
        }
    }

    /**
     * Calculate the percentage decrease in price due to the promo code.
     */
    public function decreasePercent(): float
    {
        if ($this->discountedPackagePrice->amount > 0 && $this->packagePrice->amount > 0) {
            return round(
                (($this->packagePrice->amount - $this->discountedPackagePrice->amount) / $this->packagePrice->amount) * 100
            );
        }

        return 0;
    }

    /**
     * Calculate the amount decreased by the promo code.
     */
    public function decreaseAmount(): float
    {
        return $this->packagePrice->amount - $this->discountedPackagePrice->amount;
    }

    /**
     * percentage of the total price that is down payment.
     */
    public function downPercentOfTotal(): float
    {
        return round(
            ($this->showPaymentPrice->amount / $this->showTotalPrice->amount) * 100
        );
    }
}
