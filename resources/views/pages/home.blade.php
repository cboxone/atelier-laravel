@extends('layout')
@section('description', \App\Models\Setting::publicValue('business_name') ? \App\Models\Setting::publicValue('business_name') . ' : ' . \App\Support\PublicCopy::text('home_meta_description', 'dépannage, installation et vente de climatiseurs et de froid à Cotonou.') : \App\Models\Setting::publicTagline())
@push('head')<script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>@endpush
@section('content')
<section class="hero">
    <div class="wrap hero-layout">
        <div class="hero-copy">
            <p class="hero-eyebrow">{{ \App\Support\PublicCopy::text('home_hero_eyebrow', 'Froid · Climatisation · Services') }}</p>
            <h1>{{ \App\Support\Typo::fr(\App\Models\Setting::publicTagline()) }}</h1>
            <p class="hero-intro">{{ \App\Support\PublicCopy::text('home_hero_intro', 'Installation, entretien et dépannage pour vos équipements de froid et de climatisation. Parlons de votre besoin.') }}</p>
            @if($openNow === true)<p class="is-open">{{ \App\Support\PublicCopy::text('home_open_label', 'Ouvert maintenant') }}</p>@elseif($openNow === false)<p class="is-closed">{{ \App\Support\PublicCopy::text('home_closed_label', 'Fermé actuellement.') }}</p>@endif
            <div class="actions">
                @if(\App\Models\Setting::phoneHref())<a class="btn" href="{{ \App\Models\Setting::phoneHref() }}">{{ \App\Support\PublicCopy::text('home_call_button', 'Appeler maintenant') }}</a>@endif
                <a class="btn urgent-action" href="{{ route('requests.create', ['urgent' => 1]) }}">{{ \App\Support\PublicCopy::text('home_urgent_button', 'Signaler une panne urgente') }}</a>
                <a class="btn light" href="{{ route('requests.create') }}">{{ \App\Support\PublicCopy::text('home_request_button', 'Demander une intervention') }}</a>
                <a class="btn light" href="{{ route('products') }}">{{ \App\Support\PublicCopy::text('home_products_button', 'Découvrir les produits') }}</a>
            </div>
            <p class="hero-note">{{ \App\Support\PublicCopy::text('home_hero_note', 'Pour préparer votre demande : l’équipement, le problème et votre quartier.') }}</p>
        </div>
        <figure class="hero-art" aria-labelledby="hero-photo-caption">
                @if($workshopImage)
                    <div class="photo-slot photo-slot-atelier photo-slot-has-image">
                        <img class="photo-slot-photo" src="{{ asset('storage/' . $workshopImage) }}" alt="{{ $workshopAlt }}" width="900" height="1000" fetchpriority="high">
                    </div>
                    <figcaption class="visual-caption" id="hero-photo-caption"><span aria-hidden="true"></span>{{ \App\Support\PublicCopy::text('home_workshop_caption', 'Atelier') }}</figcaption>
                @else
                    <div class="photo-slot photo-slot-atelier photo-slot-has-image">
                        <img class="photo-slot-photo workshop-illustration" src="{{ asset('images/atelier-illustration-provisoire.svg') }}" alt="Illustration de démonstration d’un atelier de froid et de climatisation, pas une photographie." width="960" height="720" fetchpriority="high">
                    </div>
                    <figcaption class="visual-caption visual-caption-provisional" id="hero-photo-caption"><span aria-hidden="true"></span>Visuel illustratif provisoire — à remplacer par une photo de l’atelier</figcaption>
                @endif
        </figure>
    </div>
</section>

<section class="trust-strip" aria-label="Repères sur les prestations">
    <div class="wrap trust-strip-inner">
        <div class="trust-intro">
            <p class="eyebrow">{{ \App\Support\PublicCopy::text('home_scope_eyebrow', 'Repères utiles') }}</p>
            <h2>{{ \App\Support\PublicCopy::text('home_scope_heading', 'Un champ d’intervention clair.') }}</h2>
        </div>
        <div class="trust-scope">
            <strong>{{ \App\Support\PublicCopy::text('home_scope_label', 'Prestations publiées') }}</strong>
            @if($services->isNotEmpty())
                <ul>
                    @foreach($services->take(3) as $service)<li>{{ $service->name }}</li>@endforeach
                </ul>
            @else
                <p>{{ \App\Support\PublicCopy::text('home_scope_empty', 'Installation, entretien et dépannage de vos équipements de froid et de climatisation.') }}</p>
            @endif
        </div>
        @if($zones->isNotEmpty())
            <div class="trust-zones">
                <strong>{{ \App\Support\PublicCopy::text('home_zones_label', 'Zones indiquées') }}</strong>
                <p>{{ $zones->pluck('name')->join(', ') }}</p>
            </div>
        @endif
    </div>
