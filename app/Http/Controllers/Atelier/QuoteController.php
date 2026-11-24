<?php

namespace App\Http\Controllers\Atelier;

use App\Http\Controllers\Controller;
use App\Invoicing\{BillingService, InvoiceException};
use App\Models\{Customer, Quote};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class QuoteController extends Controller
{
    private const NEXT = ['draft' => ['sent', 'accepted', 'refused'], 'sent' => ['accepted', 'refused']];

    public function index()
    {
        return view('atelier.quotes.index', ['quotes' => Quote::with('customer')->latest()->paginate(25)]);
    }

    public function create()
    {
        return view('atelier.quotes.create', ['customers' => Customer::orderBy('name')->get(), 'groups' => array_keys(config('emecef.tax_rates'))]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'], 'valid_until' => ['nullable', 'date', 'after_or_equal:today'], 'notes' => ['nullable', 'string', 'max:1000'],
            'lines' => ['required', 'array', 'min:1', 'max:50'],
            'lines.*.description' => ['required', 'string', 'max:255'], 'lines.*.quantity' => ['required', 'integer', 'min:1', 'max:1000'],
            'lines.*.unit_price_fcfa' => ['required', 'integer', 'min:0', 'max:1000000000'], 'lines.*.tax_group' => ['required', Rule::in(array_keys(config('emecef.tax_rates')))],
        ], ['lines.required' => 'Ajoutez au moins une ligne.']);

        $quote = DB::transaction(function () use ($data) {
            $q = Quote::create(['customer_id' => $data['customer_id'], 'valid_until' => $data['valid_until'] ?? null, 'notes' => $data['notes'] ?? null]);
            foreach ($data['lines'] as $l) $q->lines()->create($l);
            return $q;
        });

        return redirect()->route('atelier.quotes.show', $quote);
    }

    public function show(Quote $quote)
    {
        $quote->load('customer', 'lines');
        return view('atelier.quotes.show', ['quote' => $quote, 'totals' => $quote->totals(), 'next' => self::NEXT[$quote->status] ?? []]);
    }

    public function status(Request $request, Quote $quote)
    {
        $to = $request->validate(['status' => ['required', 'string']])['status'];
        if (! in_array($to, self::NEXT[$quote->status] ?? [], true)) {
            return back()->withErrors(['action' => 'Changement de statut impossible.']);
        }
        $quote->update(['status' => $to]);
        return back();
    }

    public function invoice(Quote $quote, BillingService $billing)
    {
        try {
            $invoice = $billing->invoiceFromQuote($quote);
        } catch (InvoiceException $e) {
            return back()->withErrors(['action' => $e->getMessage()]);
        }
        return redirect()->route('atelier.invoices.show', $invoice);
    }
}
