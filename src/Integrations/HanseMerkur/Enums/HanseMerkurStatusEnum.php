<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\HanseMerkur\Enums;

use AzimKordpour\PowerEnum\Traits\PowerEnum;

/**
 * Enum representing the status of a reservation in the HanseMerkur Checkout API.
 *
 * @link https://api-fbt.hmrv.de/rest/swagger-ui/index.html#/Booking/createBookingV1
 *
 * @method bool isConfirmed()
 * @method bool isCancelled()
 * @method bool isReserved()
 * @method bool isReservedCancelled()
 */
enum HanseMerkurStatusEnum: string
{
    use PowerEnum;

    case Confirmed = 'CONFIRMED';
    case Cancelled = 'CANCELLED';
    case Reserved = 'RESERVED';
    case ReservedCancelled = 'RESERVED_CANCELLED';
}
