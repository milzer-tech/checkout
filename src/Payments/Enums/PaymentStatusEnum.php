<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Payments\Enums;

use AzimKordpour\PowerEnum\Traits\PowerEnum;

/**
 * Enum representing different payment statuses.
 *
 * @method bool isStarted()
 * @method bool isPending()
 * @method bool isSucceeded()
 * @method bool isFailed()
 * @method bool isCanceled()
 */
enum PaymentStatusEnum: int
{
    use PowerEnum;

    case Started = 1;
    case Pending = 2;
    case Succeeded = 3;
    case Failed = 4;
    case Canceled = 5;
}
