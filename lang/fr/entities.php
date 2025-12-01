<?php

return [
    'gender' => [
        'male' => 'Homme',
        'female' => 'Femme',
    ],
    'availability' => [
        'NoAvailability' => "Le composant n'est plus disponible.",
        'DateTimeConflict' => 'Le composant de vol a une date/heure qui entre en conflit avec une autre correspondance.',
        'PaxSelectionConflict' => 'Le composant de vol a une sélection de passagers qui entre en conflit avec un autre vol.',
        'Cancelled' => 'Le composant a été annulé.',
        'NonFittingToItinerary' => "Le composant ne correspond plus à la structure actuelle de l'itinéraire.",
        'NonFittingLocations' => "Les lieux de départ et/ou d'arrivée du composant ne correspondent pas aux lieux de l'itinéraire immédiatement avant et/ou après. Ceci est signalé uniquement comme un avertissement et peut ne pas être un réel problème pour la réservation.",
        'NonFittingButAccepted' => "Indique que ce composant ne correspond pas mais a été tout de même marqué comme accepté par l'utilisateur pour la réservation.",
        'NoTicketsSelected' => 'Ce composant nécessite l’attribution de billets individuels afin de pouvoir être réservé.',
        'UnscheduledComponentNoLocationFound' => "Indique que le composant ne peut pas être planifié actuellement en raison d'une structure d'itinéraire incorrecte.",
        'Unknown' => 'Inconnu',
    ],
];
