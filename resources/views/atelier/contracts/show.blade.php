@extends('atelier.layout')
@section('title', $c->title)
@section('content')
<h1>{{ $c->title }} <span class="badge">{{ ['active' => 'Actif', 'ended' => 'Terminé', 'cancelled' => 'Annulé'][$c->status] }}</span></h1>
@if(session('status'))<div class="banner">{{ session('status') }}</div>@endif
<div class="card"><p><strong>{{ $c->customer->name }}</strong> · du {{ $c->starts_on->format('d/m/Y') }} au {{ $c->ends_on->format('d/m/Y') }} · {{ $c->visits_per_year }} visite(s) par an · <strong>{{ \App\Support\Money::fcfa($c->price_fcfa) }}</strong> TTC</p>
<p class="muted">Équipements : @foreach($c->equipments as $e){{ $e->label }}@if(! $loop->last), @endif @endforeach</p>
@if($c->invoiced_at)<p class="ok">Facturé le {{ $c->invoiced_at->format('d/m/Y') }}.</p>@endif</div>
<div class="card"><h2>Visites</h2><table><tr><th>Date prévue</th><th>Statut</th><th>Interventions</th></tr>
@foreach($c->visits as $v)<tr><td>{{ $v->due_on->format('d/m/Y') }}</td><td><span class="badge">{{ ['planned' => 'Prévue', 'done' => 'Faite', 'skipped' => 'Ignorée'][$v->status] }}</span></td>
<td>@forelse($v->interventions as $i)<a href="{{ route('atelier.interventions.show', $i) }}">{{ $i->reference }}</a> @empty<span class="muted">pas encore créées (automatique 14 jours avant)</span>@endforelse</td></tr>@endforeach</table></div>
<div class="card no-print"><div class="row">
<form method="post" action="{{ route('atelier.contracts.generate', $c) }}">@csrf<button class="btn dark">Créer maintenant les interventions à venir</button></form>
@if(! $c->invoiced_at && $c->status === 'active')<form method="post" action="{{ route('atelier.contracts.invoice', $c) }}">@csrf<button class="btn">Créer la facture du contrat</button></form>@endif
@if($c->status === 'active')<form method="post" action="{{ route('atelier.contracts.cancel', $c) }}">@csrf<button class="btn line">Annuler le contrat</button></form>@endif</div></div>
@endsection
