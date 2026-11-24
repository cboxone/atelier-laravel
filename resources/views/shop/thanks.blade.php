@extends('layout')
@section('title', \App\Support\PublicCopy::text('shop_thanks_title', 'Commande reçue'))
@section('content')
<section class="wrap confirmation"><p class="eyebrow">{{ \App\Support\PublicCopy::text('products_eyebrow', 'La boutique') }}</p><div class="confirmation-mark" aria-hidden="true">✓</div><h1>{{ \App\Support\PublicCopy::text('shop_thanks_title', 'Commande reçue') }}</h1>
@if($warning)<p class="form-errors err"><strong>{{ $warning }}</strong></p>@endif
<p>Votre numéro de commande : <strong>{{ $order->reference }}</strong>.</p><p>Total : <strong>{{ \App\Support\Money::fcfa($order->total_fcfa) }}</strong>.</p>
@if($method)<div class="card payment-instructions"><strong>Paiement : {{ $method->label }}</strong>@if($instructions)<p style="white-space:pre-line">{{ $instructions }}</p>@endif</div>@endif
<p>Notez ce numéro : il vous servira, avec votre téléphone, à <a href="{{ route('orders.track') }}" class="text-link">suivre votre commande</a>. Nous vous appelons pour confirmer la livraison.</p><a class="btn" href="{{ route('orders.track') }}">Suivre ma commande</a></section>
@endsection
