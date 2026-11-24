@extends('atelier.layout')
@section('title', 'Intervention '.$i->reference)
@section('content')
<h1>{{ $i->reference }} · {{ $i->title }} <span class="badge">{{ \App\Models\Intervention::STATUSES[$i->status] }}</span></h1>
<div class="card"><p><strong>{{ $i->customer->name }}</strong> · {{ $i->customer->phone }}@if($i->equipment) · équipement : {{ $i->equipment->label }}@endif @if($i->technician) · technicien : {{ $i->technician->name }}@endif</p></div>

<div class="card"><h2>Travaux et pièces (prix TTC)</h2>
<table><tr><th>Désignation</th><th>Type</th><th class="num">Qté</th><th class="num">PU TTC</th><th class="num">Total</th></tr>
@forelse($i->lines as $l)<tr><td>{{ $l->description }}</td><td>{{ $l->kind === 'part' ? 'Pièce' : 'Main-d’œuvre' }}</td><td class="num">{{ $l->quantity }}</td><td class="num">{{ \App\Support\Money::fcfa($l->unit_price_fcfa) }}</td><td class="num">{{ \App\Support\Money::fcfa($l->quantity * $l->unit_price_fcfa) }}</td></tr>@empty<tr><td colspan="5" class="muted">Aucune ligne.</td></tr>@endforelse</table>
@if(in_array($i->status, ['planned', 'in_progress']))
<form method="post" action="{{ route('atelier.interventions.lines', $i) }}" class="row no-print" style="align-items:end;margin-top:10px">@csrf
<label>Type<select name="kind"><option value="labor">Main-d’œuvre</option><option value="part">Pièce</option></select></label>
<label>Produit (pièce)<select name="product_id"><option value="">Aucun</option>@foreach($products as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach</select></label>
<label style="flex:2 1 200px">Désignation<input name="description"></label><label>Qté<input type="number" name="quantity" min="1" value="1" required></label>
<label>PU TTC<input type="number" name="unit_price_fcfa" min="0"></label>
<label>Taxe<select name="tax_group">@foreach($groups as $g)<option value="{{ $g }}" @selected($g === config('emecef.default_tax_group'))>{{ $g }}</option>@endforeach</select></label><button class="btn dark">Ajouter</button></form>@endif</div>

@if($i->equipment)
<div class="card"><h2>Registre des fluides · {{ $i->equipment->label }}</h2>
<p>Charge enregistrée : <strong>{{ \App\Support\Mass::gramsToKg($balance) }} kg</strong> · <a href="{{ route('atelier.fluid.show', $i->equipment) }}">Voir le registre complet</a></p>
<table>@foreach($i->fluidMovements as $m)<tr><td>{{ $m->occurred_at->format('d/m/Y H:i') }}</td><td>{{ \App\Models\FluidMovement::OPERATIONS[$m->operation] }} · {{ $m->fluid }}</td><td class="num">{{ $m->quantity_g > 0 ? '+' : '' }}{{ $m->kg }} kg</td></tr>@endforeach</table>
@if(in_array($i->status, ['planned', 'in_progress']))
<form method="post" action="{{ route('atelier.interventions.fluid', $i) }}" class="row no-print" style="align-items:end;margin-top:10px">@csrf
<label>Opération<select name="operation"><option value="charge">Ajout de fluide</option><option value="recovery">Récupération</option><option value="initial">Charge initiale déclarée</option></select></label>
<label>Fluide<input name="fluid" value="{{ old('fluid', $i->equipment->fluid_type) }}" required></label><label>Quantité (kg)<input name="kg" inputmode="decimal" placeholder="0,350" required></label>
<label>Notes<input name="notes"></label><button class="btn dark">Inscrire au registre</button></form>@endif</div>@endif

<div class="card no-print">
@if(in_array($i->status, ['planned', 'in_progress']))
<form method="post" action="{{ route('atelier.interventions.finish', $i) }}">@csrf<label>Travaux réalisés<textarea name="work_done" rows="3">{{ $i->work_done }}</textarea></label><button class="btn">Terminer l’intervention (retire les pièces du stock)</button></form>
@elseif($i->status === 'done')<p class="ok">Intervention terminée.</p>
@if($i->invoices->where('type', 'FV')->isEmpty())<form method="post" action="{{ route('atelier.interventions.invoice', $i) }}">@csrf<button class="btn">Créer la facture</button></form>
@else<p>Facture : @foreach($i->invoices->where('type', 'FV') as $inv)<a href="{{ route('atelier.invoices.show', $inv) }}">{{ $inv->number ?? 'brouillon' }}</a> @endforeach</p>@endif @endif</div>
@endsection
