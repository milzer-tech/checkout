<?php

return [
    'gender' => [
        'male' => 'Hombre',
        'female' => 'Mujer',
    ],
    'availability' => [
        'NoAvailability' => 'El componente ya no está disponible.',
        'DateTimeConflict' => 'El componente de vuelo tiene una fecha/hora que entra en conflicto con otra conexión.',
        'PaxSelectionConflict' => 'El componente de vuelo tiene una selección de pasajeros que entra en conflicto con otro vuelo.',
        'Cancelled' => 'El componente ha sido cancelado.',
        'NonFittingToItinerary' => 'El componente ya no se ajusta a la estructura actual del itinerario.',
        'NonFittingLocations' => 'Los lugares de inicio y/o fin del componente no coinciden con los del itinerario inmediatamente antes y/o después. Esto solo se señala como advertencia y puede que no sea realmente un problema para la reserva.',
        'NonFittingButAccepted' => 'Indica que este componente no encaja pero ha sido marcado por el usuario como aceptado para la reserva de todos modos.',
        'NoTicketsSelected' => 'Este componente necesita que se asignen billetes individuales para poder reservarse.',
        'UnscheduledComponentNoLocationFound' => 'Indica que el componente no se puede programar actualmente debido a una estructura de itinerario incorrecta.',
        'Unknown' => 'Desconocido',
    ],
];
