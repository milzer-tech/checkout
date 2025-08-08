<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Enums;

use AzimKordpour\PowerEnum\Traits\PowerEnum;

enum NoneBookableReasonEnum: string
{
    use PowerEnum;

    case NoAvailability = 'NoAvailability';
    case DateTimeConflict = 'DateTimeConflict';
    case PaxSelectionConflict = 'PaxSelectionConflict';
    case Cancelled = 'Cancelled';
    case NonFittingToItinerary = 'NonFittingToItinerary';
    case NonFittingLocations = 'NonFittingLocations';
    case NonFittingButAccepted = 'NonFittingButAccepted';
    case NoTicketsSelected = 'NoTicketsSelected';
    case UnscheduledComponentNoLocationFound = 'UnscheduledComponentNoLocationFound';
    case Unknown = 'Unknown';

    /**
     * Get the labels for each enum case.
     *
     * @return array<string, string>
     */
    protected static function setLabels(): array
    {
        return [
            self::NoAvailability->value => trans('checkout::entities.availability.NoAvailability'),
            self::DateTimeConflict->value => trans('checkout::entities.availability.DateTimeConflict'),
            self::PaxSelectionConflict->value => trans('checkout::entities.availability.PaxSelectionConflict'),
            self::Cancelled->value => trans('checkout::entities.availability.Cancelled'),
            self::NonFittingToItinerary->value => trans('checkout::entities.availability.NonFittingToItinerary'),
            self::NonFittingLocations->value => trans('checkout::entities.availability.NonFittingLocations'),
            self::NonFittingButAccepted->value => trans('checkout::entities.availability.NonFittingButAccepted'),
            self::NoTicketsSelected->value => trans('checkout::entities.availability.NoTicketsSelected'),
            self::UnscheduledComponentNoLocationFound->value => trans('checkout::entities.availability.UnscheduledComponentNoLocationFound'),
            self::Unknown->value => trans('checkout::entities.availability.Unknown'),
        ];
    }
}
