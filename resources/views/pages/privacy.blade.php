@extends('layout')
@section('title', \App\Support\PublicCopy::text('privacy_title', 'Politique de confidentialité'))
@section('content')
<section class="wrap legal-page"><p class="eyebrow">{{ \App\Support\PublicCopy::text('privacy_eyebrow', 'Vos données') }}</p><h1>{{ \App\Support\PublicCopy::text('privacy_title', 'Politique de confidentialité') }}</h1>
<h2>Données collectées</h2>
<p>Quand vous demandez une intervention, nous enregistrons : l’équipement concerné, la description de la panne, votre quartier, votre numéro de téléphone ou WhatsApp, la date souhaitée et, si vous en envoyez, vos photos ou vidéos.</p>
<h2>Pourquoi</h2><p>Uniquement pour vous rappeler, préparer l’intervention ou le devis et assurer le suivi. Nous ne vendons pas vos données.</p>
<h2>Qui y accède</h2><p>Les personnes de l’atelier chargées de traiter votre demande. Vos photos ne sont pas accessibles publiquement.</p>
<h2>Durée de conservation</h2><p>{{ \App\Models\Setting::publicValue('retention_text') ?? 'Le temps nécessaire au traitement de votre demande, puis pour la durée exigée par la loi.' }}</p>
<h2>Vos droits</h2><p>Vous pouvez demander l’accès, la correction ou la suppression de vos données en contactant l’éditeur
@if(\App\Models\Setting::phoneNumber()) au {{ \App\Models\Setting::phoneNumber() }}@endif
@if(\App\Models\Setting::contactEmail()) ou à {{ \App\Models\Setting::contactEmail() }}@endif. Vous pouvez aussi saisir l’autorité béninoise de protection des données personnelles.</p>
</section>
@endsection
