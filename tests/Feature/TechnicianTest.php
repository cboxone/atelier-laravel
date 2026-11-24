<?php

namespace Tests\Feature;

use App\Models\{Customer, Equipment, Intervention, Product, ProductCategory, Setting, User};
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\{Hash, Storage};
use Tests\Support\Png;
use Tests\TestCase;

class TechnicianTest extends TestCase
{
    use RefreshDatabase;

    private User $tech;
    private User $other;
    private Intervention $mine;
    private Intervention $theirs;
    private Equipment $eq;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->tech = $this->technician('tech1@example.com');
        $this->other = $this->technician('tech2@example.com');
        $c = Customer::create(['name' => 'Client', 'phone' => '45661122']);
        $this->eq = Equipment::create(['customer_id' => $c->id, 'label' => 'Chambre froide', 'fluid_type' => 'R404A']);
        $this->mine = Intervention::create(['customer_id' => $c->id, 'equipment_id' => $this->eq->id, 'technician_id' => $this->tech->id, 'title' => 'Panne']);
        $this->theirs = Intervention::create(['customer_id' => $c->id, 'technician_id' => $this->other->id, 'title' => 'Autre']);
    }

    private function technician(string $email): User
    {
        $u = User::factory()->create(['email' => $email, 'password' => Hash::make('mot-de-passe-solide')]);
        $u->is_technician = true;
        $u->save();
        return $u;
    }

    private function product(?int $stock = null): Product
    {
        return Product::create(['product_category_id' => ProductCategory::first()->id, 'name' => 'Détendeur', 'slug' => 'detendeur', 'price_fcfa' => 25000, 'availability' => 'in_stock', 'is_published' => true, 'stock_quantity' => $stock]);
    }

    // ---------- accès
    public function test_un_visiteur_est_renvoye_vers_la_connexion_technicien(): void
    {
        $this->get('/technicien')->assertRedirect('/technicien/connexion');
        $this->get("/technicien/interventions/{$this->mine->id}")->assertRedirect('/technicien/connexion');
    }

    public function test_un_utilisateur_sans_role_est_refuse(): void
    {
        $this->actingAs(User::factory()->create());
        $this->get('/technicien')->assertForbidden();
    }

    public function test_un_technicien_ne_voit_que_ses_interventions(): void
    {
        $this->actingAs($this->tech);
        $this->get('/technicien')->assertOk()->assertSee('Panne')->assertDontSee('Autre');
        $this->get("/technicien/interventions/{$this->mine->id}")->assertOk();
        $this->get("/technicien/interventions/{$this->theirs->id}")->assertForbidden();
    }

    public function test_un_technicien_ne_peut_rien_faire_sur_l_intervention_d_un_autre(): void
    {
        $this->actingAs($this->tech);
        $id = $this->theirs->id;
        $this->post("/technicien/interventions/$id/demarrer")->assertForbidden();
        $this->post("/technicien/interventions/$id/lignes", ['kind' => 'labor', 'quantity' => 1, 'description' => 'x'])->assertForbidden();
        $this->post("/technicien/interventions/$id/signature", ['signed_by' => 'x', 'signature' => Png::signatureDataUrl()])->assertForbidden();
        $this->post("/technicien/interventions/$id/terminer", ['work_done' => 'travail fait'])->assertForbidden();
        $this->get("/technicien/interventions/$id/signature")->assertForbidden();
        $this->assertSame('planned', $this->theirs->fresh()->status);
    }

    public function test_l_administrateur_voit_tout(): void
    {
        $admin = User::factory()->create();
        $admin->is_admin = true;
        $admin->save();
        $this->actingAs($admin)->get("/technicien/interventions/{$this->theirs->id}")->assertOk();
    }

    public function test_connexion_mauvais_mot_de_passe_ou_sans_role(): void
    {
        $this->post('/technicien/connexion', ['email' => 'tech1@example.com', 'password' => 'faux'])->assertSessionHasErrors('email');
        $this->assertGuest();

        User::factory()->create(['email' => 'simple@example.com', 'password' => Hash::make('mot-de-passe-solide')]);
        $this->post('/technicien/connexion', ['email' => 'simple@example.com', 'password' => 'mot-de-passe-solide'])->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->post('/technicien/connexion', ['email' => 'tech1@example.com', 'password' => 'mot-de-passe-solide'])->assertRedirect('/technicien');
        $this->assertAuthenticatedAs($this->tech);
    }

    // ---------- travail
    public function test_demarrer_passe_en_cours(): void
    {
        $this->actingAs($this->tech)->post("/technicien/interventions/{$this->mine->id}/demarrer");
        $this->assertSame('in_progress', $this->mine->fresh()->status);
        $this->assertNotNull($this->mine->fresh()->started_at);
    }

    public function test_le_technicien_ne_fixe_jamais_un_prix(): void
    {
        $p = $this->product();
        $this->actingAs($this->tech)->post("/technicien/interventions/{$this->mine->id}/lignes", ['kind' => 'part', 'product_id' => $p->id, 'quantity' => 2, 'unit_price_fcfa' => 1, 'description' => 'gratuit']);
        $line = $this->mine->lines()->firstOrFail();
        $this->assertSame(25000, $line->unit_price_fcfa); // prix du catalogue, pas celui envoyé
        $this->assertSame('Détendeur', $line->description);
    }

    public function test_main_d_oeuvre_au_tarif_des_reglages_et_refusee_sans_tarif(): void
    {
        $this->actingAs($this->tech);
        $this->post("/technicien/interventions/{$this->mine->id}/lignes", ['kind' => 'labor', 'description' => 'Réparation', 'quantity' => 2])->assertSessionHasErrors('action');
        $this->assertSame(0, $this->mine->lines()->count());

        Setting::updateOrCreate(['key' => 'labor_rate_fcfa'], ['value' => '8000']);
        $this->post("/technicien/interventions/{$this->mine->id}/lignes", ['kind' => 'labor', 'description' => 'Réparation', 'quantity' => 2, 'unit_price_fcfa' => 1]);
        $this->assertSame(8000, $this->mine->lines()->firstOrFail()->unit_price_fcfa);
    }

    public function test_registre_des_fluides_depuis_le_telephone(): void
    {
        $this->actingAs($this->tech);
        $url = "/technicien/interventions/{$this->mine->id}/fluide";
        $this->post($url, ['operation' => 'initial', 'fluid' => 'R404A', 'kg' => '2,000'])->assertSessionHasNoErrors();
        $this->post($url, ['operation' => 'recovery', 'fluid' => 'R404A', 'kg' => '0,350']);
        $this->assertSame(1650, $this->eq->fluidMovements()->sum('quantity_g'));
        $this->post($url, ['operation' => 'charge', 'fluid' => 'R404A', 'kg' => '1,2345'])->assertSessionHasErrors('kg');
        $this->post($url, ['operation' => 'recovery', 'fluid' => 'R404A', 'kg' => '9'])->assertSessionHasErrors('action'); // plus que la charge
        $this->assertSame(1650, $this->eq->fluidMovements()->sum('quantity_g'));
    }

    public function test_signature_valide_stockee_en_prive_et_invalide_refusee(): void
    {
        Storage::fake('local');
        $this->actingAs($this->tech);
        $url = "/technicien/interventions/{$this->mine->id}/signature";

        $this->post($url, ['signed_by' => 'Mme Adjovi', 'signature' => 'data:image/png;base64,' . base64_encode('pas une image')])->assertSessionHasErrors('signature');
        $this->assertNull($this->mine->fresh()->signature_path);

        $this->post($url, ['signed_by' => 'Mme Adjovi', 'signature' => Png::signatureDataUrl()])->assertSessionHasNoErrors();
        $i = $this->mine->fresh();
        Storage::disk('local')->assertExists($i->signature_path);
        $this->assertSame('Mme Adjovi', $i->signed_by);
        $this->get("/technicien/interventions/{$i->id}/signature")->assertOk();
    }

    public function test_photo_envoyee_sur_le_disque_prive_et_non_image_refusee(): void
    {
        Storage::fake('local');
        $this->actingAs($this->tech);
        $url = "/technicien/interventions/{$this->mine->id}/photos";

        $this->post($url, ['photo' => UploadedFile::fake()->createWithContent('virus.php', '<?php echo 1;')])->assertSessionHasErrors('photo');
        $this->assertSame(0, $this->mine->photos()->count());

        $this->post($url, ['photo' => UploadedFile::fake()->createWithContent('panne.png', base64_decode(Png::PIXEL_1x1))])->assertSessionHasNoErrors();
        $photo = $this->mine->photos()->firstOrFail();
        Storage::disk('local')->assertExists($photo->path);
        $this->get("/technicien/photos/{$photo->id}")->assertOk();

        $this->actingAs($this->other)->get("/technicien/photos/{$photo->id}")->assertForbidden();
    }

    public function test_terminer_exige_une_description_retire_le_stock_puis_verrouille(): void
    {
        $p = $this->product(5);
        $this->actingAs($this->tech);
        $this->post("/technicien/interventions/{$this->mine->id}/lignes", ['kind' => 'part', 'product_id' => $p->id, 'quantity' => 2]);

        $this->post("/technicien/interventions/{$this->mine->id}/terminer", ['work_done' => ''])->assertSessionHasErrors('work_done');
        $this->assertSame(5, $p->fresh()->stock_quantity);

        $this->post("/technicien/interventions/{$this->mine->id}/terminer", ['work_done' => 'Détendeur remplacé, essai OK'])->assertRedirect('/technicien');
        $this->assertSame('done', $this->mine->fresh()->status);
        $this->assertSame(3, $p->fresh()->stock_quantity);

        $this->post("/technicien/interventions/{$this->mine->id}/lignes", ['kind' => 'part', 'product_id' => $p->id, 'quantity' => 1])->assertSessionHasErrors('action'); // clôturée
        $this->assertSame(1, $this->mine->lines()->count());
    }
}
