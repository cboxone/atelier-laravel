@extends('atelier.layout')
@section('title', 'Factures')
@section('content')
<h1>Factures</h1>
<div class="card"><table><tr><th>N°</th><th>Type</th><th>Client</th><th>Statut</th><th class="num">Total</th><th class="num">Reste à payer</th></tr>
@forelse($invoices as $i)<tr><td><a href="{{ route('atelier.invoices.show', $i) }}">{{ $i->number ?? 'Brouillon #'.$i->id }}</a></td><td>{{ $i->type === 'FA' ? 'Avoir' : 'Facture' }}</td><td>{{ $i->customer->name }}</td>
<td><span class="badge">{{ \App\Models\Invoice::STATUSES[$i->status] }}</span>@if($i->status === 'issued' && ! $i->normalized) <span class="err">non normalisée</span>@endif</td>
<td class="num">{{ \App\Support\Money::fcfa($i->total_fcfa) }}</td><td class="num">{{ $i->type === 'FV' && $i->status === 'issued' ? \App\Support\Money::fcfa($i->balance_fcfa) : '' }}</td></tr>@empty<tr><td colspan="6" class="muted">Aucune facture.</td></tr>@endforelse</table>{{ $invoices->links() }}</div>
@endsection