</section>

<section class="wrap">
    <div class="section-heading">
        <h2>{{ \App\Support\PublicCopy::text('home_services_heading', 'Nos services') }}</h2>
        <p>{{ \App\Support\PublicCopy::text('home_services_intro', 'Des solutions adaptées à votre équipement et à votre quotidien.') }}</p>
    </div>
    <div class="grid homepage-services-grid">
        @forelse($services as $s)
            <article class="service-card">
                <div>
                    <div class="service-card-meta"><span class="service-index">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span><x-service-visual :service="$s" /></div>
                    <h3>{{ $s->name }}</h3><p>{{ $s->summary }}</p>
                </div>
                <a href="{{ route('requests.create', ['service' => $s->slug]) }}">{{ \App\Support\PublicCopy::text('services_item_link', 'En savoir plus') }} <span aria-hidden="true">↗</span></a>
            </article>
        @empty
            <p class="muted">{{ \App\Support\PublicCopy::text('home_services_empty', 'Contactez-nous pour en savoir plus sur nos prestations.') }}</p>
        @endforelse
    </div>
</section>

<section class="wrap request-preparation" aria-labelledby="request-preparation-title">
    <div class="request-prep-copy">
        <p class="eyebrow">{{ \App\Support\PublicCopy::text('home_request_eyebrow', 'Une demande bien préparée') }}</p>
        <h2 id="request-preparation-title">{{ \App\Support\PublicCopy::text('home_request_heading', 'Comment préparer votre demande') }}</h2>
        <p class="request-prep-intro">{{ \App\Support\PublicCopy::text('home_request_intro', 'Quelques précisions suffisent pour décrire votre besoin dans le formulaire.') }}</p>
        <ol class="request-steps">
            <li><span>01</span><div><h3>{{ \App\Support\PublicCopy::text('home_request_step_equipment', 'L’équipement') }}</h3><p>{{ \App\Support\PublicCopy::text('home_request_step_equipment_text', 'Indiquez le type d’appareil ou d’installation concerné.') }}</p></div></li>
            <li><span>02</span><div><h3>{{ \App\Support\PublicCopy::text('home_request_step_issue', 'Le problème') }}</h3><p>{{ \App\Support\PublicCopy::text('home_request_step_issue_text', 'Décrivez ce que vous constatez, même en quelques mots.') }}</p></div></li>
            <li><span>03</span><div><h3>{{ \App\Support\PublicCopy::text('home_request_step_contact', 'Le quartier et un contact') }}</h3><p>{{ \App\Support\PublicCopy::text('home_request_step_contact_text', 'Précisez votre zone et un numéro pour vous joindre.') }}</p></div></li>
        </ol>
        <a class="request-prep-link" href="{{ route('requests.create') }}">{{ \App\Support\PublicCopy::text('home_request_link', 'Préparer ma demande') }} <span aria-hidden="true">↗</span></a>
    </div>
    <figure class="request-prep-art" aria-labelledby="intervention-photo-caption">
        @if($interventionImage)
            <div class="photo-slot photo-slot-intervention photo-slot-has-image">
                <img class="photo-slot-photo" src="{{ asset('storage/' . $interventionImage) }}" alt="{{ $interventionAlt }}" width="900" height="1000" loading="lazy" decoding="async">
            </div>
            <figcaption id="intervention-photo-caption">{{ \App\Support\PublicCopy::text('home_intervention_caption', 'Intervention') }}</figcaption>
        @else
            <div class="photo-slot photo-slot-intervention" aria-hidden="true">
                <span class="photo-slot-index">02 <span>/</span> {{ \App\Support\PublicCopy::text('home_intervention_caption', 'Intervention') }}</span>
                <span class="hero-art-mark"><i></i><i></i><i></i></span>
                <span class="photo-slot-note">Emplacement temporaire</span>
            </div>
            <figcaption id="intervention-photo-caption">Photo d’intervention à ajouter</figcaption>
        @endif
    </figure>
</section>

@if($homepageGallery !== [])
    <section class="wrap homepage-gallery" aria-labelledby="homepage-gallery-title">
        <div class="section-heading">
            <div>
                <p class="eyebrow">Galerie photo</p>
                <h2 id="homepage-gallery-title">{{ \App\Support\PublicCopy::text('home_gallery_heading', 'Dans les coulisses de l’atelier') }}</h2>
            </div>
            <p>{{ \App\Support\PublicCopy::text('home_gallery_intro', 'Quelques images de nos espaces et de nos interventions.') }}</p>
        </div>
        <div class="homepage-gallery-grid">
            @foreach($homepageGallery as $photo)
                <figure class="homepage-gallery-item">
                    <img src="{{ asset('storage/' . $photo['image']) }}" alt="{{ $photo['alt'] }}" loading="lazy" decoding="async">
                    @if(! empty($photo['caption']))
                        <figcaption>{{ $photo['caption'] }}</figcaption>
                    @endif
                </figure>
            @endforeach
        </div>
    </section>
