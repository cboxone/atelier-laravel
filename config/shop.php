<?php

use App\Payments\FedaPayGateway;

return [
    // Associe un moyen de paiement (colonne `code` de la table payment_methods) à sa passerelle.
    // Tout code absent d'ici est un moyen « manuel » (espèces, virement, envoi Mobile Money à vérifier à la main).
    // Pour ajouter un autre prestataire : écrire une classe qui implémente PaymentGateway et l'associer ici.
    'gateways' => [
        'online' => FedaPayGateway::class,
    ],

    // Commandes à paiement en ligne jamais payées : annulées (et stock remis) après ce délai, en minutes.
    'unpaid_online_expiry_minutes' => (int) env('SHOP_UNPAID_EXPIRY_MINUTES', 180),

    // Paiement « échoué » : commande annulée et stock libéré après ce délai (24 h par défaut).
    'failed_online_expiry_minutes' => (int) env('SHOP_FAILED_EXPIRY_MINUTES', 1440),

    'fedapay' => [
        'secret_key'     => env('FEDAPAY_SECRET_KEY'),
        'environment'    => env('FEDAPAY_ENV', 'sandbox'), // sandbox | live
        'webhook_secret' => env('FEDAPAY_WEBHOOK_SECRET'),
    ],
];
