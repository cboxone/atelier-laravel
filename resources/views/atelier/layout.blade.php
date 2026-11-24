<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>@yield('title', 'Atelier') · Espace atelier</title>
<style>
:root{--ink:#0B2A3C;--muted:#33566a;--ice:#F2F8FA;--line:#D5E5EB;--cta:#C2410C;--accent:#0E7490}
*{box-sizing:border-box}body{margin:0;font-family:Inter,system-ui,sans-serif;color:var(--ink);background:var(--ice)}
a{color:inherit}.wrap{max-width:1180px;margin:0 auto;padding:0 clamp(12px,3vw,28px)}
header{background:var(--ink);color:#fff}header .wrap{display:flex;flex-wrap:wrap;align-items:center;gap:4px 18px}
header a{color:#fff;text-decoration:none;display:inline-block;padding:12px 4px;min-height:44px}header a.cur{font-weight:700;text-decoration:underline}
h1{font-size:26px;margin:20px 0 12px}h2{font-size:18px;margin:0 0 10px}main{padding-bottom:40px}
.card{background:#fff;border:1px solid var(--line);border-radius:12px;padding:16px;margin-bottom:16px;overflow-x:auto}
table{width:100%;border-collapse:collapse;font-size:15px}th{text-align:left;color:var(--muted);font-weight:600}th,td{padding:8px 6px;border-top:1px solid var(--line);vertical-align:top}
.sr{position:absolute;left:-9999px}.num{text-align:right;white-space:nowrap}.muted{color:var(--muted)}.err{color:#B42318}.ok{color:#166534;font-weight:600}
.btn{display:inline-block;background:var(--cta);color:#fff;border:0;border-radius:8px;padding:10px 16px;font:inherit;font-weight:600;cursor:pointer;min-height:44px;text-decoration:none}.btn.dark{background:var(--ink)}.btn.line{background:#fff;color:var(--ink);border:1px solid var(--ink)}
label{display:block;margin-bottom:10px;color:var(--muted);font-size:14px}input,select,textarea{display:block;width:100%;padding:10px;margin-top:3px;border:1px solid var(--line);border-radius:8px;font:inherit;min-height:44px}
.row{display:flex;flex-wrap:wrap;gap:0 12px}.row>*{flex:1 1 150px}.badge{display:inline-block;padding:2px 10px;border-radius:99px;background:#E3EEF2;font-size:13px;font-weight:600}
.alert{background:#FEF3F2;border:1px solid #FDA29B;color:#B42318;border-radius:8px;padding:10px 14px;margin-bottom:14px}
.banner{background:#FFFAEB;border:1px solid #FEC84B;color:#7A2E0E;border-radius:8px;padding:10px 14px;margin-bottom:14px;font-weight:600}
@media print{header,.no-print{display:none!important}body{background:#fff}.card{border:0;padding:0}}
</style>
</head>
<body>
<header class="no-print"><div class="wrap">
<a href="{{ route('atelier.dashboard') }}">Atelier</a><a href="{{ route('atelier.quotes.index') }}">Devis</a><a href="{{ route('atelier.invoices.index') }}">Factures</a><a href="{{ route('atelier.interventions.index') }}">Interventions</a><a href="{{ route('atelier.contracts.index') }}">Contrats</a><a href="/admin" style="margin-left:auto">Administration</a>
</div></header>
<main class="wrap">
@if($errors->any())<div class="alert no-print" role="alert">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif
@yield('content')
</main>
<script>document.querySelectorAll('.card').forEach(function(c){if(c.scrollWidth>c.clientWidth){c.setAttribute('tabindex','0');c.setAttribute('role','region');c.setAttribute('aria-label','Tableau défilant')}})</script>
</body>
</html>
