@extends('atelier.layout')
@section('title', $invoice->number ?? 'Facture')
@section('content')
<h1>{{ $invoice->type === 'FA' ? 'Facture d’avoir' : 'Facture' }} {{ $invoice->number ?? '(brouillon)' }} <span class="badge">{{ \App\Models\Invoice::STATUSES[$invoice->status] }}</span></h1>
@if($invoice->status === 'issued' && ! $invoice->normalized)<div class="banner">Document NON normalisé : il ne remplace pas une facture normalisée e-MECeF.</div>@endif
@if($invoice->status === 'failed')<div class="alert" role="alert">Émission en échec : {{ $invoice->error }}</div>@endif
<div class="card"><div style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:14px"><div><strong>{{ \App\Models\Setting::get('business_name') }}</strong><br>{{ \App\Models\Setting::get('address') }}<br>Tél. {{ \App\Models\Setting::get('phone') }}@if(config('emecef.ifu'))<br>IFU {{ config('emecef.ifu') }}@endif</div><div class="num">@if($invoice->issued_at)Émise le {{ $invoice->issued_at->format('d/m/Y') }}@endif</div></div>
<p><span class="muted">Facturé à :</span> <strong>{{ $invoice->customer->name }}</strong>@if($invoice->customer->ifu) · IFU {{ $invoice->customer->ifu }}@endif @if($invoice->customer->phone)· {{ $invoice->customer->phone }}@endif</p>
@if($invoice->original)<p class="muted">Avoir sur la facture {{ $invoice->original->number }}</p>@endif
<table><tr><th>Désignation</th><th class="num">Qté</th><th class="num">PU TTC</th><th>Taxe</th><th class="num">Total TTC</th></tr>
@foreach($invoice->lines as $l)<tr><td>{{ $l->description }}</td><td class="num">{{ $l->quantity }}</td><td class="num">{{ \App\Support\Money::fcfa($l->unit_price_fcfa) }}</td><td>{{ $l->tax_group }}</td><td class="num">{{ \App\Support\Money::fcfa($l->quantity * $l->unit_price_fcfa) }}</td></tr>@endforeach
<tr><td colspan="4" class="num"><strong>Total TTC</strong></td><td class="num"><strong>{{ \App\Support\Money::fcfa($totals['total']) }}</strong></td></tr>
<tr><td colspan="4" class="num muted">dont TVA</td><td class="num muted">{{ \App\Support\Money::fcfa($totals['tax']) }}</td></tr></table>
@if($invoice->normalized)
<div style="display:flex;gap:16px;flex-wrap:wrap;align-items:flex-start;margin-top:14px"><div id="qr" data-qr="{{ $invoice->emecef_qr }}" aria-label="QR code de la facture normalisée" role="img"></div>
<div><p style="margin:0"><strong>Facture normalisée e-MECeF</strong></p><p class="muted" style="margin:4px 0">Code MECeF/DGI : {{ $invoice->emecef_code }}<br>NIM : {{ $invoice->emecef_nim }} · Compteurs : {{ $invoice->emecef_counters }}<br>Date : {{ $invoice->emecef_datetime?->format('d/m/Y H:i:s') }}</p></div></div>
<script src="/js/qrcode.js"></script><script>var q=document.getElementById('qr');var c=qrcode(0,'M');c.addData(q.dataset.qr);c.make();q.innerHTML=c.createSvgTag(4,0);</script>
@endif</div>

<div class="card no-print">
@if(in_array($invoice->status, ['draft', 'failed']))<form method="post" action="{{ route('atelier.invoices.issue', $invoice) }}">@csrf<button class="btn">Émettre la facture{{ config('emecef.enabled') ? ' (envoi à l’e-MECeF)' : '' }}</button></form>
@if(! config('emecef.enabled') && config('emecef.required'))<p class="err">e-MECeF non activé : l’émission sera refusée tant que EMECEF_ENABLED n’est pas à true.</p>@endif @endif
@if($invoice->type === 'FV')<h2 style="margin-top:14px">Paiements</h2>
<table>@foreach($invoice->payments as $p)<tr><td>{{ $p->paid_at->format('d/m/Y') }}</td><td>{{ $methods[$p->method] }} {{ $p->reference }}</td><td class="num">{{ \App\Support\Money::fcfa($p->amount_fcfa) }}</td></tr>@endforeach
<tr><td colspan="2" class="num"><strong>Reste à payer</strong></td><td class="num"><strong>{{ \App\Support\Money::fcfa($invoice->balance_fcfa) }}</strong></td></tr></table>
<form method="post" action="{{ route('atelier.invoices.pay', $invoice) }}" class="row" style="align-items:end;margin-top:10px">@csrf
<label>Moyen<select name="method">@foreach($methods as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach</select></label>
<label>Montant (FCFA)<input type="number" name="amount_fcfa" min="1" required></label><label>Référence<input name="reference"></label><button class="btn dark">Enregistrer le paiement</button></form>@endif
@if($invoice->type === 'FV' && $invoice->status === 'issued')<form method="post" action="{{ route('atelier.invoices.credit', $invoice) }}" style="margin-top:12px">@csrf<button class="btn line">Créer un avoir total</button></form>@endif
<button class="btn line" onclick="window.print()" type="button" style="margin-top:12px">Imprimer</button></div>
@endsection
