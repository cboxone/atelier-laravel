@extends('atelier.layout')
@section('title', 'Registre des fluides')
@section('content')
<h1>Registre des fluides frigorigènes</h1>
<div class="card"><p><strong>{{ $equipment->label }}</strong> · {{ $equipment->customer->name }} · {{ $equipment->brand }} {{ $equipment->model }} @if($equipment->serial_number)· N° série {{ $equipment->serial_number }}@endif · fluide : {{ $equipment->fluid_type ?? 'non précisé' }}</p>
<p>Charge enregistrée aujourd’hui : <strong>{{ \App\Support\Mass::gramsToKg($balance) }} kg</strong></p>
<table><tr><th>Date</th><th>Opération</th><th>Fluide</th><th class="num">Quantité (kg)</th><th>Technicien</th><th>Intervention</th><th>Notes</th><th class="no-print"><span class="sr">Action</span></th></tr>
@forelse($movements as $m)<tr><td>{{ $m->occurred_at->format('d/m/Y H:i') }}</td><td>{{ \App\Models\FluidMovement::OPERATIONS[$m->operation] }}@if($m->reverses_id) <span class="muted">(annule l’écriture n° {{ $m->reverses_id }})</span>@endif @if(in_array($m->id, $reversed)) <span class="err">corrigée</span>@endif</td><td>{{ $m->fluid }}</td><td class="num">{{ $m->quantity_g > 0 ? '+' : '' }}{{ $m->kg }}</td><td>{{ $m->technician?->name }}</td><td>{{ $m->intervention?->reference }}</td><td>{{ $m->notes }}</td>
<td class="no-print">@if($m->operation !== 'correction' && ! in_array($m->id, $reversed))<form method="post" action="{{ route('atelier.fluid.reverse', $m) }}" style="display:flex;gap:6px">@csrf<input name="reason" placeholder="Raison de la correction" required aria-label="Raison de la correction" style="min-width:150px"><button class="btn line">Corriger</button></form>@endif</td></tr>
@empty<tr><td colspan="8" class="muted">Aucune écriture.</td></tr>@endforelse</table>
<p class="muted">Ce registre ne se modifie ni ne se supprime : une erreur se corrige par une écriture inverse qui reste visible.</p>
<button class="btn line no-print" onclick="window.print()" type="button">Imprimer</button></div>
@endsection
