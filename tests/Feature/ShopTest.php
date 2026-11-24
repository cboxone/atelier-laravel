<?php

namespace Tests\Feature;

use App\Models\{Order, Product, ProductCategory, ServiceZone};
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ShopTest extends TestCase
{
    use RefreshDatabase;

    private Product $product;
    private ServiceZone $zone;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->product = $this->makeProduct(['name' => 'Split test', 'price_fcfa' => 100000, 'stock_quantity' => 5]);
        $this->zone = ServiceZone::create(['name' => 'Zone test', 'delivery_fee_fcfa' => 2000, 'is_active' => true]);
    }

    private function makeProduct(array $over = []): Product
    {
        static $n = 0;
        $n++;
        return Product::create(array_merge([
            'product_category_id' => ProductCategory::first()->id, 'name' => "Produit $n", 'slug' => "produit-$n",
            'price_fcfa' => 50000, 'availability' => 'in_stock', 'is_published' => true,
        ], $over));
    }

    private function checkoutPayload(array $over = []): array
    {
        return array_merge([
            'customer_name' => 'Client Test', 'phone' => '+229 01 02 03 04', 'zone_id' => $this->zone->id,
            'address' => 'Rue test, près du marché', 'payment_method' => 'cash_on_delivery', 'consent' => '1',
            'checkout_token' => (string) Str::uuid(),
        ], $over);
    }

    private function order(int $qty = 2): Order
    {
        $this->post("/panier/ajouter/{$this->product->id}");
        $this->patch("/panier/{$this->product->id}", ['quantity' => $qty]);
        $this->post('/commande', $this->checkoutPayload())->assertRedirect('/commande/merci');
        return Order::firstOrFail();
    }

    public function test_un_produit_sans_prix_ne_va_pas_dans_le_panier(): void
    {
        $sansPrix = $this->makeProduct(['price_fcfa' => null]);
        $this->post("/panier/ajouter/{$sansPrix->id}")->assertSessionHasErrors('cart');
        $this->assertSame([], session('cart', []));
    }

    public function test_un_produit_epuise_ne_va_pas_dans_le_panier(): void
    {
        $epuise = $this->makeProduct(['stock_quantity' => 0]);
        $this->post("/panier/ajouter/{$epuise->id}")->assertSessionHasErrors('cart');
    }

    public function test_commande_a_la_livraison_calcule_le_total_cote_serveur_et_decremente_le_stock(): void
    {
        $order = $this->order(2);

        $this->assertSame(200000, $order->subtotal_fcfa);
        $this->assertSame(2000, $order->delivery_fee_fcfa);
        $this->assertSame(202000, $order->total_fcfa);
        $this->assertSame(3, $this->product->fresh()->stock_quantity);
        $this->assertDatabaseHas('stock_movements', ['product_id' => $this->product->id, 'quantity' => -2, 'reason' => 'order']);
        $this->assertSame([], session('cart', [])); // panier vidé
    }

    public function test_un_total_envoye_par_le_navigateur_est_ignore(): void
    {
        $this->post("/panier/ajouter/{$this->product->id}");
        $this->post('/commande', $this->checkoutPayload(['total_fcfa' => 1, 'unit_price_fcfa' => 1]));
        $this->assertSame(102000, Order::firstOrFail()->total_fcfa);
    }

    public function test_un_moyen_de_paiement_desactive_est_refuse(): void
    {
        $this->post("/panier/ajouter/{$this->product->id}");
        $this->post('/commande', $this->checkoutPayload(['payment_method' => 'bank_transfer']))->assertSessionHasErrors('payment_method');
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_une_zone_sans_tarif_de_livraison_est_refusee(): void
    {
        $sansTarif = ServiceZone::create(['name' => 'Hors zone', 'is_active' => true]);
        $this->post("/panier/ajouter/{$this->product->id}");
        $this->post('/commande', $this->checkoutPayload(['zone_id' => $sansTarif->id]))->assertSessionHasErrors('zone_id');
    }

    public function test_le_suivi_exige_la_reference_et_le_bon_telephone(): void
    {
        $order = $this->order(1);

        $this->post('/commande/suivi', ['reference' => $order->reference, 'phone' => '+229 99 99 99 99'])->assertSessionHasErrors('reference');
        $this->post('/commande/suivi', ['reference' => 'CMD-INCONNUE', 'phone' => '+229 01 02 03 04'])->assertSessionHasErrors('reference');
        // Même numéro écrit autrement : accepté.
        $this->post('/commande/suivi', ['reference' => $order->reference, 'phone' => '01020304'])->assertOk()->assertSee($order->reference);
    }

    public function test_l_annulation_remet_le_stock_une_seule_fois(): void
    {
        $order = $this->order(2);
        $this->assertSame(3, $this->product->fresh()->stock_quantity);

        $order->update(['status' => 'cancelled']);
        $this->assertSame(5, $this->product->fresh()->stock_quantity);

        $order->update(['status' => 'new']);
        $order->update(['status' => 'cancelled']);
        $this->assertSame(5, $this->product->fresh()->stock_quantity); // pas de double remise en stock
    }

    public function test_le_webhook_fedapay_est_ferme_tant_qu_il_n_est_pas_configure(): void
    {
        $this->postJson('/webhooks/fedapay', ['status' => 'approved'])->assertNotFound();
    }

    public function test_la_page_merci_n_est_pas_consultable_directement(): void
    {
        $this->get('/commande/merci')->assertRedirect('/panier');
    }
}
