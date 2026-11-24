@extends('layout')
@section('title', \App\Support\PublicCopy::text('services_title', 'Nos services'))
@section('content')
<section class="wrap page-title"><p class="eyebrow">{{ \App\Support\PublicCopy::text('services_eyebrow', 'Des solutions concrètes') }}</p><h1>{{ \App\Support\PublicCopy::text('services_title', 'Nos services') }}</h1><p class="muted page-lead">{{ \App\Support\PublicCopy::text('services_lead', 'De l’installation au dépannage, nous vous aidons à garder vos équipements fiables et performants.') }}</p>
<div class="grid service-list">
@forelse($services as $s)<article class="card service-detail"><div class="service-card-meta"><span class="service-index">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span><x-service-visual :service="$s" /></div><h2>{{ $s->name }}</h2><p>{{ $s->summary }}</p>@if($s->description)<p class="muted">{{ $s->description }}</p>@endif
<a href="{{ route('requests.create', ['service' => $s->slug]) }}">{{ \App\Support\PublicCopy::text('services_request_link', 'Demander ce service') }} <span aria-hidden="true">↗</span></a></article>
@empty<div class="empty-state"><h2>{{ \App\Support\PublicCopy::text('services_empty_title', 'Nous restons à votre écoute') }}</h2><p>{{ \App\Support\PublicCopy::text('services_empty_body', 'Contactez-nous pour parler de votre équipement et de votre besoin.') }}</p><a class="btn" href="{{ route('contact') }}">Nous contacter</a></div>@endforelse
</div>
<div class="inline-cta"><p>{{ \App\Support\PublicCopy::text('services_cta', 'Vous ne savez pas par où commencer ? Décrivez-nous votre besoin.') }}</p><a class="btn" href="{{ route('requests.create') }}">{{ \App\Support\PublicCopy::text('shared_request_button', 'Demander une intervention') }}</a></div>
</section>
@endsection
