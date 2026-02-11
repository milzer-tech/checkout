<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Payments\Enums;

use AzimKordpour\PowerEnum\Traits\PowerEnum;

/**
 * Enum representing different payment statuses.
 *
 * @method bool isPending()
 * @method bool isAuthorized()
 * @method bool isAuthorizationFailed()
 * @method bool isCaptured()
 * @method bool isCaptureFailed()
 * @method bool isAborted()
 * @method bool isAbortFailed()
 * @method bool isCanceled()
 */
enum TransactionStatusEnum: int
{
    use PowerEnum;

    case Pending = 1;
    case Authorized = 2;
    case AuthorizationFailed = 3;
    case Captured = 4;
    case CaptureFailed = 5;
    case Aborted = 6;
    case AbortFailed = 7;
    case Canceled = 8;
}
