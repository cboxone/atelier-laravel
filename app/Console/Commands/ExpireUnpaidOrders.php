<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Payments\FedaPayReconciler;
use Illuminate\Console\Command;

// Un client qui abandonne la page de paiement en ligne bloquerait le stock pour toujours.
//  - paiement « en attente » depuis plus de SHOP_UNPAID_EXPIRY_MINUTES (180 par défaut) : on vérifie D'ABORD chez
//    FedaPay (jamais d'annulation d'une commande payée), puis on annule si elle n'est toujours pas payée ;
//  - paiement « échoué » (refusé, annulé par le client) depuis plus de SHOP_FAILED_EXPIRY_MINUTES (1440 = 24 h) :
//    l'atelier a eu le temps de rappeler le client, on libère le stock.
// L'annulation remet le stock (une seule fois).
class ExpireUnpaidOrders extends Command
{
    protected $signature = 'orders:expire-unpaid';
    protected $description = 'Annule les commandes à paiement en ligne jamais payées et remet leur stock';

    public function handle(FedaPayReconciler $reconciler): int
    {
        $online = array_keys(config('shop.gateways', []));
        $cancelled = 0;

        $pending = Order::whereIn('payment_method', $online)->where('payment_status', 'pending')->where('status', 'new')
            ->where('created_at', '<', now()->subMinutes((int) config('shop.unpaid_online_expiry_minutes', 180)))->get();

        foreach ($pending as $order) {
            $payment = $order->payments()->whereNotNull('provider_reference')->first();
            if ($payment && $reconciler->canReconcile()) {
                try {
                    $reconciler->reconcile($payment);
                } catch (\Throwable $e) {
                    report($e);
                    continue; // état inconnu : par prudence, on n'annule pas
                }
                $order->refresh();
                if ($order->payment_status !== 'pending' && $order->payment_status !== 'failed') continue; // payée ou remboursée
            }
            $order->update(['status' => 'cancelled']); // déclenche la remise en stock
            $cancelled++;
        }

        $failed = Order::whereIn('payment_method', $online)->where('payment_status', 'failed')->where('status', 'new')
            ->where('created_at', '<', now()->subMinutes((int) config('shop.failed_online_expiry_minutes', 1440)))->get();
        foreach ($failed as $order) {
            $order->update(['status' => 'cancelled']);
            $cancelled++;
        }

        $this->info($cancelled . ' commande(s) annulée(s).');
        return self::SUCCESS;
    }
}
