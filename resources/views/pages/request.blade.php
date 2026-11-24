@extends('layout')
@section('title', \App\Support\PublicCopy::text('request_title', 'Demander une intervention'))
@section('content')
<section class="wrap form-page"><div class="form-heading"><p class="eyebrow">{{ \App\Support\PublicCopy::text('request_eyebrow', 'Nous sommes à votre écoute') }}</p><h1>{{ \App\Support\PublicCopy::text('request_title', 'Demander une intervention ou un devis') }}</h1><p class="muted">{{ \App\Support\PublicCopy::text('request_lead', 'Quelques informations nous aideront à mieux comprendre votre besoin et à vous recontacter.') }}</p></div>
@if($errors->any())<div class="form-errors err" role="alert"><strong>Vérifiez les informations ci-dessous :</strong>@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif
<form class="form-panel request-form" method="post" action="{{ route('requests.store') }}" enctype="multipart/form-data">@csrf
<input type="text" name="website" tabindex="-1" autocomplete="off" style="display:none" aria-hidden="true">
<label>Type de service<select name="service_id"><option value="">Je ne sais pas</option>@foreach($services as $s)<option value="{{ $s->id }}" @selected(old('service_id', $preselect) == $s->id)>{{ $s->name }}</option>@endforeach</select></label>
<label>Équipement<input name="equipment" value="{{ old('equipment') }}" required></label>
<label class="form-wide">Problème<textarea name="problem" rows="4" required>{{ old('problem') }}</textarea></label>
<label>Quartier<input name="district" value="{{ old('district') }}" required></label>
<label>Téléphone ou WhatsApp<input name="phone" value="{{ old('phone') }}" required></label>
<label>Date souhaitée<input type="date" name="preferred_date" value="{{ old('preferred_date') }}"></label>
<label class="check urgent-check form-wide"><input type="checkbox" name="is_urgent" value="1" @checked(old('is_urgent', $urgent))> C’est une urgence</label>
<label class="form-wide">Photos ou vidéo (facultatif, 3 maximum)<input type="file" name="attachments[]" multiple accept="image/*,video/mp4"></label>
<label class="check form-wide"><input type="checkbox" name="consent" value="1" required @checked(old('consent'))> J’accepte que ces informations servent à traiter ma demande (<a href="{{ route('privacy') }}">politique de confidentialité</a>).</label>
<button class="btn" type="submit">Envoyer ma demande <span aria-hidden="true">↗</span></button></form></section>
@endsection
