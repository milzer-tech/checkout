<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Enums;

use AzimKordpour\PowerEnum\Traits\PowerEnum;

/**
 * Enum representing the required traveler field status.
 *
 * @see https://docs.tripbuilder.app/Mo9reezaehiengah/checkout-api-v1.html#tag/Traveler-Information/paths/~1checkout~1v1~1checkouts~1%7BcheckoutId%7D~1required-traveler-details/get
 *
 * @method bool isRequired()
 * @method bool isOptional()
 * @method bool isHidden()
 */
enum RequiredTravelerFieldEnum: string
{
    use PowerEnum;

    case Required = 'required';
    case Optional = 'optional';
    case Hidden = 'hidden';
}
