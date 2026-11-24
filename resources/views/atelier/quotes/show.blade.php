@extends('atelier.layout')
@section('title', 'Devis '.$quote->number)
@section('content')
<h1>Devis {{ $quote->number }} <span class="badge">{{ \App\Models\Quote::STATUSES[$quote->status] }}</span></h1>
<div class="card"><div style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:14px"><div><strong>{{ \App\Models\Setting::get('business_name') }}</strong><br>{{ \App\Models\Setting::get('address') }}<br>Tél. {{ \App\Models\Setting::get('phone') }}@if(config('emecef.ifu'))<br>IFU {{ config('emecef.ifu') }}@endif</div><div class="num">@if($quote->created_at)Devis du {{ $quote->created_at?->format('d/m/Y') }}@endif</div></div>
<p><span class="muted">Client :</span> <strong>{{ $quote->customer->name }}</strong> · {{ $quote->customer->phone }}@if($quote->valid_until) · valable jusqu’au {{ $quote->valid_until->format('d/m/Y') }}@endif</p>
<table><tr><th>Désignation</th><th class="num">Qté</th><th class="num">PU TTC</th><th class="num">Total TTC</th></tr>
@foreach($quote->lines as $l)<tr><td>{{ $l->description }}</td><td class="num">{{ $l->quantity }}</td><td class="num">{{ \App\Support\Money::fcfa($l->unit_price_fcfa) }}</td><td class="num">{{ \App\Support\Money::fcfa($l->quantity * $l->unit_price_fcfa) }}</td></tr>@endforeach
<tr><td colspan="3" class="num"><strong>Total TTC</strong></td><td class="num"><strong>{{ \App\Support\Money::fcfa($totals['total']) }}</strong></td></tr>
<tr><td colspan="3" class="num muted">dont TVA</td><td class="num muted">{{ \App\Support\Money::fcfa($totals['tax']) }}</td></tr></table>
@if($quote->notes)<p class="muted">{{ $quote->notes }}</p>@endif
<p class="muted">Devis : document commercial, pas une facture.</p></div>
<div class="card no-print"><div class="row">
@foreach($next as $s)<form method="post" action="{{ route('atelier.quotes.status', $quote) }}">@csrf<input type="hidden" name="status" value="{{ $s }}"><button class="btn {{ $s === 'refused' ? 'line' : 'dark' }}">Marquer « {{ \App\Models\Quote::STATUSES[$s] }} »</button></form>@endforeach
@if($quote->status === 'accepted')<form method="post" action="{{ route('atelier.quotes.invoice', $quote) }}">@csrf<button class="btn">Créer la facture</button></form>@endif
<button class="btn line" onclick="window.print()" type="button">Imprimer</button></div></div>
@endsection
