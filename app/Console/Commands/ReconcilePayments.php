<?php

namespace App\Console\Commands;

use App\Models\Payment;
use App\Payments\FedaPayReconciler;
use Illuminate\Console\Command;

class ReconcilePayments extends Command
{
    protected $signature = 'payments:reconcile';
    protected $description = 'Vérifie chez FedaPay l’état des paiements en ligne en attente (filet de sécurité du webhook)';

    public function handle(FedaPayReconciler $reconciler): int
    {
        if (! $reconciler->canReconcile()) {
            $this->info('Paiement en ligne non configuré : rien à faire.');
            return self::SUCCESS;
        }

        $payments = Payment::where('provider', 'online')->where('status', 'pending')
            ->whereNotNull('provider_reference')->where('created_at', '>', now()->subDays(3))->get();

        foreach ($payments as $payment) {
            try {
                $reconciler->reconcile($payment);
            } catch (\Throwable $e) {
                report($e); // FedaPay injoignable : on réessaiera au prochain passage
            }
        }

        $this->info($payments->count() . ' paiement(s) vérifié(s).');
        return self::SUCCESS;
    }
}
