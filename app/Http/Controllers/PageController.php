<?php

namespace App\Http\Controllers;

use App\Models\{ContractPlan, OpeningHour, Product, ProductCategory, Service, ServiceZone, Setting, Testimonial};
use App\Support\HomepageGallery;
use Illuminate\Support\Facades\Storage;

class PageController extends Controller
{
    public function home()
    {
        $hours = OpeningHour::orderBy('day_of_week')->get()
            ->filter(fn ($hour) => $hour->is_closed || ($hour->opens_at && $hour->closes_at))
            ->values();
        $zones = ServiceZone::where('is_active', true)->orderBy('sort_order')->get();

        return view('pages.home', [
            'services'     => Service::where('is_published', true)->orderBy('sort_order')->get(),
            'hours'        => $hours,
            'openNow'      => OpeningHour::isOpenNow(),
            'zones'        => $zones,
            'featured'     => Product::published()->orderBy('sort_order')->take(4)->get(),
            'testimonials' => Testimonial::where('is_published', true)->where('consent_given', true)->latest('given_on')->take(6)->get(),
            'why'          => array_filter([
                'Certification'  => Setting::publicValue('certification'),
                'Garantie'       => Setting::publicValue('guarantee'),
                'Délai d’intervention' => Setting::publicValue('response_time'),
            ]),
            'workshopImage' => $this->publicHomepageImage('homepage_workshop_image'),
            'workshopAlt' => Setting::publicValue('homepage_workshop_alt') ?? 'Atelier de froid et de climatisation',
            'interventionImage' => $this->publicHomepageImage('homepage_intervention_image'),
            'interventionAlt' => Setting::publicValue('homepage_intervention_alt') ?? 'Intervention sur un équipement de froid ou de climatisation',
            'homepageGallery' => HomepageGallery::decode(Setting::get(HomepageGallery::SETTING_KEY), true),
            'schema'       => $this->localBusinessSchema($hours, $zones),
        ]);
    }

    public function services()
    {
        return view('pages.services', ['services' => Service::where('is_published', true)->orderBy('sort_order')->get()]);
    }

    public function products()
    {
        $categories = ProductCategory::with(['products' => fn ($q) => $q->published()->orderBy('sort_order')])
            ->orderBy('sort_order')->get()->filter(fn ($c) => $c->products->isNotEmpty());

        return view('pages.products', compact('categories'));
    }

    public function contracts()
    {
        return view('pages.contracts', ['plans' => ContractPlan::where('is_published', true)->orderBy('sort_order')->get()]);
    }

    public function contact()
    {
        $hours = OpeningHour::orderBy('day_of_week')->get()
            ->filter(fn ($hour) => $hour->is_closed || ($hour->opens_at && $hour->closes_at))
            ->values();

        return view('pages.contact', compact('hours'));
    }

    public function legal() { return view('pages.legal'); }
    public function privacy() { return view('pages.privacy'); }

    private function publicHomepageImage(string $key): ?string
    {
        $path = Setting::get($key);

        return is_string($path) && str_starts_with($path, 'homepage/') && Storage::disk('public')->exists($path)
            ? $path
            : null;
    }

    public function sitemap()
    {
        $urls = [route('home'), route('services'), route('products'), route('contracts'), route('contact'), route('requests.create'), route('legal'), route('privacy')];
        $xml = '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        foreach ($urls as $u) $xml .= '<url><loc>' . e($u) . '</loc></url>';
        return response($xml . '</urlset>', 200, ['Content-Type' => 'application/xml']);
    }

    // Données structurées pour la recherche locale. Seules les informations renseignées sont publiées.
    private function localBusinessSchema($hours, $zones): array
    {
        $days = [1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday'];
        $spec = $hours->filter(fn ($h) => ! $h->is_closed && $h->opens_at && $h->closes_at)->map(fn ($h) => [
            '@type' => 'OpeningHoursSpecification', 'dayOfWeek' => $days[$h->day_of_week],
            'opens' => substr($h->opens_at, 0, 5), 'closes' => substr($h->closes_at, 0, 5),
        ])->values()->all();

        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'LocalBusiness',
            'name' => Setting::publicValue('business_name'),
            'url' => url('/'),
            'telephone' => Setting::phoneNumber(),
            'address' => Setting::publicAddress()
                ? ['@type' => 'PostalAddress', 'streetAddress' => Setting::publicAddress(), 'addressLocality' => 'Cotonou', 'addressCountry' => 'BJ']
                : null,
            'areaServed' => $zones->pluck('name')->all(),
            'openingHoursSpecification' => $spec,
        ]);
    }
}
