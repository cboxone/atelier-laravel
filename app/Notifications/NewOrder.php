<?php

namespace App\Notifications;

use App\Models\Order;
use App\Support\Money;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewOrder extends Notification
{
    public function __construct(public Order $order) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $o = $this->order->loadMissing('items');
        $mail = (new MailMessage)
            ->subject('Nouvelle commande ' . $o->reference)
            ->greeting('Nouvelle commande en ligne')
            ->line('Client : ' . $o->customer_name . ' · ' . $o->phone)
            ->line('Livraison : ' . $o->district . ', ' . $o->address)
            ->line('Paiement : ' . ($o->payment_method === 'cash_on_delivery' ? 'à la livraison' : 'Mobile Money'));
        foreach ($o->items as $i) {
            $mail->line($i->quantity . ' × ' . $i->name . ' : ' . Money::fcfa($i->line_total_fcfa));
        }
        return $mail->line('Livraison : ' . Money::fcfa($o->delivery_fee_fcfa))
            ->line('Total : ' . Money::fcfa($o->total_fcfa))
            ->action('Ouvrir l’administration', url('/admin'));
    }
}
