<?php

return [
    'gender' => [
        'male' => 'Male',
        'female' => 'Female',
    ],
    'availability' => [
        'NoAvailability' => 'the component is no longer available.',
        'DateTimeConflict' => 'the flight component as a date/time that conflicts with other connection.',
        'PaxSelectionConflict' => 'the flight component has a pax selection that conflicts with another flight.',
        'Cancelled' => 'the component has been cancelled.',
        'NonFittingToItinerary' => 'the component is no longer aligned to the current itinerary structure.',
        'NonFittingLocations' => "the component's start and/or end locations are not aligned with the itinerary locations immediately before and/or after. This is only only flagged as a warning, and may not be actual an issue for the booking.",
        'NonFittingButAccepted' => "indicates that this component doesn't fit but has been marked by the user as accepted for booking nevertheless.",
        'NoTicketsSelected' => 'this component needs individual tickets to be assigned in order to be booked.',
        'UnscheduledComponentNoLocationFound' => 'indicates that the component cannot currently be scheduled due to an incorrect itinerary structure.',
        'Unknown' => 'Unknown',
    ],
];
