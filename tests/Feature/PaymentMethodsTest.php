<?php

namespace Tests\Feature;

use App\Models\{Order, PaymentMethod, Product, ProductCategory, ServiceZone};
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PaymentMethodsTest extends TestCase
{
    use RefreshDatabase;

    private Product $product;
    private ServiceZone $zone;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->product = Product::create([
            'product_category_id' => ProductCategory::first()->id, 'name' => 'Split test', 'slug' => 'split-test',
            'price_fcfa' => 100000, 'availability' => 'in_stock', 'is_published' => true,
        ]);
        $this->zone = ServiceZone::create(['name' => 'Zone test', 'delivery_fee_fcfa' => 2000, 'is_active' => true]);
        $this->post("/panier/ajouter/{$this->product->id}");
    }

    private function payload(string $method): array
    {
        return [
            'customer_name' => 'Client Test', 'phone' => '+229 01 02 03 04', 'zone_id' => $this->zone->id,
            'address' => 'Rue test', 'payment_method' => $method, 'consent' => '1',
            'checkout_token' => (string) Str::uuid(),
        ];
    }

    public function test_les_moyens_sans_configuration_sont_proposes_par_defaut(): void
    {
        $this->get('/commande')->assertOk()->assertSee('Paiement à la livraison')->assertSee('Paiement à l’atelier')
            ->assertDontSee('Virement bancaire')->assertDontSee('Paiement en ligne');
    }

    public function test_un_virement_active_affiche_ses_consignes_avec_reference_et_total(): void
    {
        PaymentMethod::where('code', 'bank_transfer')->update(['is_enabled' => true, 'instructions' => 'Virez {total} avec la référence {reference}.']);

        $this->post('/commande', $this->payload('bank_transfer'))->assertRedirect('/commande/merci');

        $order = Order::firstOrFail();
        $this->assertSame('pending', $order->payment_status);
        $this->get('/commande/merci')->assertOk()->assertSee($order->reference)->assertSee('102 000 FCFA');
    }

    public function test_le_paiement_a_l_atelier_fonctionne(): void
    {
        $this->post('/commande', $this->payload('pay_at_workshop'))->assertRedirect('/commande/merci');
        $this->assertDatabaseHas('orders', ['payment_method' => 'pay_at_workshop']);
    }

    public function test_le_paiement_en_ligne_reste_cache_sans_cles(): void
    {
        PaymentMethod::where('code', 'online')->update(['is_enabled' => true]); // activé mais FedaPay non configuré
        $this->get('/commande')->assertDontSee('Paiement en ligne');
        $this->post('/commande', $this->payload('online'))->assertSessionHasErrors('payment_method');
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_le_plafond_d_un_moyen_de_paiement_est_respecte(): void
    {
        PaymentMethod::where('code', 'cash_on_delivery')->update(['max_amount_fcfa' => 1000]);
        $this->post('/commande', $this->payload('cash_on_delivery'))->assertSessionHasErrors('cart');
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_le_retour_de_la_page_de_paiement_ne_valide_rien(): void
    {
        $this->get('/commande/paiement/retour')->assertRedirect('/commande/suivi');
        $this->assertDatabaseCount('payments', 0);
    }

    public function test_un_rappel_fedapay_a_la_signature_invalide_est_rejete(): void
    {
        if (! class_exists(\FedaPay\Webhook::class)) {
            $this->markTestSkipped('Le package fedapay/fedapay-php n’est pas installé.');
        }
        config(['shop.fedapay.webhook_secret' => 'wh_test_secret']);

        $this->call('POST', '/webhooks/fedapay', [], [], [], ['HTTP_X-FEDAPAY-SIGNATURE' => 't=1,s=faux', 'CONTENT_TYPE' => 'application/json'], '{"name":"transaction.approved"}')
            ->assertStatus(400);
    }
}
