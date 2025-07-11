<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Enums;

use AzimKordpour\PowerEnum\Traits\PowerEnum;

/**
 * Enum representing gender options for traveler information in the Nezasa Checkout API.
 *
 * @see https://docs.tripbuilder.app/Mo9reezaehiengah/checkout-api-v1.html#tag/Traveler-Information/paths/~1checkout~1v1~1checkouts~1%7BcheckoutId%7D~1traveler-details/post
 *
 * @method bool isMale()
 * @method bool isFemale()
 */
enum GenderEnum: string
{
    use PowerEnum;

    case Male = 'Male';
    case Female = 'Female';
}
