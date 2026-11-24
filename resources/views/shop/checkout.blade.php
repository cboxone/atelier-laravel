@extends('layout')
@section('title', \App\Support\PublicCopy::text('checkout_title', 'Commande'))
@section('content')
<section class="wrap checkout-page"><div class="form-heading"><p class="eyebrow">{{ \App\Support\PublicCopy::text('checkout_eyebrow', 'La boutique · Étape finale') }}</p><h1>{{ \App\Support\PublicCopy::text('checkout_title', 'Finaliser la commande') }}</h1><p class="muted">{{ \App\Support\PublicCopy::text('checkout_lead', 'Renseignez vos informations de livraison et choisissez votre moyen de paiement.') }}</p></div>
@if($errors->any())<div class="form-errors err" role="alert">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif
@if($zones->isEmpty() || $methods->isEmpty())<div class="empty-state"><span class="empty-mark" aria-hidden="true">—</span><h2>{{ \App\Support\PublicCopy::text('checkout_unavailable', 'Commande en ligne momentanément indisponible') }}</h2><p>La commande en ligne n’est pas encore ouverte. @if(\App\Models\Setting::phoneHref())Appelez-nous au <a href="{{ \App\Models\Setting::phoneHref() }}"><strong>{{ \App\Models\Setting::phoneNumber() }}</strong></a>.@else<a href="{{ route('contact') }}">Nous contacter</a>.@endif</p></div>
@else
<div class="order-summary"><p class="eyebrow">Votre commande</p>@foreach($lines as $l)<div class="order-summary-line"><span>{{ $l['qty'] }} × {{ $l['product']->name }}</span><strong>{{ \App\Support\Money::fcfa($l['total']) }}</strong></div>@endforeach
<div class="order-summary-total"><span>Sous-total</span><strong>{{ \App\Support\Money::fcfa($subtotal) }}</strong></div></div>
<form class="form-panel checkout-form" method="post" action="{{ route('checkout.store') }}">@csrf
<input type="hidden" name="expected_subtotal" value="{{ $subtotal }}">
<input type="hidden" name="checkout_token" value="{{ $checkoutToken }}">
<input type="text" name="website" tabindex="-1" autocomplete="off" style="display:none" aria-hidden="true">
<label>Nom complet<input name="customer_name" value="{{ old('customer_name') }}" required></label>
<label>Téléphone (pour la livraison)<input name="phone" value="{{ old('phone') }}" required></label>
<label>E-mail (facultatif)<input type="email" name="email" value="{{ old('email') }}"></label>
<label>Zone de livraison<select name="zone_id" required><option value="">Choisir…</option>@foreach($zones as $z)<option value="{{ $z->id }}" @selected(old('zone_id') == $z->id)>{{ $z->name }} : livraison {{ \App\Support\Money::fcfa($z->delivery_fee_fcfa) }}</option>@endforeach</select></label>
<label>Adresse et repère<textarea name="address" rows="3" required>{{ old('address') }}</textarea></label>
<label>Instructions pour le livreur (facultatif)<input name="delivery_notes" value="{{ old('delivery_notes') }}"></label>
<label class="check"><input type="checkbox" name="want_installation" value="1" @checked(old('want_installation'))> Je souhaite aussi la pose par un technicien (devis à part)</label>
<fieldset style="border:1px solid var(--line);border-radius:8px;margin:0 0 14px"><legend>Paiement</legend>
@foreach($methods as $m)<label class="check"><input type="radio" name="payment_method" value="{{ $m->code }}" @checked(old('payment_method', $methods->first()->code) === $m->code) required> {{ $m->label }}@if($m->max_amount_fcfa) <small class="muted">(jusqu’à {{ \App\Support\Money::fcfa($m->max_amount_fcfa) }})</small>@endif</label>@endforeach</fieldset>
<label class="check"><input type="checkbox" name="consent" value="1" required @checked(old('consent'))> J’accepte que ces informations servent à traiter ma commande (<a href="{{ route('privacy') }}" style="text-decoration:underline">confidentialité</a>).</label>
<button class="btn" type="submit">Confirmer la commande</button></form>
@endif</section>
@endsection