@endif

@if(count($why))
    <section class="wrap">
        <div class="section-heading">
            <h2>{{ \App\Support\PublicCopy::text('home_proof_heading', 'Repères renseignés') }}</h2>
            <p>{{ \App\Support\PublicCopy::text('home_proof_intro', 'Les informations ci-dessous sont celles communiquées par l’atelier.') }}</p>
        </div>
        <div class="proof-row">@foreach($why as $title => $text)<div class="proof-item"><strong>{{ $title }}</strong><span>{{ $text }}</span></div>@endforeach</div>
    </section>
@endif

@if($featured->isNotEmpty())
    <section class="wrap">
        <div class="section-heading"><h2>{{ \App\Support\PublicCopy::text('home_shop_heading', 'À découvrir en boutique') }}</h2><a class="more" href="{{ route('products') }}">{{ \App\Support\PublicCopy::text('home_shop_link', 'Voir tous les produits') }} <span aria-hidden="true">↗</span></a></div>
        <div class="grid">
            @foreach($featured as $p)
                <article class="card product-card">
                    @if($p->image_url)<img src="{{ $p->image_url }}" alt="{{ $p->name }}" loading="lazy">@endif
                    <small class="availability">{{ $p->availability_label }}</small>
                    <h3>{{ $p->name }}</h3>
                    <strong class="product-price">{{ $p->price_label }}</strong>
                    @if($p->isPurchasable())
                        <form method="post" action="{{ route('cart.add', $p) }}" class="product-action">@csrf<button class="btn" type="submit">{{ \App\Support\PublicCopy::text('products_add_button', 'Ajouter au panier') }}</button></form>
                    @else
                        <p class="product-action"><a class="btn dark" href="{{ route('requests.create') }}">{{ \App\Support\PublicCopy::text('products_quote_button', 'Demander un devis') }}</a></p>
                    @endif
                </article>
            @endforeach
        </div>
    </section>
@endif

@if($hours->isNotEmpty())
    <section class="wrap">
        <div class="section-heading"><h2>{{ \App\Support\PublicCopy::text('home_hours_heading', 'Quand nous trouver') }}</h2><p>{{ \App\Support\PublicCopy::text('home_hours_intro', 'Consultez nos horaires avant de passer.') }}</p></div>
        <div class="card hours-list">@foreach($hours as $h)<div><span>{{ $h->day_name }}</span><strong>{{ $h->label }}</strong></div>@endforeach</div>
    </section>
@endif

@if($zones->isNotEmpty())
    <section class="wrap zones-band"><div><p class="eyebrow">Présents près de chez vous</p><h2>{{ \App\Support\PublicCopy::text('home_zones_heading', 'Zones d’intervention') }}</h2></div><p>{{ $zones->pluck('name')->join(', ') }}</p></section>
@endif

@if($testimonials->isNotEmpty())
    <section class="wrap">
        <div class="section-heading"><h2>{{ \App\Support\PublicCopy::text('home_testimonials_heading', 'Ils nous font confiance') }}</h2></div>
        <div class="grid">@foreach($testimonials as $t)<figure class="card testimonial"><blockquote>{{ $t->content }}</blockquote><figcaption>{{ $t->author_name }}</figcaption></figure>@endforeach</div>
    </section>
@endif

<section class="contact-close">
    <div class="wrap contact-close-inner">
        <div><p class="eyebrow">{{ \App\Support\PublicCopy::text('home_final_eyebrow', 'Un projet ou une panne ?') }}</p><h2>{{ \App\Support\PublicCopy::text('home_final_heading', 'Parlons de votre besoin.') }}</h2><p>{{ \App\Support\PublicCopy::text('home_final_intro', 'Décrivez votre besoin ou consultez les moyens de contact disponibles.') }}</p></div>
        <div class="contact-close-actions">
            <a class="btn" href="{{ route('requests.create') }}">{{ \App\Support\PublicCopy::text('home_final_request_button', 'Décrire mon besoin') }} <span aria-hidden="true">↗</span></a>
            <a class="contact-close-secondary" href="{{ route('contact') }}">{{ \App\Support\PublicCopy::text('home_final_contact_link', 'Voir les options de contact') }}</a>
        </div>
    </div>
</section>
@endsection
