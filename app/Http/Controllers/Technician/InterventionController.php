<?php

namespace App\Http\Controllers\Technician;

use App\Http\Controllers\Controller;
use App\Models\{Intervention, InterventionPhoto, Product, Setting};
use App\Support\{Mass, SignatureImage};
use App\Workshop\{FluidRegister, InterventionService, WorkshopException};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class InterventionController extends Controller
{
    // Un technicien ne voit et ne modifie QUE ses interventions. L'administrateur voit tout.
    private function own(Request $request, Intervention $i): Intervention
    {
        $u = $request->user();
        abort_unless($u->is_admin === true || $i->technician_id === $u->id, 403);
        return $i;
    }

    private function open(Intervention $i): ?\Illuminate\Http\RedirectResponse
    {
        return in_array($i->status, ['planned', 'in_progress'], true) ? null : back()->withErrors(['action' => 'Intervention close : plus de modification.']);
    }

    public function index(Request $request)
    {
        $q = Intervention::with('customer', 'equipment')->whereIn('status', ['planned', 'in_progress'])->orderBy('scheduled_at');
        if (! $request->user()->is_admin) $q->where('technician_id', $request->user()->id);
        return view('technician.index', ['interventions' => $q->get()]);
    }

    public function show(Request $request, Intervention $intervention, FluidRegister $register)
    {
        $this->own($request, $intervention)->load('customer', 'equipment', 'lines', 'fluidMovements', 'photos');
        return view('technician.show', [
            'i' => $intervention, 'products' => Product::published()->orderBy('name')->get(),
            'balance' => $intervention->equipment ? $register->balance($intervention->equipment) : null,
        ]);
    }

    public function start(Request $request, Intervention $intervention)
    {
        $this->own($request, $intervention);
        if ($intervention->status === 'planned') $intervention->update(['status' => 'in_progress', 'started_at' => now()]);
        return back();
    }

    // Le technicien ne fixe jamais un prix : pièce = prix du catalogue ; main-d'œuvre = tarif horaire des réglages.
    public function addLine(Request $request, Intervention $intervention)
    {
        $this->own($request, $intervention);
        if ($r = $this->open($intervention)) return $r;

        $d = $request->validate(['kind' => ['required', Rule::in(['part', 'labor'])], 'product_id' => ['nullable', 'exists:products,id'], 'description' => ['nullable', 'string', 'max:255'], 'quantity' => ['required', 'integer', 'min:1', 'max:100']]);

        if ($d['kind'] === 'part') {
            $p = Product::published()->find($d['product_id'] ?? 0);
            if (! $p || $p->price_fcfa === null) return back()->withErrors(['action' => 'Choisissez une pièce du catalogue (avec un prix).']);
            $line = ['description' => $p->name, 'unit_price_fcfa' => $p->price_fcfa, 'product_id' => $p->id];
        } else {
            $rate = (int) Setting::get('labor_rate_fcfa');
            if ($rate < 1) return back()->withErrors(['action' => 'Le tarif horaire n’est pas configuré : demandez au bureau d’ajouter la main-d’œuvre.']);
            if (trim((string) ($d['description'] ?? '')) === '') return back()->withErrors(['action' => 'Décrivez le travail effectué.']);
            $line = ['description' => trim($d['description']), 'unit_price_fcfa' => $rate, 'product_id' => null];
        }

        $intervention->lines()->create($line + ['kind' => $d['kind'], 'quantity' => $d['quantity'], 'tax_group' => config('emecef.default_tax_group')]);
        return back();
    }

    public function addFluid(Request $request, Intervention $intervention, FluidRegister $register)
    {
        $this->own($request, $intervention);
        if ($r = $this->open($intervention)) return $r;
        $d = $request->validate(['operation' => ['required', Rule::in(['initial', 'charge', 'recovery'])], 'fluid' => ['required', 'string', 'max:40'], 'kg' => ['required', 'string'], 'notes' => ['nullable', 'string', 'max:500']]);
        $grams = Mass::kgToGrams($d['kg']);
        if ($grams === null) return back()->withErrors(['kg' => 'Quantité invalide : indiquez des kilos (ex. 0,350).'])->withInput();
        if (! $intervention->equipment) return back()->withErrors(['action' => 'Aucun équipement sur cette intervention.']);
        try {
            $register->record($intervention->equipment, $d['operation'], $grams, $d['fluid'], $request->user(), $intervention, $d['notes'] ?? null);
        } catch (WorkshopException $e) {
            return back()->withErrors(['action' => $e->getMessage()])->withInput();
        }
        return back();
    }

    public function addPhoto(Request $request, Intervention $intervention)
    {
        $this->own($request, $intervention);
        if ($r = $this->open($intervention)) return $r;
        $d = $request->validate(['photo' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:8192'], 'caption' => ['nullable', 'string', 'max:150']]);
        if ($intervention->photos()->count() >= 30) return back()->withErrors(['photo' => 'Maximum 30 photos par intervention.']);

        $path = $request->file('photo')->store("interventions/{$intervention->id}/photos", 'local'); // disque privé
        $intervention->photos()->create(['uploaded_by' => $request->user()->id, 'path' => $path, 'caption' => $d['caption'] ?? null]);
        return back();
    }

    public function sign(Request $request, Intervention $intervention)
    {
        $this->own($request, $intervention);
        if ($r = $this->open($intervention)) return $r;
        $d = $request->validate(['signed_by' => ['required', 'string', 'max:120'], 'signature' => ['required', 'string']]);
        try {
            $png = SignatureImage::decode($d['signature']);
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['signature' => 'Signature invalide : refaites-la à l’écran.']);
        }
        $path = "interventions/{$intervention->id}/signature.png";
        Storage::disk('local')->put($path, $png);
        $intervention->update(['signature_path' => $path, 'signed_by' => $d['signed_by'], 'signed_at' => now()]);
        return back();
    }

    public function finish(Request $request, Intervention $intervention, InterventionService $service)
    {
        $this->own($request, $intervention);
        $d = $request->validate(['work_done' => ['required', 'string', 'min:5', 'max:2000']], ['work_done.required' => 'Décrivez les travaux réalisés.', 'work_done.min' => 'Décrivez les travaux réalisés.']);
        try {
            $service->finish($intervention, $d['work_done']);
        } catch (WorkshopException $e) {
            return back()->withErrors(['action' => $e->getMessage()]);
        }
        return redirect('/technicien');
    }

    public function photo(Request $request, InterventionPhoto $photo)
    {
        $this->own($request, $photo->intervention);
        abort_unless(Storage::disk('local')->exists($photo->path), 404);
        return Storage::disk('local')->response($photo->path);
    }

    public function signature(Request $request, Intervention $intervention)
    {
        $this->own($request, $intervention);
        abort_unless($intervention->signature_path && Storage::disk('local')->exists($intervention->signature_path), 404);
        return Storage::disk('local')->response($intervention->signature_path);
    }
}
