@extends('layout')
@section('title', \App\Support\PublicCopy::text('contact_title', 'Contact'))
@section('content')
<section class="wrap page-title"><p class="eyebrow">{{ \App\Support\PublicCopy::text('contact_eyebrow', 'Une question ?') }}</p><h1>{{ \App\Support\PublicCopy::text('contact_title', 'Contact') }}</h1><p class="muted page-lead">{{ \App\Support\PublicCopy::text('contact_lead', 'Contactez-nous au sujet de votre équipement ou demandez une intervention.') }}</p>
@php($hasContactDetails = \App\Models\Setting::phoneHref() || \App\Models\Setting::whatsappHref() || \App\Models\Setting::contactEmail() || \App\Models\Setting::publicAddress())
@if($hasContactDetails || $hours->isNotEmpty())
<div class="contact-grid">
@if($hasContactDetails)<div class="contact-primary"><p class="eyebrow">Nous joindre</p>@if(\App\Models\Setting::phoneHref())<a class="contact-number" href="{{ \App\Models\Setting::phoneHref() }}">{{ \App\Models\Setting::phoneNumber() }} <span aria-hidden="true">↗</span></a>@endif
@if(\App\Models\Setting::whatsappHref() || \App\Models\Setting::contactEmail())<div class="contact-options">@if(\App\Models\Setting::whatsappHref())<a href="{{ \App\Models\Setting::whatsappHref() }}" rel="noopener">Écrire sur WhatsApp <span aria-hidden="true">↗</span></a>@endif @if(\App\Models\Setting::contactEmail())<a href="mailto:{{ \App\Models\Setting::contactEmail() }}">{{ \App\Models\Setting::contactEmail() }}</a>@endif</div>@endif
@if(\App\Models\Setting::publicAddress())<div class="contact-address"><strong>Adresse</strong><p>{{ \App\Models\Setting::publicAddress() }}</p><a href="https://www.google.com/maps/search/?api=1&query={{ urlencode(\App\Models\Setting::publicAddress()) }}" rel="noopener">Voir l’itinéraire ↗</a></div>@endif
</div>@endif
@if($hours->isNotEmpty())<div class="card contact-hours"><p class="eyebrow">{{ \App\Support\PublicCopy::text('contact_hours_eyebrow', 'Horaires d’ouverture') }}</p><h2>{{ \App\Support\PublicCopy::text('contact_hours_title', 'À quel moment passer ?') }}</h2><div class="hours-list">
@foreach($hours as $h)<div><span>{{ $h->day_name }}</span><strong>{{ $h->label }}</strong></div>@endforeach</div>
</div>@endif
</div>
@endif
@unless($hasContactDetails)<div class="empty-state"><span class="empty-mark" aria-hidden="true">—</span><h2>{{ \App\Support\PublicCopy::text('contact_missing_title', 'Coordonnées non disponibles') }}</h2><p>{{ \App\Support\PublicCopy::text('contact_missing_body', 'Les coordonnées de l’atelier ne sont pas encore renseignées. Vous pouvez tout de même nous décrire votre besoin.') }}</p></div>@endunless
<div class="inline-cta"><p>{{ \App\Support\PublicCopy::text('contact_cta', 'Besoin d’une intervention ou d’un devis ? Décrivez-nous votre situation.') }}</p><a class="btn" href="{{ route('requests.create') }}">{{ \App\Support\PublicCopy::text('contact_button', 'Faire une demande') }}</a></div></section>
@endsection
