<?php

namespace App\Http\Controllers\Atelier;

use App\Http\Controllers\Controller;
use App\Invoicing\{BillingService, InvoiceException, InvoiceIssuer};
use App\Models\{Invoice, InvoicePayment};
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index()
    {
        return view('atelier.invoices.index', ['invoices' => Invoice::with(['customer', 'payments'])->latest()->paginate(25)]);
    }

    public function show(Invoice $invoice)
    {
        $invoice->load('customer', 'lines', 'payments', 'original', 'credits');
        return view('atelier.invoices.show', ['invoice' => $invoice, 'totals' => $invoice->totals(), 'methods' => InvoicePayment::METHODS]);
    }

    public function issue(Request $request, Invoice $invoice, InvoiceIssuer $issuer)
    {
        try {
            $issuer->issue($invoice, $request->user()->name);
        } catch (InvoiceException $e) {
            return redirect()->route('atelier.invoices.show', $invoice)->withErrors(['action' => $e->getMessage()]);
        }
        return redirect()->route('atelier.invoices.show', $invoice);
    }

    public function pay(Request $request, Invoice $invoice, BillingService $billing)
    {
        $d = $request->validate(['method' => ['required', 'string'], 'amount_fcfa' => ['required', 'integer', 'min:1'], 'reference' => ['nullable', 'string', 'max:120']]);
        try {
            $billing->recordPayment($invoice, $d['method'], (int) $d['amount_fcfa'], $d['reference'] ?? null);
        } catch (InvoiceException $e) {
            return back()->withErrors(['action' => $e->getMessage()]);
        }
        return back();
    }

    // Avoir total de la facture : le cas simple. (Avoir partiel : à saisir en brouillon d'avoir à l'étape suivante.)
    public function credit(Invoice $invoice, BillingService $billing)
    {
        try {
            $credit = $billing->creditNote($invoice, $invoice->lines->map->only(['description', 'quantity', 'unit_price_fcfa', 'tax_group'])->all());
        } catch (InvoiceException $e) {
            return back()->withErrors(['action' => $e->getMessage()]);
        }
        return redirect()->route('atelier.invoices.show', $credit);
    }
}
