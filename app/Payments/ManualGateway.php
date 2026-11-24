<?php

namespace App\Payments;

use App\Models\{Order, Payment};

// Espèces à la livraison, paiement à l'atelier, virement, envoi Mobile Money à un numéro de l'atelier :
// rien à appeler en ligne. Le paiement reste « en attente » jusqu'à ce que l'atelier le confirme dans l'administration.
class ManualGateway implements PaymentGateway
{
    public function isConfigured(): bool
    {
        return true;
    }

    public function initiate(Order $order, Payment $payment): ?string
    {
        return null;
    }
}
