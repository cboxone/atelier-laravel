<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#0B2A3C">
<link rel="manifest" href="/manifest.webmanifest">
<link rel="icon" href="/icons/icon-192.png">
<title>@yield('title', 'Technicien')</title>
<style>
:root{--ink:#0B2A3C;--muted:#33566a;--ice:#F2F8FA;--line:#D5E5EB;--cta:#C2410C}
*{box-sizing:border-box}body{margin:0;font-family:system-ui,sans-serif;color:var(--ink);background:var(--ice);font-size:17px;line-height:1.4}
header{background:var(--ink);color:#fff;padding:10px 14px;display:flex;align-items:center;justify-content:space-between;gap:10px}header a,header button{color:#fff}
header .out{background:none;border:1px solid #fff;border-radius:8px;min-height:44px;padding:0 14px;font:inherit}
main{padding:14px;max-width:640px;margin:0 auto 40px}h1{font-size:22px;margin:6px 0 12px}h2{font-size:18px;margin:0 0 10px}
.card{background:#fff;border:1px solid var(--line);border-radius:14px;padding:14px;margin-bottom:14px;overflow-x:auto}
.btn{display:block;width:100%;text-align:center;background:var(--cta);color:#fff;border:0;border-radius:12px;padding:14px;min-height:52px;font:inherit;font-weight:700;text-decoration:none;cursor:pointer}.btn.dark{background:var(--ink)}.btn.line{background:#fff;color:var(--ink);border:2px solid var(--ink)}
label{display:block;margin-bottom:12px;color:var(--muted);font-size:15px}input,select,textarea{display:block;width:100%;padding:12px;margin-top:4px;border:1px solid #8aa4b1;border-radius:10px;font:inherit;min-height:48px;background:#fff;color:var(--ink)}
table{width:100%;border-collapse:collapse;font-size:15px}td,th{padding:6px 4px;border-top:1px solid var(--line);text-align:left;vertical-align:top}.num{text-align:right;white-space:nowrap}
.muted{color:var(--muted)}.alert{background:#FEF3F2;border:1px solid #FDA29B;color:#B42318;border-radius:10px;padding:10px 12px;margin-bottom:12px}.badge{display:inline-block;padding:2px 10px;border-radius:99px;background:#E3EEF2;font-size:14px;font-weight:600}
a.tel{display:inline-block;padding:10px 0;font-weight:700;color:var(--ink)}
.photos{display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin-bottom:10px}.photos img{width:100%;aspect-ratio:1;object-fit:cover;border-radius:8px;background:#E3EEF2}
#sig{width:100%;height:170px;border:2px dashed #8aa4b1;border-radius:10px;background:#fff;touch-action:none}
.sr{position:absolute;left:-9999px}
</style>
</head>
<body>
<header><strong>Atelier · Technicien</strong>@auth<form method="post" action="{{ route('tech.logout') }}">@csrf<button class="out">Sortir</button></form>@endauth</header>
<main>
@if($errors->any())<div class="alert" role="alert">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif
@yield('content')
</main>
<script>if('serviceWorker' in navigator){navigator.serviceWorker.register('/technicien/sw.js',{scope:'/technicien/'}).catch(function(){})}</script>
</body>
</html>
