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
}
