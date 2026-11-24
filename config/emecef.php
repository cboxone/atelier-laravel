<?php

// Facture normalisée e-MECeF (Bénin). RIEN ICI n'est une valeur officielle : ces réglages viennent de bibliothèques
// tierces. À confirmer avec la documentation remise par la DGI et à tester sur leur environnement d'essai AVANT
// d'émettre une seule facture réelle.
return [
    // Obligatoire pour une entreprise assujettie à la TVA. Si vrai et que l'e-MECeF n'est pas activé, aucune facture ne peut être émise.
    'required' => (bool) env('EMECEF_REQUIRED', true),
    'enabled'  => (bool) env('EMECEF_ENABLED', false),

    'base_url' => env('EMECEF_BASE_URL'),   // adresse exacte fournie par la DGI (les sources tierces divergent : developer / developper)
    'token'    => env('EMECEF_TOKEN'),      // jeton JWT remis par la DGI
    'ifu'      => env('EMECEF_IFU'),        // IFU de l'atelier
    'timeout'  => 30,

    'endpoints' => [                        // chemins observés dans des bibliothèques tierces : à vérifier
        'create'   => '/invoice',
        'finalize' => '/invoice/{uid}/{action}',   // action : confirm | cancel
    ],

    // Les prix saisis sont TTC (la TVA est incluse). Taux par groupe de taxe, en %.
    // Seuls A et B apparaissent dans tous les exemples tiers : les autres groupes restent INTERDITS tant que non configurés.
    'default_tax_group' => env('EMECEF_DEFAULT_TAX_GROUP', 'B'),
    'tax_rates' => ['A' => 0, 'B' => 18],

    // Nom de chaque moyen de paiement attendu par l'API. Seul ESPECES est confirmé par les exemples tiers.
    'payment_names' => [
        'cash' => 'ESPECES', 'mobile_money' => 'MOBILEMONEY', 'transfer' => 'VIREMENT',
        'card' => 'CARTEBANCAIRE', 'cheque' => 'CHEQUES', 'credit' => 'CREDIT', 'other' => 'AUTRE',
    ],
];
