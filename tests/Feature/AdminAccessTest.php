<?php

namespace Tests\Feature;

use App\Models\User;
use App\Filament\Pages\WebsiteSettings;
use App\Filament\Resources\Services\Pages\ManageServices;
use App\Models\{Service, Setting};
use App\Support\HomepageGallery;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_visiteur_est_renvoye_vers_la_connexion(): void
    {
        $this->get('/admin')->assertRedirect();
    }

    public function test_un_utilisateur_non_admin_est_refuse(): void
    {
        $user = User::factory()->create(); // is_admin = false par défaut
        $this->actingAs($user)->get('/admin')->assertForbidden();
    }

    public function test_un_admin_accede_au_panneau(): void
    {
        $admin = User::factory()->create();
        $admin->is_admin = true;
        $admin->save();

        $this->actingAs($admin)->get('/admin')->assertOk();
    }

    public function test_les_ecrans_de_gestion_du_site_restent_reserves_aux_admins(): void
    {
        $paths = [
            '/admin/website-settings',
            '/admin/services',
            '/admin/product-categories',
            '/admin/products',
            '/admin/contract-plans',
            '/admin/opening-hours',
            '/admin/service-zones',
            '/admin/testimonials',
            '/admin/payment-methods',
        ];

        foreach ($paths as $path) {
            $this->get($path)->assertRedirect();
        }

        $this->actingAs(User::factory()->create())->get('/admin/website-settings')->assertForbidden();

        $admin = User::factory()->create();
        $admin->is_admin = true;
        $admin->save();
        $this->actingAs($admin);

        foreach ($paths as $path) {
            $this->get($path)->assertOk();
        }
    }

    public function test_un_admin_peut_enregistrer_les_coordonnees_et_les_textes_du_site(): void
    {
        $admin = User::factory()->create();
        $admin->is_admin = true;
        $admin->save();
        $this->actingAs($admin);
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::test(WebsiteSettings::class)
            ->set('data.business_name', 'Atelier test')
            ->set('data.copy_home_hero_intro', 'Un texte public actualisé.')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('settings', ['key' => 'business_name', 'value' => 'Atelier test']);
        $this->assertDatabaseHas('settings', ['key' => 'copy_home_hero_intro', 'value' => 'Un texte public actualisé.']);
    }

    public function test_un_admin_peut_charger_une_photo_d_accueil_sur_le_disque_public_dedie(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create();
        $admin->is_admin = true;
        $admin->save();
        $this->actingAs($admin);
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $image = UploadedFile::fake()->createWithContent(
            'atelier.png',
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jXioAAAAASUVORK5CYII=')
        );

        Livewire::test(WebsiteSettings::class)
            ->set('data.homepage_workshop_image', $image)
            ->set('data.homepage_workshop_alt', 'Photo réelle de l’atelier')
            ->call('save')
            ->assertHasNoErrors();

        $path = \App\Models\Setting::where('key', 'homepage_workshop_image')->value('value');
        $this->assertStringStartsWith('homepage/', $path);
        Storage::disk('public')->assertExists($path);
        $this->assertDatabaseHas('settings', ['key' => 'homepage_workshop_alt', 'value' => 'Photo réelle de l’atelier']);
    }

    public function test_un_admin_peut_remplacer_la_photo_d_accueil_sans_effacer_d_autres_fichiers(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('homepage/atelier-precedent.jpg', 'old workshop photo');
        Storage::disk('public')->put('homepage/gallery/galerie.png', 'gallery photo');
        Storage::disk('public')->put('homepage/autre-dossier/image.png', 'other homepage image');
        Storage::disk('public')->put('services/service.png', 'service image');
        Setting::updateOrCreate(['key' => 'homepage_workshop_image'], ['value' => 'homepage/atelier-precedent.jpg']);

        $admin = User::factory()->create();
        $admin->is_admin = true;
        $admin->save();
        $this->actingAs($admin);
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::test(WebsiteSettings::class)
            ->set('data.homepage_workshop_image', null)
            ->set('data.homepage_workshop_alt', 'Illustration conservée tant qu’aucune photo n’est chargée')
            ->call('save')
            ->assertHasNoErrors();

        Storage::disk('public')->assertMissing('homepage/atelier-precedent.jpg');
        Storage::disk('public')->assertExists('homepage/gallery/galerie.png');
        Storage::disk('public')->assertExists('homepage/autre-dossier/image.png');
        Storage::disk('public')->assertExists('services/service.png');
        $this->assertDatabaseHas('settings', [
            'key' => 'homepage_workshop_alt',
            'value' => 'Illustration conservée tant qu’aucune photo n’est chargée',
        ]);
    }

    public function test_un_admin_peut_choisir_une_icone_et_charger_une_image_pour_un_service(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create();
        $admin->is_admin = true;
        $admin->save();
        $this->actingAs($admin);
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $image = UploadedFile::fake()->createWithContent(
            'ventilation.png',
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jXioAAAAASUVORK5CYII=')
        );

        Livewire::test(ManageServices::class)
            ->callAction('create', [
                'name' => 'Ventilation de test',
                'slug' => 'ventilation-de-test',
                'summary' => 'Résumé de test',
                'description' => 'Description de test',
                'icon_key' => 'fan',
                'image_path' => [$image],
                'sort_order' => 80,
                'is_published' => true,
            ])
            ->assertHasNoErrors();

        $service = Service::where('slug', 'ventilation-de-test')->firstOrFail();
        $this->assertSame('fan', $service->icon_key);
        $this->assertStringStartsWith('services/', $service->image_path);
        Storage::disk('public')->assertExists($service->image_path);

        Livewire::test(ManageServices::class)
            ->callTableAction('edit', $service, [
                'icon_key' => 'shield',
                'image_path' => [$service->image_path],
                'name' => $service->name,
                'slug' => $service->slug,
                'summary' => $service->summary,
                'description' => $service->description,
                'sort_order' => $service->sort_order,
                'is_published' => $service->is_published,
            ])
            ->assertHasNoErrors();

        $this->assertSame('shield', $service->fresh()->icon_key);
        Storage::disk('public')->assertExists($service->image_path);
    }

    public function test_un_admin_voit_une_icone_par_defaut_en_modifiant_une_ancienne_prestation(): void
    {
        $admin = User::factory()->create();
        $admin->is_admin = true;
        $admin->save();
        $this->actingAs($admin);
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $service = Service::create([
            'name' => 'Dépannage urgent',
            'slug' => 'depannage-urgent',
            'summary' => 'Résumé de test',
            'sort_order' => 0,
            'is_published' => true,
        ]);

        Livewire::test(ManageServices::class)
            ->mountTableAction('edit', $service)
            ->assertActionDataSet(['icon_key' => 'repair']);
    }

    public function test_un_admin_peut_ajouter_et_reordonner_la_galerie_sans_supprimer_les_autres_photos(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('homepage/gallery/obsolete.png', 'old gallery photo');
        Storage::disk('public')->put('homepage/atelier.png', 'standalone homepage photo');
        Storage::disk('public')->put('other/asset.png', 'unrelated asset');
        Setting::updateOrCreate(['key' => HomepageGallery::SETTING_KEY], [
            'value' => json_encode([['image' => 'homepage/gallery/obsolete.png', 'alt' => 'Ancienne photo']]),
        ]);
        Setting::updateOrCreate(['key' => 'homepage_workshop_image'], ['value' => 'homepage/atelier.png']);

        $admin = User::factory()->create();
        $admin->is_admin = true;
        $admin->save();
        $this->actingAs($admin);
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Storage::disk('public')->put('homepage/gallery/premiere.png', 'first gallery photo');
        Storage::disk('public')->put('homepage/gallery/seconde.png', 'second gallery photo');

        Livewire::test(WebsiteSettings::class)
            ->set('data.homepage_gallery', [
                ['image' => ['homepage/gallery/seconde.png'], 'alt' => 'Deuxième emplacement', 'caption' => 'Légende de la seconde photo'],
                ['image' => ['homepage/gallery/premiere.png'], 'alt' => 'Premier emplacement'],
            ])
            ->call('save')
            ->assertHasNoErrors();

        $gallery = json_decode(Setting::get(HomepageGallery::SETTING_KEY), true);
        $this->assertSame(['Deuxième emplacement', 'Premier emplacement'], array_column($gallery, 'alt'));
        $this->assertSame('Légende de la seconde photo', $gallery[0]['caption']);
        foreach ($gallery as $photo) {
            $this->assertStringStartsWith('homepage/gallery/', $photo['image']);
            Storage::disk('public')->assertExists($photo['image']);
        }

        Storage::disk('public')->assertMissing('homepage/gallery/obsolete.png');
        Storage::disk('public')->assertExists('homepage/atelier.png');
        Storage::disk('public')->assertExists('other/asset.png');
    }

    public function test_la_suppression_de_la_galerie_ne_supprime_que_les_fichiers_de_sa_sous_repertoire(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('homepage/gallery/remove.png', 'gallery photo');
        Storage::disk('public')->put('homepage/workshop.png', 'standalone photo');
        Storage::disk('public')->put('unrelated/image.png', 'unrelated asset');
        Setting::updateOrCreate(['key' => HomepageGallery::SETTING_KEY], [
            'value' => json_encode([['image' => 'homepage/gallery/remove.png', 'alt' => 'À retirer']]),
        ]);

        $admin = User::factory()->create();
        $admin->is_admin = true;
        $admin->save();
        $this->actingAs($admin);
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::test(WebsiteSettings::class)
            ->set('data.homepage_gallery', [])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame([], json_decode(Setting::get(HomepageGallery::SETTING_KEY), true));
        Storage::disk('public')->assertMissing('homepage/gallery/remove.png');
        Storage::disk('public')->assertExists('homepage/workshop.png');
        Storage::disk('public')->assertExists('unrelated/image.png');
    }

    public function test_les_pieces_jointes_sont_reservees_aux_admins(): void
    {
        $this->get('/admin/pieces-jointes/1')->assertForbidden();
        $this->actingAs(User::factory()->create())->get('/admin/pieces-jointes/1')->assertForbidden();
    }
}
