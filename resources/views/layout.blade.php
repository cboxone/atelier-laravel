<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>@yield('title', \App\Models\Setting::publicValue('business_name') ?? 'Accueil')</title>
<meta name="description" content="@yield('description', \App\Models\Setting::publicTagline())">
<link rel="canonical" href="{{ url()->current() }}">
<meta property="og:title" content="@yield('title', \App\Models\Setting::publicValue('business_name') ?? 'Accueil')">
<meta property="og:description" content="@yield('description', \App\Models\Setting::publicTagline())">
<meta property="og:type" content="website">
<meta property="og:locale" content="fr_BJ">
<link rel="stylesheet" href="{{ asset('css/site.css') }}">
@stack('head')
<script>document.documentElement.classList.add('js')</script>
</head>
<body>
<a class="skip-link" href="#main-content">Aller au contenu</a>
<header class="site-header"><div class="wrap header-inner">
<a class="brand" href="{{ route('home') }}" aria-label="Accueil — {{ \App\Models\Setting::publicValue('business_name') ?: 'Atelier de froid et climatisation' }}"><span class="brand-mark" aria-hidden="true"><i></i><i></i><i></i></span>@if(\App\Models\Setting::publicValue('business_name'))<span>{{ \App\Models\Setting::publicValue('business_name') }}</span>@endif</a>
<button class="burger" type="button" aria-expanded="false" aria-controls="nav"><span class="burger-lines" aria-hidden="true"></span><span class="burger-label">Menu</span></button>
<nav id="nav" class="site-nav" aria-label="Navigation principale">
<a href="{{ route('home') }}" @if(request()->routeIs('home')) aria-current="page" @endif>{{ \App\Support\PublicCopy::text('shared_nav_home', 'Accueil') }}</a>
<a href="{{ route('services') }}" @if(request()->routeIs('services')) aria-current="page" @endif>{{ \App\Support\PublicCopy::text('shared_nav_services', 'Services') }}</a>
<a href="{{ route('products') }}" @if(request()->routeIs('products')) aria-current="page" @endif>{{ \App\Support\PublicCopy::text('shared_nav_products', 'Produits') }}</a>
<a href="{{ route('contracts') }}" @if(request()->routeIs('contracts')) aria-current="page" @endif>{{ \App\Support\PublicCopy::text('shared_nav_contracts', 'Contrats') }}</a>
<a href="{{ route('contact') }}" @if(request()->routeIs('contact')) aria-current="page" @endif>{{ \App\Support\PublicCopy::text('shared_nav_contact', 'Contact') }}</a>
<a class="nav-cart" href="{{ route('cart') }}" @if(request()->routeIs('cart')) aria-current="page" @endif>{{ \App\Support\PublicCopy::text('shared_nav_cart', 'Panier') }} <span>{{ array_sum(session('cart', [])) }}</span></a>
</nav>
@if(\App\Models\Setting::phoneHref())<a class="header-phone" href="{{ \App\Models\Setting::phoneHref() }}"><span class="phone-label">Appelez-nous</span>{{ \App\Models\Setting::phoneNumber() }}</a>@endif
<a class="btn header-action" href="{{ route('requests.create') }}">{{ \App\Support\PublicCopy::text('shared_request_button', 'Demander une intervention') }} <span aria-hidden="true">↗</span></a>
</div></header>
<main id="main-content">@yield('content')</main>
<footer class="site-footer"><div class="wrap footer-main">
<div class="footer-brand"><a class="brand" href="{{ route('home') }}" aria-label="Accueil — {{ \App\Models\Setting::publicValue('business_name') ?: 'Atelier de froid et climatisation' }}"><span class="brand-mark" aria-hidden="true"><i></i><i></i><i></i></span>@if(\App\Models\Setting::publicValue('business_name'))<span>{{ \App\Models\Setting::publicValue('business_name') }}</span>@endif<p>{{ \App\Models\Setting::publicTagline() }}</p></div>
@if(\App\Models\Setting::publicAddress() || \App\Models\Setting::phoneHref())<div><h2>{{ \App\Support\PublicCopy::text('shared_footer_contact', 'Nous joindre') }}</h2>@if(\App\Models\Setting::publicAddress())<p>{{ \App\Models\Setting::publicAddress() }}</p>@endif @if(\App\Models\Setting::phoneHref())<a class="footer-phone" href="{{ \App\Models\Setting::phoneHref() }}">{{ \App\Models\Setting::phoneNumber() }}</a>@endif</div>@endif
<div><h2>{{ \App\Support\PublicCopy::text('shared_footer_services', 'À votre service') }}</h2><a href="{{ route('services') }}">{{ \App\Support\PublicCopy::text('shared_footer_services_link', 'Nos services') }}</a><a href="{{ route('products') }}">{{ \App\Support\PublicCopy::text('shared_footer_products_link', 'Produits et prix') }}</a><a href="{{ route('requests.create') }}">{{ \App\Support\PublicCopy::text('shared_footer_request_link', 'Demander une intervention') }}</a></div>
<div><h2>{{ \App\Support\PublicCopy::text('shared_footer_information', 'Informations') }}</h2><a href="{{ route('legal') }}">{{ \App\Support\PublicCopy::text('shared_footer_legal_link', 'Mentions légales') }}</a><a href="{{ route('privacy') }}">{{ \App\Support\PublicCopy::text('shared_footer_privacy_link', 'Confidentialité') }}</a><a href="{{ route('orders.track') }}">{{ \App\Support\PublicCopy::text('shared_footer_track_link', 'Suivre ma commande') }}</a></div>
</div><div class="wrap footer-bottom"><span>© {{ date('Y') }} @if(\App\Models\Setting::publicValue('business_name')){{ \App\Models\Setting::publicValue('business_name') }}@endif</span><span>{{ \App\Support\PublicCopy::text('shared_footer_tagline', 'Votre confort, notre métier.') }}</span></div></footer>
@if(\App\Models\Setting::phoneHref() || \App\Models\Setting::whatsappHref())<aside class="callbar" aria-label="Contact rapide">@if(\App\Models\Setting::phoneHref())<a href="{{ \App\Models\Setting::phoneHref() }}">Appeler <span aria-hidden="true">↗</span></a>@endif @if(\App\Models\Setting::whatsappHref())<a href="{{ \App\Models\Setting::whatsappHref() }}" rel="noopener">WhatsApp <span aria-hidden="true">↗</span></a>@endif</aside>@endif
<script>
const menuButton = document.querySelector('.burger');
const mainNav = document.getElementById('nav');
if (menuButton && mainNav) {
    menuButton.addEventListener('click', () => {
        const isOpen = mainNav.classList.toggle('is-open');
        menuButton.setAttribute('aria-expanded', String(isOpen));
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && mainNav.classList.contains('is-open')) {
            mainNav.classList.remove('is-open');
            menuButton.setAttribute('aria-expanded', 'false');
            menuButton.focus();
        }
    });
}
</script>
</body>
</html>
