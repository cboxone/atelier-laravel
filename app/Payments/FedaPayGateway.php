<?php

namespace App\Payments;

use App\Models\{Order, Payment};

// Paiement en ligne via FedaPay (Mobile Money et carte selon votre compte marchand).
// Nécessite : composer require fedapay/fedapay-php  +  FEDAPAY_SECRET_KEY dans .env
class FedaPayGateway implements PaymentGateway
{
    public function isConfigured(): bool
    {
        return class_exists(\FedaPay\FedaPay::class) && filled(config('shop.fedapay.secret_key'));
    }

    public static function boot(): void
    {
        \FedaPay\FedaPay::setApiKey(config('shop.fedapay.secret_key'));
        \FedaPay\FedaPay::setEnvironment(config('shop.fedapay.environment', 'sandbox'));
    }

    public function initiate(Order $order, Payment $payment): ?string
    {
        self::boot();

        [$first, $last] = array_pad(explode(' ', trim($order->customer_name), 2), 2, '');
        $digits = preg_replace('/\D+/', '', $order->phone);
        if (str_starts_with($digits, '229') && strlen($digits) > 10) {
            $digits = substr($digits, 3);
        }

        $transaction = \FedaPay\Transaction::create([
            'description'  => 'Commande ' . $order->reference,
            'amount'       => $order->total_fcfa,           // entier, en FCFA
            'currency'     => ['iso' => 'XOF'],
            'callback_url' => route('orders.payment.return'),
            'customer'     => array_filter([
                'firstname'    => $first,
                'lastname'     => $last ?: $first,
                'email'        => $order->email,
                'phone_number' => ['number' => '+229' . $digits, 'country' => 'bj'],
            ]),
        ]);

        // On retient l'identifiant FedaPay : c'est lui qui relie le rappel (webhook) à ce paiement.
        $payment->update(['provider_reference' => (string) $transaction->id]);

        return $transaction->generateToken()->url;
    }
}
