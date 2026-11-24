<?php

namespace App\Notifications;

use App\Models\ServiceRequest;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewServiceRequest extends Notification
{
    public function __construct(public ServiceRequest $serviceRequest) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $r = $this->serviceRequest;

        return (new MailMessage)
            ->subject(($r->is_urgent ? '[URGENT] ' : '') . 'Nouvelle demande ' . $r->reference)
            ->greeting('Nouvelle demande d’intervention')
            ->line('Référence : ' . $r->reference)
            ->line('Téléphone : ' . $r->phone)
            ->line('Quartier : ' . $r->district)
            ->line('Équipement : ' . $r->equipment)
            ->line('Problème : ' . $r->problem)
            ->action('Ouvrir l’administration', url('/admin'));
    }
}
