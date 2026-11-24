<?php

namespace App\Invoicing;

use App\Models\Invoice;
use Illuminate\Support\Facades\DB;

// Émission d'une facture. Règles qui ne se négocient pas :
//  - entreprise assujettie à la TVA (EMECEF_REQUIRED=true) : AUCUNE facture « émise » sans validation e-MECeF ;
//  - le total calculé ici doit être identique à celui renvoyé par la DGI, sinon on annule et on n'émet rien ;
//  - le numéro est attribué dans la transaction : pas de trou ; une facture numérotée ne se supprime plus.
class InvoiceIssuer
{
    public function __construct(private EmecefClient $client) {}

    public function issue(Invoice $invoice, string $operator): Invoice
    {
        $invoice = $this->prepare($invoice);

        if (! config('emecef.enabled')) {
            if (config('emecef.required')) {
                return $this->fail($invoice, 'Facturation normalisée e-MECeF requise mais non activée : la facture ne peut pas être émise.');
            }
            $invoice->update(['status' => 'issued', 'normalized' => false, 'issued_at' => now()]); // non assujetti : document interne
            return $invoice->refresh();
        }

        try {
            $payload = EmecefPayload::build([
                'ifu' => (string) config('emecef.ifu'), 'type' => $invoice->type,
                'lines' => $invoice->lines->map->only(['description', 'quantity', 'unit_price_fcfa', 'tax_group'])->all(),
                'customer' => $invoice->customer->only(['name', 'phone', 'ifu', 'address']),
                'operator' => $operator, 'payments' => $this->payments($invoice),
                'reference' => $invoice->original?->emecef_code,
            ], config('emecef.payment_names'));

            $created = $this->client->create($payload);
            $uid = (string) ($created['uid'] ?? '');
            if ($uid === '') throw new InvoiceException('Réponse e-MECeF inattendue : identifiant (uid) absent.');

            // Contrôle indispensable : on ne confirme jamais un total que nous n'avons pas calculé nous-mêmes.
            if (! isset($created['total']) || (int) $created['total'] !== $invoice->total_fcfa) {
                $this->safeCancel($uid);
                throw new InvoiceException('Total différent entre l’atelier (' . $invoice->total_fcfa . ') et l’e-MECeF (' . ($created['total'] ?? 'absent') . ') : facture annulée, rien n’a été émis.');
            }

            $final = $this->client->finalize($uid, 'confirm');
            if (empty($final['codeMECeFDGI']) || empty($final['qrCode'])) {
                throw new InvoiceException('Confirmation e-MECeF incomplète (code ou QR absent) : vérifiez la facture dans le portail DGI avant de réessayer.');
            }

            $invoice->update([
                'status' => 'issued', 'normalized' => true, 'issued_at' => now(), 'error' => null,
                'emecef_uid' => $uid, 'emecef_code' => $final['codeMECeFDGI'], 'emecef_qr' => $final['qrCode'],
                'emecef_nim' => $final['nim'] ?? null, 'emecef_counters' => $final['counters'] ?? null,
                'emecef_datetime' => isset($final['dateTime']) ? \Carbon\Carbon::parse($final['dateTime']) : null,
            ]);
            return $invoice->refresh();
        } catch (\Throwable $e) {
            report($e);
            return $this->fail($invoice, $e->getMessage());
        }
    }

    // Passe la facture en « en cours », fige les totaux et attribue le numéro, dans une seule transaction.
    private function prepare(Invoice $invoice): Invoice
    {
        return DB::transaction(function () use ($invoice) {
            $i = Invoice::with(['lines', 'customer', 'original', 'payments'])->lockForUpdate()->findOrFail($invoice->id);
            if (! in_array($i->status, ['draft', 'failed'], true)) throw new InvoiceException('Cette facture ne peut plus être émise.');
            if ($i->lines->isEmpty()) throw new InvoiceException('La facture est vide.');
            if ($i->type === 'FA' && (! $i->original || $i->original->status !== 'issued')) throw new InvoiceException('Un avoir doit référencer une facture émise.');

            $t = $i->totals();
            if ($t['total'] <= 0) throw new InvoiceException('Le total de la facture doit être supérieur à zéro.');

            $i->forceFill(['total_fcfa' => $t['total'], 'tax_fcfa' => $t['tax'], 'status' => 'pending', 'error' => null]);
            $i->number ??= Numbering::next($i->type === 'FA' ? 'credit_note' : 'invoice');
            $i->save();
            return $i;
        });
    }

    // Paiements déjà saisis + le reste « à crédit », pour que le total payé égale toujours le total de la facture.
    private function payments(Invoice $invoice): array
    {
        if ($invoice->type === 'FA') return [['method' => 'cash', 'amount' => $invoice->total_fcfa]];
        $paid = $invoice->payments->map(fn ($p) => ['method' => $p->method, 'amount' => $p->amount_fcfa])->all();
        $sum = array_sum(array_column($paid, 'amount'));
        if ($sum > $invoice->total_fcfa) throw new InvoiceException('Les paiements saisis dépassent le total de la facture.');
        if ($sum < $invoice->total_fcfa) $paid[] = ['method' => 'credit', 'amount' => $invoice->total_fcfa - $sum];
        return $paid;
    }

    private function safeCancel(string $uid): void
    {
        try { $this->client->finalize($uid, 'cancel'); } catch (\Throwable $e) { report($e); }
    }

    private function fail(Invoice $invoice, string $message): Invoice
    {
        $invoice->update(['status' => 'failed', 'error' => mb_substr($message, 0, 500)]);
        throw new InvoiceException($message);
    }
}
