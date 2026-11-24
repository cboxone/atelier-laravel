@extends('layout')
@section('title', \App\Support\PublicCopy::text('contracts_title', 'Contrats d’entretien'))
@section('content')
<section class="wrap page-title"><p class="eyebrow">{{ \App\Support\PublicCopy::text('contracts_eyebrow', 'Préserver vos équipements') }}</p><h1>{{ \App\Support\PublicCopy::text('contracts_title', 'Contrats d’entretien') }}</h1>
<p class="muted page-lead">{{ \App\Support\PublicCopy::text('contracts_lead', 'Des visites régulières pour garder votre froid et votre climatisation en bon état.') }}</p>
@if($plans->isEmpty())<div class="empty-state"><span class="empty-mark" aria-hidden="true">—</span><h2>{{ \App\Support\PublicCopy::text('contracts_empty_title', 'Une formule adaptée à votre besoin') }}</h2><p>{{ \App\Support\PublicCopy::text('contracts_empty_body', 'Nos formules sont en cours de définition. Demandez un devis personnalisé.') }}</p><a class="btn" href="{{ route('requests.create', ['service' => 'entretien-et-contrats']) }}">Parler de mon équipement</a></div>
@else<div class="grid contract-list">@foreach($plans as $p)<article class="card contract-card"><p class="eyebrow">Entretien préventif</p><h2>{{ $p->name }}</h2><p>{{ $p->description }}</p>@if($p->visits_per_year)<p class="contract-visits"><span aria-hidden="true">↻</span> {{ $p->visits_per_year }} visite(s) par an</p>@endif<strong class="product-price">{{ $p->price_label }}</strong></article>@endforeach</div>
<p class="contract-action"><a class="btn" href="{{ route('requests.create', ['service' => 'entretien-et-contrats']) }}">Demander un contrat</a></p>@endif
</section>
@endsection
