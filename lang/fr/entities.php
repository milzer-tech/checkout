<?php

return [
    'gender' => [
        'male' => 'Homme',
        'female' => 'Femme',
    ],
    'availability' => [
        'NoAvailability' => 'le composant n’est plus disponible.',
        'DateTimeConflict' => 'le composant de vol a une date/heure qui est en conflit avec une autre correspondance.',
        'PaxSelectionConflict' => 'la sélection de passagers du vol est en conflit avec un autre vol.',
        'Cancelled' => 'le composant a été annulé.',
        'NonFittingToItinerary' => 'le composant n’est plus aligné sur la structure actuelle de l’itinéraire.',
        'NonFittingLocations' => 'les lieux de départ et/ou d’arrivée du composant ne sont pas alignés avec les lieux de l’itinéraire immédiatement avant et/ou après. Ceci est signalé uniquement comme un avertissement et peut ne pas constituer un problème réel pour la réservation.',
        'NonFittingButAccepted' => 'indique que ce composant ne convient pas, mais qu’il a néanmoins été marqué comme accepté pour la réservation par l’utilisateur.',
        'NoTicketsSelected' => 'ce composant nécessite l’attribution de billets individuels pour pouvoir être réservé.',
        'UnscheduledComponentNoLocationFound' => 'indique que le composant ne peut pas être planifié actuellement en raison d’une structure d’itinéraire incorrecte.',
        'Unknown' => 'Inconnu',
    ],
];
