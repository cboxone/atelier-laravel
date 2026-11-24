@extends('technician.layout')
@section('title', 'Mes interventions')
@section('content')
<h1>Mes interventions</h1>
@forelse($interventions as $i)
<a href="{{ route('tech.show', $i) }}" class="card" style="display:block;text-decoration:none;color:inherit">
<p style="margin:0 0 4px"><span class="badge">{{ $i->status === 'in_progress' ? 'En cours' : 'Planifiée' }}</span> @if($i->scheduled_at)<strong>{{ $i->scheduled_at->format('d/m H:i') }}</strong>@endif</p>
<p style="margin:0"><strong>{{ $i->customer->name }}</strong></p><p class="muted" style="margin:0">{{ $i->title }}@if($i->equipment) · {{ $i->equipment->label }}@endif</p></a>
@empty<div class="card"><p class="muted" style="margin:0">Aucune intervention à faire pour le moment.</p></div>@endforelse
@endsection
