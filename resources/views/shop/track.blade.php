@extends('layout')
@section('title', \App\Support\PublicCopy::text('track_title', 'Suivre ma commande'))
@section('content')
<section class="wrap tracking-page"><div class="form-heading"><p class="eyebrow">{{ \App\Support\PublicCopy::text('products_eyebrow', 'La boutique') }}</p><h1>{{ \App\Support\PublicCopy::text('track_title', 'Suivre ma commande') }}</h1><p class="muted">{{ \App\Support\PublicCopy::text('track_lead', 'Retrouvez l’avancement avec le numéro de commande et le téléphone utilisés lors de l’achat.') }}</p></div>
@if(isset($order))
<div class="card tracking-result"><p class="eyebrow">Commande {{ $order->reference }}</p><p class="tracking-status">{{ $order->status_label }}</p>
@foreach($order->items as $i)<div class="order-summary-line"><span>{{ $i->quantity }} × {{ $i->name }}</span><span>{{ \App\Support\Money::fcfa($i->line_total_fcfa) }}</span></div>@endforeach
<p class="tracking-total"><strong>Total : {{ \App\Support\Money::fcfa($order->total_fcfa) }}</strong><span>Paiement : {{ ['pending' => 'en attente', 'paid' => 'reçu', 'failed' => 'échoué', 'refunded' => 'remboursé'][$order->payment_status] ?? '' }}</span></p></div>
@else
@if($errors->any())<div class="form-errors err" role="alert">{{ $errors->first() }}</div>@endif
<form class="form-panel tracking-form" method="post" action="{{ route('orders.lookup') }}">@csrf
<label>Numéro de commande<input name="reference" value="{{ old('reference') }}" required></label>
<label>Téléphone utilisé pour la commande<input name="phone" value="{{ old('phone') }}" required></label>
<button class="btn" type="submit">Voir ma commande <span aria-hidden="true">↗</span></button></form>
@endif</section>
@endsection
