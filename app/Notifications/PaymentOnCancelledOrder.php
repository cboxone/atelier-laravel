<?php

namespace App\Notifications;

use App\Models\Order;
use App\Support\Money;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

// Un paiement est arrivé APRÈS l'annulation de la commande : de l'argent a été reçu, il faut agir.
class PaymentOnCancelledOrder extends Notification
{
    public function __construct(public Order $order) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('[ACTION] Paiement reçu pour une commande annulée ' . $this->order->reference)
            ->line('Le client a payé ' . Money::fcfa($this->order->total_fcfa) . ' alors que la commande avait été annulée (stock déjà remis en vente).')
            ->line('Téléphone du client : ' . $this->order->phone)
            ->line('Rappelez-le : rétablissez la commande si le stock le permet, sinon remboursez.')
            ->action('Ouvrir l’administration', url('/admin'));
    }
}
