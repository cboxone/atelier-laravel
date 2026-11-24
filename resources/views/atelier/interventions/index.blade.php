@extends('atelier.layout')
@section('title', 'Interventions')
@section('content')
<h1>Interventions</h1><p><a class="btn" href="{{ route('atelier.interventions.create') }}">Nouvelle intervention</a></p>
<div class="card"><table><tr><th>Réf.</th><th>Client</th><th>Objet</th><th>Technicien</th><th>Statut</th></tr>
@forelse($interventions as $i)<tr><td><a href="{{ route('atelier.interventions.show', $i) }}">{{ $i->reference }}</a></td><td>{{ $i->customer->name }}</td><td>{{ $i->title }}</td><td>{{ $i->technician?->name }}</td><td><span class="badge">{{ \App\Models\Intervention::STATUSES[$i->status] }}</span></td></tr>@empty<tr><td colspan="5" class="muted">Aucune intervention.</td></tr>@endforelse</table>{{ $interventions->links() }}</div>
@endsection
