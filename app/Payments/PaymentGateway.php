<?php

namespace App\Payments;

use App\Models\{Order, Payment};

interface PaymentGateway
{
    // Faux si le moyen ne peut pas fonctionner (clés manquantes, bibliothèque absente) : il est alors caché au client.
    public function isConfigured(): bool;

    // Retourne l'adresse où envoyer le client pour payer, ou null s'il n'y a rien à faire en ligne.
    public function initiate(Order $order, Payment $payment): ?string;
}
