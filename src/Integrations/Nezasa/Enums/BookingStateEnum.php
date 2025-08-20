<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Enums;

use AzimKordpour\PowerEnum\Traits\PowerEnum;

/**
 * Enum representing the various states of a booking in the checkout process.
 *
 * @see https://docs.tripbuilder.app/Mo9reezaehiengah/booking-api-v1.html#tag/Bookings/operation/findBooking
 *
 * @method bool isBookingInitiated()
 * @method bool isBookingInProgress()
 * @method bool isBookingRequested()
 * @method bool isBookingCompleted()
 * @method bool isCancellationInitiated()
 * @method bool isCancellationInProgress()
 * @method bool isCancellationCompleted()
 * @method bool isBookingChangeInitiated()
 * @method bool isBookingChangeInProgress()
 * @method bool isBookingChangeRequested()
 * @method bool isBookingChangeCompleted()
 * @method bool isDiscarded()
 * @method bool isOptionInProgress()
 * @method bool isOptionRequested()
 * @method bool isOptionCompleted()
 * @method bool isOptionChangeInProgress()
 * @method bool isOptionChangeCompleted()
 * @method bool isOptionCancellationCompleted()
 */
enum BookingStateEnum: string
{
    use PowerEnum;

    case BookingInitiated = 'BookingInitiated';
    case BookingInProgress = 'BookingInProgress';
    case BookingRequested = 'BookingRequested';
    case BookingCompleted = 'BookingCompleted';
    case CancellationInitiated = 'CancellationInitiated';
    case CancellationInProgress = 'CancellationInProgress';
    case CancellationCompleted = 'CancellationCompleted';
    case BookingChangeInitiated = 'BookingChangeInitiated';
    case BookingChangeInProgress = 'BookingChangeInProgress';
    case BookingChangeRequested = 'BookingChangeRequested';
    case BookingChangeCompleted = 'BookingChangeCompleted';
    case Discarded = 'Discarded';
    case OptionInProgress = 'OptionInProgress';
    case OptionRequested = 'OptionRequested';
    case OptionCompleted = 'OptionCompleted';
    case OptionChangeInProgress = 'OptionChangeInProgress';
    case OptionChangeCompleted = 'OptionChangeCompleted';
    case OptionCancellationCompleted = 'OptionCancellationCompleted';

    public function isSuccessfulState(): bool
    {
        return $this->is(self::BookingRequested) || $this->is(self::BookingCompleted);
    }
}
