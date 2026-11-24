@extends('atelier.layout')
@section('title', 'Tableau de bord')
@section('content')
<h1>Tableau de bord</h1>
@if($failed > 0)<div class="alert" role="alert">{{ $failed }} facture(s) en échec d’émission : à vérifier dans <a href="{{ route('atelier.invoices.index') }}">Factures</a>.</div>@endif
<div class="row">
<div class="card"><h2>Factures à encaisser</h2><p style="font-size:22px;margin:0"><strong>{{ \App\Support\Money::fcfa((int) $unpaidSum) }}</strong></p><p class="muted">{{ $unpaidCount }} facture(s) émise(s) non soldée(s)</p></div>
<div class="card"><h2>Contrats d’entretien</h2><p style="font-size:22px;margin:0"><strong>{{ $visits }}</strong> visite(s)</p><p class="muted">à effectuer dans les 14 jours · <a href="{{ route('atelier.contracts.index') }}">Voir les contrats</a></p></div>
<div class="card"><h2>Interventions du jour</h2>
@forelse($today as $i)<p style="margin:4px 0"><a href="{{ route('atelier.interventions.show', $i) }}">{{ $i->scheduled_at->format('H:i') }} · {{ $i->customer->name }} · {{ $i->title }}</a></p>@empty<p class="muted">Aucune intervention planifiée aujourd’hui.</p>@endforelse</div>
</div>
<div class="card"><h2>Nouvelles demandes du site</h2>
@forelse($newRequests as $r)<p style="margin:6px 0">@if($r->is_urgent)<span class="err"><strong>URGENT</strong></span> @endif<strong>{{ $r->reference }}</strong> · {{ $r->district }} · {{ $r->equipment }} · {{ $r->phone }}</p>@empty<p class="muted">Aucune nouvelle demande.</p>@endforelse
<p><a class="btn dark" href="{{ route('atelier.interventions.create') }}">Nouvelle intervention</a> <a class="btn line" href="{{ route('atelier.quotes.create') }}">Nouveau devis</a></p></div>
@endsection
