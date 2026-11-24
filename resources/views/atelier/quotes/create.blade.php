@extends('atelier.layout')
@section('title', 'Nouveau devis')
@section('content')
<h1>Nouveau devis</h1>
<form method="post" action="{{ route('atelier.quotes.store') }}" class="card">@csrf
<div class="row"><label>Client<select name="customer_id" required><option value="">Choisir…</option>@foreach($customers as $c)<option value="{{ $c->id }}" @selected(old('customer_id') == $c->id)>{{ $c->name }}</option>@endforeach</select></label>
<label>Valable jusqu’au<input type="date" name="valid_until" value="{{ old('valid_until') }}"></label></div>
<h2>Lignes (prix TTC, TVA incluse)</h2>
<div id="lines"></div>
<p><button type="button" class="btn line" id="add">+ Ajouter une ligne</button></p>
<label>Notes<textarea name="notes" rows="2">{{ old('notes') }}</textarea></label>
<button class="btn" type="submit">Enregistrer le devis</button>
</form>
<template id="tpl"><div class="row" style="align-items:end"><label style="flex:3 1 220px">Désignation<input name="lines[__][description]" required></label>
<label>Quantité<input type="number" name="lines[__][quantity]" min="1" value="1" required></label>
<label>Prix unitaire TTC (FCFA)<input type="number" name="lines[__][unit_price_fcfa]" min="0" required></label>
<label>Groupe de taxe<select name="lines[__][tax_group]">@foreach($groups as $g)<option value="{{ $g }}" @selected($g === config('emecef.default_tax_group'))>{{ $g }}</option>@endforeach</select></label></div></template>
<script>
var n=0,box=document.getElementById('lines'),tpl=document.getElementById('tpl');
function add(){var d=document.createElement('div');d.innerHTML=tpl.innerHTML.replace(/__/g,n++);box.appendChild(d.firstElementChild)}
document.getElementById('add').addEventListener('click',add);add();
</script>
@endsection
