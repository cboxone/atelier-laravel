@extends('atelier.layout')
@section('title', 'Devis')
@section('content')
<h1>Devis</h1><p><a class="btn" href="{{ route('atelier.quotes.create') }}">Nouveau devis</a></p>
<div class="card"><table><tr><th>N°</th><th>Client</th><th>Statut</th><th>Valable jusqu’au</th></tr>
@forelse($quotes as $q)<tr><td><a href="{{ route('atelier.quotes.show', $q) }}">{{ $q->number }}</a></td><td>{{ $q->customer->name }}</td><td><span class="badge">{{ \App\Models\Quote::STATUSES[$q->status] }}</span></td><td>{{ $q->valid_until?->format('d/m/Y') }}</td></tr>@empty<tr><td colspan="4" class="muted">Aucun devis.</td></tr>@endforelse</table>{{ $quotes->links() }}</div>
@endsection
