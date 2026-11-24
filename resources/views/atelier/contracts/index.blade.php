@extends('atelier.layout')
@section('title', 'Contrats')
@section('content')
<h1>Contrats d’entretien</h1><p><a class="btn" href="{{ route('atelier.contracts.create') }}">Nouveau contrat</a></p>
<div class="card"><table><tr><th>Contrat</th><th>Client</th><th>Période</th><th class="num">Visites / an</th><th>Statut</th></tr>
@forelse($contracts as $c)<tr><td><a href="{{ route('atelier.contracts.show', $c) }}">{{ $c->title }}</a></td><td>{{ $c->customer->name }}</td><td>{{ $c->starts_on->format('d/m/Y') }} → {{ $c->ends_on->format('d/m/Y') }}</td><td class="num">{{ $c->visits_per_year }}</td><td><span class="badge">{{ ['active' => 'Actif', 'ended' => 'Terminé', 'cancelled' => 'Annulé'][$c->status] }}</span></td></tr>@empty<tr><td colspan="5" class="muted">Aucun contrat.</td></tr>@endforelse</table>{{ $contracts->links() }}</div>
@endsection
