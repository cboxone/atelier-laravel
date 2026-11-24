<?php

namespace App\Invoicing;

use App\Models\{Intervention, Invoice, InvoicePayment, Quote};
use Illuminate\Support\Facades\DB;

// Création des brouillons de facture et des avoirs, enregistrement des paiements.
class BillingService
{
    public function invoiceFromQuote(Quote $quote): Invoice
    {
        return DB::transaction(function () use ($quote) {
            $q = Quote::with('lines')->lockForUpdate()->findOrFail($quote->id);
            if ($q->status !== 'accepted') throw new InvoiceException('Seul un devis accepté peut être facturé.');
            $invoice = Invoice::create(['customer_id' => $q->customer_id, 'quote_id' => $q->id, 'intervention_id' => $q->intervention_id]);
            foreach ($q->lines as $l) $invoice->lines()->create($l->only(['description', 'quantity', 'unit_price_fcfa', 'tax_group']));
            $q->update(['status' => 'invoiced']);
            return $invoice;
        });
    }

    public function invoiceFromIntervention(Intervention $intervention): Invoice
    {
        return DB::transaction(function () use ($intervention) {
            $i = Intervention::with('lines')->lockForUpdate()->findOrFail($intervention->id);
            if ($i->status !== 'done') throw new InvoiceException('Terminez l’intervention avant de la facturer.');
            if ($i->lines->isEmpty()) throw new InvoiceException('Aucune ligne à facturer.');
            if (Invoice::where('intervention_id', $i->id)->where('type', 'FV')->exists()) throw new InvoiceException('Cette intervention a déjà une facture.');
            $invoice = Invoice::create(['customer_id' => $i->customer_id, 'intervention_id' => $i->id]);
            foreach ($i->lines as $l) $invoice->lines()->create(['description' => $l->description, 'quantity' => $l->quantity, 'unit_price_fcfa' => $l->unit_price_fcfa, 'tax_group' => $l->tax_group]);
            return $invoice;
        });
    }

    // Avoir : jamais plus que ce qui reste à créditer sur la facture d'origine.
    public function creditNote(Invoice $original, array $lines): Invoice
    {
        return DB::transaction(function () use ($original, $lines) {
            $o = Invoice::with('credits.lines')->lockForUpdate()->findOrFail($original->id);
            if ($o->type !== 'FV' || $o->status !== 'issued') throw new InvoiceException('Un avoir se fait sur une facture de vente émise.');
            $rates = config('emecef.tax_rates');
            $amount = InvoiceTotals::compute($lines, $rates)['total'];
            // Les avoirs en brouillon comptent aussi : on ne peut pas en préparer deux qui dépassent ensemble la facture.
            $alreadyCredited = (int) $o->credits->sum(fn ($c) => $c->status === 'draft' ? InvoiceTotals::compute($c->lines->map->only(['quantity', 'unit_price_fcfa', 'tax_group']), $rates)['total'] : $c->total_fcfa);
            if ($amount <= 0) throw new InvoiceException('L’avoir est vide.');
            if ($amount + $alreadyCredited > $o->total_fcfa) throw new InvoiceException('L’avoir dépasse le montant restant à créditer sur la facture.');
            $credit = Invoice::create(['type' => 'FA', 'customer_id' => $o->customer_id, 'original_invoice_id' => $o->id]);
            foreach ($lines as $l) $credit->lines()->create(['description' => $l['description'], 'quantity' => $l['quantity'], 'unit_price_fcfa' => $l['unit_price_fcfa'], 'tax_group' => $l['tax_group']]);
            return $credit;
        });
    }

    public function recordPayment(Invoice $invoice, string $method, int $amount, ?string $reference = null): InvoicePayment
    {
        return DB::transaction(function () use ($invoice, $method, $amount, $reference) {
            $i = Invoice::with('payments')->lockForUpdate()->findOrFail($invoice->id);
            if (! array_key_exists($method, InvoicePayment::METHODS)) throw new InvoiceException('Moyen de paiement inconnu.');
            if ($amount < 1) throw new InvoiceException('Le montant doit être positif.');
            if ($i->type !== 'FV') throw new InvoiceException('On n’encaisse pas sur un avoir.');
            if ($amount > $i->total_fcfa - $i->paid_fcfa && $i->status === 'issued') throw new InvoiceException('Le montant dépasse le reste à payer.');
            return $i->payments()->create(['method' => $method, 'amount_fcfa' => $amount, 'reference' => $reference, 'paid_at' => now()]);
        });
    }
}
