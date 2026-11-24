<?php

namespace App\Http\Controllers\Atelier;

use App\Http\Controllers\Controller;
use App\Invoicing\{BillingService, InvoiceException};
use App\Models\{Customer, Equipment, Intervention, Product, User};
use App\Support\Mass;
use App\Workshop\{FluidRegister, InterventionService, WorkshopException};
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InterventionController extends Controller
{
    public function index()
    {
        return view('atelier.interventions.index', ['interventions' => Intervention::with(['customer', 'technician'])->latest()->paginate(25)]);
    }

    public function create()
    {
        return view('atelier.interventions.create', ['customers' => Customer::orderBy('name')->get(), 'equipments' => Equipment::with('customer')->orderBy('label')->get(), 'users' => User::orderBy('name')->get()]);
    }

    public function store(Request $request)
    {
        $d = $request->validate(['customer_id' => ['required', 'exists:customers,id'], 'equipment_id' => ['nullable', 'exists:equipments,id'], 'technician_id' => ['nullable', 'exists:users,id'],
            'title' => ['required', 'string', 'max:255'], 'scheduled_at' => ['nullable', 'date']]);
        if (! empty($d['equipment_id']) && Equipment::find($d['equipment_id'])->customer_id !== (int) $d['customer_id']) {
            return back()->withErrors(['equipment_id' => 'Cet équipement n’appartient pas à ce client.'])->withInput();
        }
        return redirect()->route('atelier.interventions.show', Intervention::create($d));
    }

    public function show(Intervention $intervention, FluidRegister $register)
    {
        $intervention->load('customer', 'equipment', 'technician', 'lines', 'fluidMovements.technician', 'invoices');
        return view('atelier.interventions.show', [
            'i' => $intervention, 'products' => Product::published()->orderBy('name')->get(), 'groups' => array_keys(config('emecef.tax_rates')),
            'balance' => $intervention->equipment ? $register->balance($intervention->equipment) : null,
        ]);
    }

    public function addLine(Request $request, Intervention $intervention)
    {
        if ($intervention->status === 'done' || $intervention->status === 'cancelled') return back()->withErrors(['action' => 'Intervention close : on n’ajoute plus de ligne.']);
        $d = $request->validate(['kind' => ['required', Rule::in(['labor', 'part'])], 'product_id' => ['nullable', 'exists:products,id'], 'description' => ['nullable', 'string', 'max:255'],
            'quantity' => ['required', 'integer', 'min:1', 'max:1000'], 'unit_price_fcfa' => ['nullable', 'integer', 'min:0'], 'tax_group' => ['required', Rule::in(array_keys(config('emecef.tax_rates')))]]);
        $product = ! empty($d['product_id']) ? Product::find($d['product_id']) : null;
        $d['description'] = $d['description'] ?: $product?->name;
        $d['unit_price_fcfa'] = $d['unit_price_fcfa'] ?? $product?->price_fcfa;
        if (! $d['description'] || $d['unit_price_fcfa'] === null) return back()->withErrors(['action' => 'Indiquez une désignation et un prix.'])->withInput();
        $intervention->lines()->create($d);
        return back();
    }

    public function addFluid(Request $request, Intervention $intervention, FluidRegister $register)
    {
        $d = $request->validate(['operation' => ['required', Rule::in(['initial', 'charge', 'recovery'])], 'fluid' => ['required', 'string', 'max:40'], 'kg' => ['required', 'string'], 'notes' => ['nullable', 'string', 'max:500']]);
        $grams = Mass::kgToGrams($d['kg']);
        if ($grams === null) return back()->withErrors(['kg' => 'Quantité invalide : indiquez des kilos (ex. 0,350), 3 décimales au plus.'])->withInput();
        if (! $intervention->equipment) return back()->withErrors(['action' => 'Cette intervention n’a pas d’équipement.']);
        try {
            $register->record($intervention->equipment, $d['operation'], $grams, $d['fluid'], $request->user(), $intervention, $d['notes'] ?? null);
        } catch (WorkshopException $e) {
            return back()->withErrors(['action' => $e->getMessage()])->withInput();
        }
        return back();
    }

    public function finish(Request $request, Intervention $intervention, InterventionService $service)
    {
        try {
            $service->finish($intervention, $request->input('work_done'));
        } catch (WorkshopException $e) {
            return back()->withErrors(['action' => $e->getMessage()]);
        }
        return back();
    }

    public function invoice(Intervention $intervention, BillingService $billing)
    {
        try {
            $invoice = $billing->invoiceFromIntervention($intervention);
        } catch (InvoiceException $e) {
            return back()->withErrors(['action' => $e->getMessage()]);
        }
        return redirect()->route('atelier.invoices.show', $invoice);
    }
}
