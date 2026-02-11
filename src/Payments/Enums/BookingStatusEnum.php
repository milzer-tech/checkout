<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Payments\Enums;

use AzimKordpour\PowerEnum\Traits\PowerEnum;

/**
 * Enum representing different booking statuses.
 *
 * @method bool isCompleteSuccess()
 * @method bool isCompleteFailed()
 * @method bool isPartialFailure()
 * @method bool isUnknown()
 */
enum BookingStatusEnum: string
{
    use PowerEnum;

    case CompleteSuccess = 'CompleteSuccess';
    case CompleteFailed = 'CompleteFailed';
    case PartialFailure = 'PartialFailure';
    case Unknown = 'Unknown';

}
