<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Payments\Enums;

use AzimKordpour\PowerEnum\Traits\PowerEnum;

/**
 * Enum representing different payment gateways.
 *
 * @method bool isOppwa()
 */
enum PaymentGatewayEnum: int
{
    use PowerEnum;

    case Oppwa = 1;

    /**
     * Specifies if the payment gateway is a widget.
     */
    public function isWidget(): bool
    {
        return match ($this) {
            self::Oppwa => true,
            default => false,
        };
    }
}
