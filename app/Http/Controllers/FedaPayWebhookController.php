<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Payments\FedaPayReconciler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

// Rappel serveur de FedaPay (documentation : en-tête X-FEDAPAY-SIGNATURE, \FedaPay\Webhook::constructEvent).
//   1. la signature est vérifiée avec le secret du webhook ;
//   2. le contenu reçu n'est PAS cru : on redemande la transaction à FedaPay (voir FedaPayReconciler) ;
//   3. on répond 2xx rapidement : FedaPay désactive un webhook après 10 échecs. En cas de panne de notre côté,
//      la commande payments:reconcile rattrape les paiements manqués.
class FedaPayWebhookController extends Controller
{
    public function __invoke(Request $request, FedaPayReconciler $reconciler)
    {
        $secret = config('shop.fedapay.webhook_secret');
        abort_unless($secret && class_exists(\FedaPay\Webhook::class), 404);

        try {
            $event = \FedaPay\Webhook::constructEvent($request->getContent(), (string) $request->header('X-FEDAPAY-SIGNATURE'), $secret);
        } catch (\Throwable $e) {
            return response('Signature invalide', 400);
        }

        $txId = $this->transactionId($event);
        if ($txId === '') {
            Log::warning('Rappel FedaPay sans identifiant de transaction exploitable', ['name' => $event->name ?? null]);
            return response()->json(['ok' => true]); // le paiement sera retrouvé par payments:reconcile
        }

        $payment = Payment::where('provider', 'online')->where('provider_reference', $txId)->first();
        if ($payment) {
            try {
                $reconciler->reconcile($payment);
            } catch (\Throwable $e) {
                report($e); // 2xx quand même : payments:reconcile réessaiera
            }
        }

        return response()->json(['ok' => true]);
    }

    private function transactionId(object $event): string
    {
        foreach ([fn () => $event->entity->id ?? null, fn () => $event->object_id ?? null] as $try) {
            try {
                if (($v = $try()) !== null && $v !== '') return (string) $v;
            } catch (\Throwable $e) {
            }
        }
        return '';
    }
}
