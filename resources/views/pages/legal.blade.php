@extends('layout')
@section('title', \App\Support\PublicCopy::text('legal_title', 'Mentions légales'))
@section('content')
<section class="wrap legal-page"><p class="eyebrow">{{ \App\Support\PublicCopy::text('legal_eyebrow', 'Informations') }}</p><h1>{{ \App\Support\PublicCopy::text('legal_title', 'Mentions légales') }}</h1>
<h2>Éditeur du site</h2>
<p>@if(\App\Models\Setting::publicValue('business_name')){{ \App\Models\Setting::publicValue('business_name') }}<br>@endif
@if(\App\Models\Setting::publicAddress()){{ \App\Models\Setting::publicAddress() }}<br>@endif
@if(\App\Models\Setting::phoneNumber())Téléphone : {{ \App\Models\Setting::phoneNumber() }}@endif
@if(\App\Models\Setting::contactEmail())<br>E-mail : {{ \App\Models\Setting::contactEmail() }}@endif
@if(\App\Models\Setting::publicValue('ifu'))<br>IFU : {{ \App\Models\Setting::publicValue('ifu') }}@endif
@if(\App\Models\Setting::publicValue('rccm'))<br>RCCM : {{ \App\Models\Setting::publicValue('rccm') }}@endif
@if(\App\Models\Setting::publicValue('publisher_name'))<br>Responsable de la publication : {{ \App\Models\Setting::publicValue('publisher_name') }}@endif</p>
@if(\App\Models\Setting::publicValue('hosting_provider'))<h2>Hébergement</h2><p>{{ \App\Models\Setting::publicValue('hosting_provider') }}</p>@endif
<h2>Propriété du contenu</h2><p>Les textes, photos et logos du site appartiennent à l’éditeur et ne peuvent être réutilisés sans son accord.</p>
</section>
@endsection
