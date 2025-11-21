<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Enums;

use AzimKordpour\PowerEnum\Traits\PowerEnum;

/**
 * Enum representing the validation types for answer fields.
 *
 * @method bool isInt()
 * @method bool isDouble()
 * @method bool isBoolean()
 * @method bool isDate()
 * @method bool isDateAndTime()
 * @method bool isPhone()
 */
enum AnswerValidationEnum: string
{
    use PowerEnum;

    case Int = 'int';
    case Double = 'double';
    case Boolean = 'boolean';
    case Date = 'date';
    case DateAndTime = 'dateAndTime';
    case Phone = 'Phone';
}
