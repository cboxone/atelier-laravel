@extends('layout')
@section('content')
<section class="wrap confirmation"><p class="eyebrow">{{ \App\Support\PublicCopy::text('request_eyebrow', 'Votre demande') }}</p><div class="confirmation-mark" aria-hidden="true">✓</div><h1>{{ \App\Support\PublicCopy::text('thanks_title', 'Demande reçue') }}</h1>
<p>Votre numéro de suivi : <strong>{{ $reference }}</strong>. Nous vous rappelons au numéro indiqué.</p>
@if($urgent && \App\Models\Setting::phoneHref())<p>Pour une urgence, appelez aussi le <a href="{{ \App\Models\Setting::phoneHref() }}"><strong>{{ \App\Models\Setting::phoneNumber() }}</strong></a>.</p>@endif
</section>
@endsection
