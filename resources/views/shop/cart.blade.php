@extends('layout')
@section('title', \App\Support\PublicCopy::text('cart_title', 'Mon panier'))
@section('content')
<section class="wrap shop-page"><p class="eyebrow">{{ \App\Support\PublicCopy::text('products_eyebrow', 'La boutique') }}</p><h1>{{ \App\Support\PublicCopy::text('cart_title', 'Mon panier') }}</h1>
@if(session('status'))<p class="status-message" role="status">{{ session('status') }}</p>@endif
@if($errors->any())<div class="form-errors err" role="alert">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif
@if($lines->isEmpty())<div class="empty-state"><span class="empty-mark" aria-hidden="true">—</span><h2>{{ \App\Support\PublicCopy::text('cart_empty_title', 'Votre panier est vide.') }}</h2><p>{{ \App\Support\PublicCopy::text('cart_empty_body', 'Parcourez la boutique pour découvrir les produits disponibles.') }}</p><a class="btn" href="{{ route('products') }}">Voir les produits</a></div>
@else
<div class="cart-sheet">@foreach($lines as $l)
<div class="cart-line">
<div class="cart-product"><strong>{{ $l['product']->name }}</strong><div class="muted">{{ \App\Support\Money::fcfa($l['product']->price_fcfa) }}</div></div>
<form class="quantity-form" method="post" action="{{ route('cart.update', $l['product']) }}">@csrf @method('PATCH')
<input type="number" name="quantity" min="0" max="20" value="{{ $l['qty'] }}" aria-label="Quantité" style="width:80px;margin:0"><button class="btn dark" style="padding:10px 14px">Mettre à jour</button></form>
<strong class="cart-line-total">{{ \App\Support\Money::fcfa($l['total']) }}</strong>
<form method="post" action="{{ route('cart.remove', $l['product']) }}">@csrf @method('DELETE')<button class="link-btn err">Retirer</button></form></div>
@endforeach
<div class="cart-summary"><div><span>Sous-total</span><strong>{{ \App\Support\Money::fcfa($subtotal) }}</strong></div><p>Frais de livraison calculés à l’étape suivante.</p></div></div>
<div class="cart-actions"><a class="btn" href="{{ route('checkout') }}">Passer la commande <span aria-hidden="true">↗</span></a><a class="more" href="{{ route('products') }}">Continuer mes achats</a></div>
@endif</section>
@endsection
