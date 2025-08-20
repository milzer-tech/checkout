<?php

return [
    'gender' => [
        'male' => 'Hombre',
        'female' => 'Mujer',
    ],
    'availability' => [
        'NoAvailability' => 'el componente ya no está disponible.',
        'DateTimeConflict' => 'el componente de vuelo tiene una fecha/hora que entra en conflicto con otra conexión.',
        'PaxSelectionConflict' => 'el componente de vuelo tiene una selección de pasajeros que entra en conflicto con otro vuelo.',
        'Cancelled' => 'el componente ha sido cancelado.',
        'NonFittingToItinerary' => 'el componente ya no está alineado con la estructura actual del itinerario.',
        'NonFittingLocations' => 'las ubicaciones de inicio y/o fin del componente no están alineadas con las ubicaciones del itinerario inmediatamente antes y/o después. Esto solo se marca como advertencia y puede que no sea realmente un problema para la reserva.',
        'NonFittingButAccepted' => 'indica que este componente no encaja pero ha sido marcado por el usuario como aceptado para la reserva de todos modos.',
        'NoTicketsSelected' => 'este componente necesita que se asignen billetes individuales para poder reservarse.',
        'UnscheduledComponentNoLocationFound' => 'indica que el componente no se puede programar actualmente debido a una estructura de itinerario incorrecta.',
        'Unknown' => 'Desconocido',
    ],
];
