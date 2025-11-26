<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Enums;

use AzimKordpour\PowerEnum\Traits\PowerEnum;

/**
 * @method bool isSelect()
 * @method bool isText()
 * @method bool isRadio()
 * @method bool isUnknown()
 * @method bool isCheckbox()
 */
enum AnswerInputEnum: string
{
    use PowerEnum;

    case Select = 'select';
    case Text = 'text';
    case Radio = 'radio';
    case Checkbox = 'checkbox';
    case Unknown = 'unknown';
}
