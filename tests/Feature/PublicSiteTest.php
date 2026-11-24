<?php

namespace Tests\Feature;

use App\Models\{OpeningHour, Service, ServiceRequest, Setting};
use App\Notifications\NewServiceRequest;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;
use App\Support\HomepageGallery;
use Tests\TestCase;

class PublicSiteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    private function payload(array $over = []): array
    {
        return array_merge([
            'equipment' => 'Chambre froide', 'problem' => 'Ne refroidit plus', 'district' => 'Haie Vive',
            'phone' => '+229 01 02 03 04', 'consent' => '1',
        ], $over);
    }

    public function test_les_pages_publiques_repondent(): void
    {
        foreach (['/', '/services', '/produits', '/contrats', '/contact', '/mentions-legales', '/confidentialite', '/demande', '/sitemap.xml'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_accueil_presente_le_visuel_illustratif_provisoire_et_la_preparation_d_une_demande(): void
    {
        $this->get('/')->assertOk()
            ->assertSee('atelier-illustration-provisoire.svg')
            ->assertSee('Illustration de démonstration d’un atelier de froid et de climatisation, pas une photographie.')
            ->assertSee('Visuel illustratif provisoire')
            ->assertSee('à remplacer par une photo de l’atelier')
            ->assertDontSee('Photo de l’atelier à ajouter')
            ->assertSee('Photo d’intervention à ajouter')
            ->assertSee('aria-labelledby="hero-photo-caption"', false)
            ->assertSee('id="hero-photo-caption"', false)
            ->assertSee('aria-labelledby="intervention-photo-caption"', false)
            ->assertSee('id="intervention-photo-caption"', false)
            ->assertDontSee('images/climatisation-illustration.jpg')
            ->assertDontSee('images/froid-technique-detail.jpg')
            ->assertSee('Comment préparer votre demande')
            ->assertSee('L’équipement')
            ->assertSee('Le problème')
            ->assertSee('Le quartier et un contact')
            ->assertSee('href="' . route('requests.create') . '"', false)
            ->assertDontSee('homepage-gallery-title');
    }

    public function test_une_photo_reelle_chargee_remplace_l_illustration_de_l_accueil(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('homepage/atelier-reel.jpg', 'real workshop image');
        Setting::updateOrCreate(['key' => 'homepage_workshop_image'], ['value' => 'homepage/atelier-reel.jpg']);
        Setting::updateOrCreate(['key' => 'homepage_workshop_alt'], ['value' => 'Vue réelle de l’atelier']);

        $this->get('/')->assertOk()
            ->assertSee('storage/homepage/atelier-reel.jpg')
            ->assertSee('Vue réelle de l’atelier')
            ->assertDontSee('atelier-illustration-provisoire.svg')
            ->assertDontSee('Visuel illustratif provisoire');
    }

    public function test_les_cartes_de_services_affichent_l_icone_choisie_ou_l_image_valide(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('services/installation.png', 'service image');

        $services = Service::orderBy('sort_order')->take(3)->get();
        $services[0]->update(['icon_key' => 'snowflake', 'image_path' => null]);
        $services[1]->update(['icon_key' => 'fan', 'image_path' => 'services/installation.png']);
        $services[2]->update(['icon_key' => 'shield', 'image_path' => 'services/image-absente.png']);

        foreach (['/', '/services'] as $url) {
            $this->get($url)->assertOk()
                ->assertSee('data-service-icon="snowflake"', false)
                ->assertSee('data-service-icon="shield"', false)
                ->assertSee('src="' . asset('storage/services/installation.png') . '"', false)
                ->assertDontSee('services/image-absente.png');
        }
    }

    public function test_accueil_affiche_uniquement_les_photos_de_galerie_valides(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('homepage/gallery/atelier.png', 'photo');
        Setting::updateOrCreate(['key' => HomepageGallery::SETTING_KEY], [
            'value' => json_encode([
                ['image' => 'homepage/gallery/atelier.png', 'alt' => 'Établi de travail', 'caption' => 'Vue de l’atelier'],
                ['image' => 'homepage/workshop.png', 'alt' => 'Chemin hors galerie'],
                ['image' => 'homepage/gallery/../workshop.png', 'alt' => 'Chemin relatif'],
                ['image' => 'homepage/gallery/absente.png', 'alt' => 'Fichier manquant'],
                ['image' => 'homepage/gallery/sans-texte.png'],
            ]),
        ]);

        $this->get('/')->assertOk()
            ->assertSee('id="homepage-gallery-title"', false)
            ->assertSee('Établi de travail')
            ->assertSee('Vue de l’atelier')
            ->assertSee('storage/homepage/gallery/atelier.png')
            ->assertDontSee('Chemin hors galerie')
            ->assertDontSee('Chemin relatif')
            ->assertDontSee('Fichier manquant')
            ->assertDontSee('sans-texte.png');
    }

    public function test_accueil_ignore_un_json_de_galerie_mal_forme(): void
    {
        Setting::updateOrCreate(['key' => HomepageGallery::SETTING_KEY], ['value' => '{malformed']);

        $this->get('/')->assertOk()
            ->assertDontSee('homepage-gallery-title')
            ->assertDontSee('Dans les coulisses de l’atelier');
    }

    public function test_le_tagline_par_defaut_est_remplace_par_un_libelle_factuel_sur_le_site_public(): void
    {
        $response = $this->get('/')->assertOk()
            ->assertSee('<h1>Installation, entretien et dépannage en froid et climatisation.</h1>', false)
            ->assertSee('name="description" content="Installation, entretien et dépannage en froid et climatisation."', false)
            ->assertSee('<p>Installation, entretien et dépannage en froid et climatisation.</p>', false)
            ->assertDontSee('Votre froid en panne ? On vient vite.');

        $this->assertStringNotContainsString('Votre froid en panne ? On vient vite.', $response->getContent());

        $this->get('/contact')->assertOk()
            ->assertSee('name="description" content="Installation, entretien et dépannage en froid et climatisation."', false)
            ->assertSee('property="og:description" content="Installation, entretien et dépannage en froid et climatisation."', false)
            ->assertDontSee('Votre froid en panne ? On vient vite.');
    }

    public function test_un_tagline_personnalise_non_prometteur_est_conserve(): void
    {
        Setting::updateOrCreate(['key' => 'tagline'], ['value' => 'Froid et climatisation pour votre maison.']);

        $this->get('/')->assertOk()
            ->assertSee('<h1>Froid et climatisation pour votre maison.</h1>', false)
            ->assertSee('name="description" content="Froid et climatisation pour votre maison."', false)
            ->assertSee('<p>Froid et climatisation pour votre maison.</p>', false)
            ->assertDontSee('Votre froid en panne ? On vient vite.');
    }

    public function test_le_lien_urgent_coche_le_formulaire_sans_modifier_le_preselection_du_service(): void
    {
        $service = Service::where('slug', 'depannage-urgent')->firstOrFail();
        $this->get('/')->assertOk()
            ->assertSee('Signaler une panne urgente')
            ->assertSee(route('requests.create', ['urgent' => 1]), false);

        $response = $this->get(route('requests.create', ['urgent' => 1, 'service' => $service->slug]))
            ->assertOk()
            ->assertSee('name="is_urgent" value="1" checked>', false)
            ->assertSee('<option value="' . $service->id . '" selected>', false);

        $this->assertSame(1, preg_match('/<input type="checkbox" name="is_urgent" value="1"[^>]*checked/', $response->getContent()));
    }

    public function test_le_formulaire_general_reste_non_urgent_et_garde_les_anciennes_valeurs(): void
    {
        $response = $this->get(route('requests.create'))->assertOk();
        $this->assertSame(0, preg_match('/<input type="checkbox" name="is_urgent" value="1"[^>]*checked/', $response->getContent()));

        $service = Service::where('slug', 'depannage-urgent')->firstOrFail();
        $response = $this->from(route('requests.create', ['urgent' => 1, 'service' => $service->slug]))
            ->post(route('requests.store'), $this->payload(['is_urgent' => '1', 'consent' => null]))
            ->assertSessionHasErrors('consent');

        $this->get($response->headers->get('Location'))
            ->assertOk()
            ->assertSee('name="is_urgent" value="1" checked>', false)
            ->assertSee('<option value="' . $service->id . '" selected>', false);
    }

    public function test_page_inconnue_donne_404(): void
    {
        $this->get('/page-qui-n-existe-pas')->assertNotFound();
    }

    public function test_une_demande_valide_est_enregistree_et_notifiee(): void
    {
        Notification::fake();
        Setting::updateOrCreate(['key' => 'notify_email'], ['value' => 'gerant@example.com']);

        $this->post('/demande', $this->payload(['is_urgent' => '1']))->assertRedirect('/demande/merci');

        $this->assertDatabaseCount('service_requests', 1);
        $this->assertTrue(ServiceRequest::first()->is_urgent);
        Notification::assertSentOnDemand(NewServiceRequest::class);
    }

    public function test_sans_email_de_notification_la_demande_est_quand_meme_enregistree(): void
    {
        Notification::fake();
        $this->post('/demande', $this->payload())->assertRedirect('/demande/merci');
        $this->assertDatabaseCount('service_requests', 1);
        Notification::assertNothingSent();
    }

    public function test_le_consentement_est_obligatoire(): void
    {
        $this->post('/demande', $this->payload(['consent' => null]))->assertSessionHasErrors('consent');
        $this->assertDatabaseCount('service_requests', 0);
    }

    public function test_le_champ_piege_bloque_les_robots(): void
    {
        $this->post('/demande', $this->payload(['website' => 'http://spam.example']))->assertSessionHasErrors('website');
        $this->assertDatabaseCount('service_requests', 0);
    }

    public function test_la_page_merci_n_est_pas_consultable_directement(): void
    {
        $this->get('/demande/merci')->assertRedirect('/demande');
    }

    public function test_un_produit_masque_n_apparait_pas(): void
    {
        $this->get('/produits')->assertDontSee('Split mural 12 000 BTU');
    }

    public function test_les_coordonnees_placeholder_ne_sont_pas_publiees_comme_actions(): void
    {
        foreach (['/', '/services', '/produits', '/contrats', '/contact', '/mentions-legales', '/confidentialite', '/demande'] as $url) {
            $response = $this->get($url)->assertOk();
            $html = $response->getContent();

            $this->assertStringNotContainsString('href="tel:', $html);
            $this->assertStringNotContainsString('wa.me/', $html);
            $this->assertStringNotContainsString('[NOM DE L’ATELIER]', $html);
            $this->assertStringNotContainsString('+229XXXXXXXX', $html);
            $this->assertStringNotContainsString('229XXXXXXXX', $html);
            $this->assertStringNotContainsString('[ADRESSE / REPÈRE]', $html);
            $this->assertStringNotContainsString('À renseigner', $html);
        }
    }

    public function test_les_coordonnees_renseignees_conservent_leurs_actions(): void
    {
        Setting::updateOrCreate(['key' => 'phone'], ['value' => '+229 01 02 03 04']);
        Setting::updateOrCreate(['key' => 'whatsapp'], ['value' => '229 01 23 45 67']);
        Setting::updateOrCreate(['key' => 'contact_email'], ['value' => 'atelier@example.bj']);
        Setting::updateOrCreate(['key' => 'address'], ['value' => 'Rue 123, Cotonou']);

        $this->get('/')->assertOk()->assertSee('href="tel:+22901020304"', false);
        $this->get('/contact')->assertOk()
            ->assertSee('href="tel:+22901020304"', false)
            ->assertSee('href="https://wa.me/22901234567"', false)
            ->assertSee('atelier@example.bj')
            ->assertSee('Rue 123, Cotonou')
            ->assertSee('Voir l’itinéraire');
    }

    public function test_les_coordonnees_de_demonstration_ne_sont_pas_publiees(): void
    {
        Setting::updateOrCreate(['key' => 'phone'], ['value' => '+229 97 00 00 00']);
        Setting::updateOrCreate(['key' => 'whatsapp'], ['value' => '22997000000']);
        Setting::updateOrCreate(['key' => 'contact_email'], ['value' => 'contact@example.com']);
        Setting::updateOrCreate(['key' => 'address'], ['value' => 'Cotonou, Bénin']);

        $this->assertNull(Setting::phoneHref());
        $this->assertNull(Setting::whatsappHref());
        $this->assertNull(Setting::contactEmail());
        $this->assertNull(Setting::publicAddress());

        foreach (['/', '/contact', '/mentions-legales', '/confidentialite'] as $url) {
            $response = $this->get($url)->assertOk();
            $response->assertDontSee('22997000000')
                ->assertDontSee('contact@example.com')
                ->assertDontSee('Cotonou, Bénin')
                ->assertDontSee('Voir l’itinéraire')
                ->assertDontSee('mailto:');
        }
    }

    public function test_la_page_contact_affiche_un_etat_vide_sans_panneau_contact(): void
    {
        $this->get('/contact')->assertOk()
            ->assertSee('Coordonnées non disponibles')
            ->assertDontSee('class="contact-primary"', false)
            ->assertSee('href="' . route('requests.create') . '"', false);
    }

    public function test_la_page_contact_affiche_les_horaires_sans_coordonnees(): void
    {
        OpeningHour::updateOrCreate(['day_of_week' => 1], [
            'opens_at' => '10:00',
            'closes_at' => '18:00',
            'is_closed' => false,
        ]);

        $this->get('/contact')->assertOk()
            ->assertSee('Lundi')
            ->assertSee('10:00 – 18:00')
            ->assertDontSee('class="contact-primary"', false)
            ->assertSee('Coordonnées non disponibles');
    }
}
