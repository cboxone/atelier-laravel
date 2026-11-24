<?php

namespace App\Http\Controllers\Atelier;

use App\Http\Controllers\Controller;
use App\Invoicing\{InvoiceException};
use App\Models\{Customer, Equipment, MaintenanceContract};
use App\Workshop\{ContractService, WorkshopException};
use Illuminate\Http\Request;

class ContractController extends Controller
{
    public function index()
    {
        return view('atelier.contracts.index', ['contracts' => MaintenanceContract::with('customer')->latest()->paginate(25)]);
    }

    public function create()
    {
        return view('atelier.contracts.create', ['customers' => Customer::orderBy('name')->get(), 'equipments' => Equipment::with('customer')->orderBy('label')->get()]);
    }

    public function store(Request $request, ContractService $service)
    {
        $d = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'], 'title' => ['required', 'string', 'max:200'], 'starts_on' => ['required', 'date'], 'ends_on' => ['required', 'date', 'after:starts_on'],
            'visits_per_year' => ['required', 'integer', 'min:1', 'max:12'], 'price_fcfa' => ['required', 'integer', 'min:0'], 'notes' => ['nullable', 'string', 'max:1000'],
            'equipment_ids' => ['required', 'array', 'min:1'], 'equipment_ids.*' => ['integer', 'exists:equipments,id'],
        ], ['equipment_ids.required' => 'Choisissez au moins un équipement couvert.']);

        try {
            $contract = $service->create(collect($d)->except('equipment_ids')->all(), $d['equipment_ids']);
        } catch (WorkshopException $e) {
            return back()->withErrors(['action' => $e->getMessage()])->withInput();
        }
        return redirect()->route('atelier.contracts.show', $contract);
    }

    public function show(MaintenanceContract $contract)
    {
        $contract->load('customer', 'equipments', 'visits.interventions');
        return view('atelier.contracts.show', ['c' => $contract]);
    }

    public function generate(MaintenanceContract $contract, ContractService $service)
    {
        $n = $service->generateInterventions(14);
        return back()->with('status', "$n intervention(s) créée(s) pour les visites des 14 prochains jours (tous contrats confondus).");
    }

    public function invoice(MaintenanceContract $contract, ContractService $service)
    {
        try {
            $invoice = $service->invoice($contract);
        } catch (InvoiceException $e) {
            return back()->withErrors(['action' => $e->getMessage()]);
        }
        return redirect()->route('atelier.invoices.show', $invoice);
    }

    public function cancel(MaintenanceContract $contract)
    {
        $contract->update(['status' => 'cancelled']);
        $contract->visits()->where('status', 'planned')->whereNull('interventions_created_at')->update(['status' => 'skipped']);
        return back();
    }
}
