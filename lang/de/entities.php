<?php

return [
    'gender' => [
        'male' => 'Männlich',
        'female' => 'Weiblich',
    ],
    'availability' => [
        'NoAvailability' => 'die Komponente ist nicht mehr verfügbar.',
        'DateTimeConflict' => 'die Flugkomponente hat ein Datum/eine Uhrzeit, die mit einer anderen Verbindung kollidiert.',
        'PaxSelectionConflict' => 'die Passagierauswahl des Fluges steht in Konflikt mit einem anderen Flug.',
        'Cancelled' => 'die Komponente wurde storniert.',
        'NonFittingToItinerary' => 'die Komponente passt nicht mehr zur aktuellen Struktur der Reiseroute.',
        'NonFittingLocations' => 'die Start- und/oder Endorte der Komponente stimmen nicht mit den Orten der Reiseroute unmittelbar davor und/oder danach überein. Dies wird nur als Warnung gekennzeichnet und stellt möglicherweise kein tatsächliches Problem für die Buchung dar.',
        'NonFittingButAccepted' => 'zeigt an, dass diese Komponente nicht passt, aber vom Benutzer dennoch für die Buchung als akzeptiert markiert wurde.',
        'NoTicketsSelected' => 'für diese Komponente müssen einzelne Tickets zugewiesen werden, damit sie gebucht werden kann.',
        'UnscheduledComponentNoLocationFound' => 'weist darauf hin, dass die Komponente derzeit aufgrund einer fehlerhaften Reiseroutenstruktur nicht eingeplant werden kann.',
        'Unknown' => 'Unbekannt',
    ],
];
