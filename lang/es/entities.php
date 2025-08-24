<?php

return [
    'gender' => [
        'male' => 'Hombre',
        'female' => 'Mujer',
    ],
    'availability' => [
        'NoAvailability' => 'el componente ya no está disponible.',
        'DateTimeConflict' => 'el componente de vuelo tiene una fecha/hora que entra en conflicto con otra conexión.',
        'PaxSelectionConflict' => 'la selección de pasajeros del vuelo entra en conflicto con otro vuelo.',
        'Cancelled' => 'el componente ha sido cancelado.',
        'NonFittingToItinerary' => 'el componente ya no se ajusta a la estructura actual del itinerario.',
        'NonFittingLocations' => 'las ubicaciones de inicio y/o fin del componente no están alineadas con las ubicaciones del itinerario inmediatamente antes y/o después. Esto solo se indica como advertencia y puede que no sea un problema real para la reserva.',
        'NonFittingButAccepted' => 'indica que este componente no encaja, pero aun así el usuario lo ha marcado como aceptado para la reserva.',
        'NoTicketsSelected' => 'este componente necesita que se asignen billetes individuales para poder reservarse.',
        'UnscheduledComponentNoLocationFound' => 'indica que el componente no puede programarse actualmente debido a una estructura de itinerario incorrecta.',
        'Unknown' => 'Desconocido',
    ],
];
