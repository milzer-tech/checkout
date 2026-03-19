<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\HanseMerkur\Enums;

use AzimKordpour\PowerEnum\Traits\PowerEnum;

/**
 * Enum representing the gender options for traveler information in the HanseMerkur Checkout API.
 *
 * @method bool isMale()
 * @method bool isFemale()
 * @method bool isChild()
 * @method bool isCompany()
 */
enum HanseMerkurGenderEnum: string
{
    use PowerEnum;

    case Male = 'MALE';
    case Female = 'FEMALE';
    case Child = 'CHILD';
    case Company = 'COMPANY';
}
