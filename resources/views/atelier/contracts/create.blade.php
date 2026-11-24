@extends('atelier.layout')
@section('title', 'Nouveau contrat')
@section('content')
<h1>Nouveau contrat d’entretien</h1>
<form method="post" action="{{ route('atelier.contracts.store') }}" class="card">@csrf
<div class="row"><label>Client<select name="customer_id" id="customer" required><option value="">Choisir…</option>@foreach($customers as $c)<option value="{{ $c->id }}" @selected(old('customer_id') == $c->id)>{{ $c->name }}</option>@endforeach</select></label>
<label style="flex:2 1 260px">Intitulé<input name="title" value="{{ old('title') }}" required></label></div>
<div class="row"><label>Début<input type="date" name="starts_on" value="{{ old('starts_on') }}" required></label><label>Fin<input type="date" name="ends_on" value="{{ old('ends_on') }}" required></label>
<label>Visites par an<input type="number" name="visits_per_year" min="1" max="12" value="{{ old('visits_per_year', 2) }}" required></label>
<label>Prix TTC pour la période (FCFA)<input type="number" name="price_fcfa" min="0" value="{{ old('price_fcfa') }}" required></label></div>
<fieldset style="border:1px solid var(--line);border-radius:8px;margin:0 0 12px"><legend>Équipements couverts (ceux du client choisi)</legend>
@foreach($equipments as $e)<label class="eq" data-customer="{{ $e->customer_id }}" style="display:none;margin:6px 0"><input type="checkbox" name="equipment_ids[]" value="{{ $e->id }}" style="display:inline-block;width:auto;min-height:0" @checked(in_array($e->id, old('equipment_ids', [])))> {{ $e->label }} <span class="muted">{{ $e->fluid_type }}</span></label>@endforeach
<p class="muted" id="none">Choisissez d’abord un client.</p></fieldset>
<label>Notes<textarea name="notes" rows="2">{{ old('notes') }}</textarea></label>
<button class="btn" type="submit">Créer le contrat et planifier les visites</button></form>
<script>
var sel=document.getElementById('customer');function f(){var n=0;document.querySelectorAll('.eq').forEach(function(l){var ok=l.dataset.customer===sel.value;l.style.display=ok?'block':'none';if(!ok)l.querySelector('input').checked=false;else n++});document.getElementById('none').style.display=n?'none':'block'}
sel.addEventListener('change',f);f();
</script>
@endsection
