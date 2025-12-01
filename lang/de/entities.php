<?php

return [
    'gender' => [
        'male' => 'Männlich',
        'female' => 'Weiblich',
    ],
    'availability' => [
        'NoAvailability' => 'Die Komponente ist nicht mehr verfügbar.',
        'DateTimeConflict' => 'Die Flugkomponente hat ein Datum/eine Uhrzeit, die mit einer anderen Verbindung kollidiert.',
        'PaxSelectionConflict' => 'Die Flugkomponente hat eine Passagierauswahl, die mit einem anderen Flug kollidiert.',
        'Cancelled' => 'Die Komponente wurde storniert.',
        'NonFittingToItinerary' => 'Die Komponente passt nicht mehr zur aktuellen Reisestruktur.',
        'NonFittingLocations' => 'Die Start- und/oder Endorte der Komponente stimmen nicht mit den Orten direkt davor und/oder danach überein. Dies wird nur als Warnung markiert und muss nicht unbedingt ein Problem für die Buchung darstellen.',
        'NonFittingButAccepted' => 'Zeigt an, dass diese Komponente nicht passt, aber vom Benutzer dennoch für die Buchung akzeptiert wurde.',
        'NoTicketsSelected' => 'Für diese Komponente müssen einzelne Tickets zugewiesen werden, um sie buchen zu können.',
        'UnscheduledComponentNoLocationFound' => 'Zeigt an, dass die Komponente aufgrund einer inkorrekten Reisestruktur derzeit nicht eingeplant werden kann.',
        'Unknown' => 'Unbekannt',
    ],
];
