<?php

namespace App\Payments;

use App\Models\{Order, Payment, Setting};
use App\Notifications\PaymentOnCancelledOrder;
use Illuminate\Support\Facades\{DB, Log, Notification};

// Seule source de vérité pour l'état d'un paiement FedaPay : on redemande la transaction à FedaPay.
// Utilisé par le webhook, par le retour du client, et par la commande planifiée payments:reconcile
// (filet de sécurité si un rappel est perdu, en retard, ou si le webhook a été désactivé par FedaPay).
class FedaPayReconciler
{
    // « transferred » = approuvée, puis versée sur votre solde : c'est TOUJOURS un paiement réussi.
    public const PAID = ['approved', 'transferred'];

    public function canReconcile(): bool
    {
        return app(FedaPayGateway::class)->isConfigured();
    }

    protected function fetchTransaction(string $id): object
    {
        FedaPayGateway::boot();
        return \FedaPay\Transaction::retrieve($id);
    }

    public function reconcile(Payment $payment): void
    {
        if ($payment->provider !== 'online' || ! $payment->provider_reference) return;

        $tx = $this->fetchTransaction($payment->provider_reference);
        $late = null;

        DB::transaction(function () use ($payment, $tx, &$late) {
            $p = Payment::lockForUpdate()->find($payment->id);
            $order = Order::lockForUpdate()->find($p->order_id);
            $status = (string) $tx->status;

            if (in_array($status, self::PAID, true)) {
                if ($p->status === 'paid' || $p->status === 'refunded') return;
                if ((int) $tx->amount !== (int) $p->amount_fcfa) {
                    Log::critical('Montant FedaPay différent du total de la commande', ['order' => $order->reference, 'attendu' => $p->amount_fcfa, 'reçu' => $tx->amount]);
                    return; // jamais de validation si le montant ne correspond pas
                }
                $p->update(['status' => 'paid']);
                $order->update(['payment_status' => 'paid']);
                if ($order->status === 'cancelled') $late = $order; // argent reçu pour une commande annulée
            } elseif ($status === 'refunded') {
                if ($p->status !== 'refunded') {
                    $p->update(['status' => 'refunded']);
                    $order->update(['payment_status' => 'refunded']);
                }
            } elseif (in_array($status, ['declined', 'canceled'], true)) {
                if ($p->status === 'pending') {
                    $p->update(['status' => 'failed']);
                    $order->update(['payment_status' => 'failed']);
                }
            }
            // « pending » : rien à faire, on réessaiera.
        });

        if ($late) $this->alertLate($late);
    }

    protected function alertLate(Order $order): void
    {
        Log::critical('Paiement reçu pour une commande annulée : ' . $order->reference);
        if ($to = Setting::get('notify_email')) {
            try {
                Notification::route('mail', $to)->notify(new PaymentOnCancelledOrder($order));
            } catch (\Throwable $e) {
                report($e);
            }
        }
    }
}
