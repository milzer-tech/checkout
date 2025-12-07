<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Vertical\Dtos\Payloads\Entities;

use Nezasa\Checkout\Dtos\BaseDto;

class PurchasePaymentMethodPayloadEntity extends BaseDto
{
    /**
     * Create a new instance of PurchasePaymentMethodEntity.
     */
    public function __construct(public string $token)
    {
        if (! app()->isProduction()) {
            $this->token = 'stripe:pm_card_visa';
        }
    }

}
