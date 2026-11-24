@extends('atelier.layout')
@section('title', 'Nouvelle intervention')
@section('content')
<h1>Nouvelle intervention</h1>
<form method="post" action="{{ route('atelier.interventions.store') }}" class="card">@csrf
<div class="row"><label>Client<select name="customer_id" required><option value="">Choisir…</option>@foreach($customers as $c)<option value="{{ $c->id }}" @selected(old('customer_id') == $c->id)>{{ $c->name }}</option>@endforeach</select></label>
<label>Équipement<select name="equipment_id"><option value="">Aucun</option>@foreach($equipments as $e)<option value="{{ $e->id }}" @selected(old('equipment_id') == $e->id)>{{ $e->label }} ({{ $e->customer->name }})</option>@endforeach</select></label></div>
<div class="row"><label style="flex:2 1 260px">Objet<input name="title" value="{{ old('title') }}" required></label>
<label>Technicien<select name="technician_id"><option value="">Non assigné</option>@foreach($users as $u)<option value="{{ $u->id }}" @selected(old('technician_id') == $u->id)>{{ $u->name }}</option>@endforeach</select></label>
<label>Date prévue<input type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at') }}"></label></div>
<button class="btn" type="submit">Créer l’intervention</button></form>
@endsection
