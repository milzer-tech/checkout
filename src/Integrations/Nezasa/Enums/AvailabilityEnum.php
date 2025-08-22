<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Enums;

use AzimKordpour\PowerEnum\Traits\PowerEnum;

/**
 * Enum representing the availability status of a component in the checkout process.
 *
 * @see https://docs.tripbuilder.app/Mo9reezaehiengah/checkout-api-v1.html#tag/Availability/paths/~1checkout~1v1~1checkouts~1%7BcheckoutId%7D~1availability-check/post
 *
 * @method bool isOpen()
 * @method bool isOnRequest()
 * @method bool isBooked()
 * @method bool isNoneBookable()
 * @method bool isCancelled()
 * @method bool isNone()
 */
enum AvailabilityEnum: string
{
    use PowerEnum;

    case Open = 'Open';
    case OnRequest = 'OnRequest';
    case Booked = 'Booked';
    case NoneBookable = 'NoneBookable';
    case Cancelled = 'Cancelled';
    case None = 'None';

    public function isBookable(): bool
    {
        return $this->isOpen() || $this->isOnRequest();
    }
}
