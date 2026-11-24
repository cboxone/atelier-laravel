<?php

namespace Tests\Feature;

use App\Models\{Order, Product, ProductCategory, ServiceZone, StockMovement};
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class RobustnessTest extends TestCase
{
    use RefreshDatabase;

    private Product $product;
    private ServiceZone $zone;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->product = Product::create([
            'product_category_id' => ProductCategory::first()->id, 'name' => 'Split', 'slug' => 'split',
            'price_fcfa' => 100000, 'availability' => 'in_stock', 'is_published' => true, 'stock_quantity' => 5,
        ]);
        $this->zone = ServiceZone::create(['name' => 'Zone test', 'delivery_fee_fcfa' => 2000, 'is_active' => true]);
    }

    private function payload(string $token): array
    {
        return [
            'customer_name' => 'Client Test', 'phone' => '+229 01 97 12 34 56', 'zone_id' => $this->zone->id,
            'address' => 'Rue test', 'payment_method' => 'cash_on_delivery', 'consent' => '1', 'checkout_token' => $token,
        ];
    }

    private function ledgerSum(): int
    {
        return (int) StockMovement::where('product_id', $this->product->id)->sum('quantity');
    }

    public function test_le_meme_formulaire_envoye_deux_fois_ne_cree_qu_une_commande(): void
    {
        $token = (string) Str::uuid();
        $this->post("/panier/ajouter/{$this->product->id}");

        $this->post('/commande', $this->payload($token))->assertRedirect('/commande/merci');
        $this->post('/commande', $this->payload($token))->assertRedirect('/commande/merci'); // double clic

        $this->assertDatabaseCount('orders', 1);
        $this->assertSame(4, $this->product->fresh()->stock_quantity); // une seule unité retirée
    }

    public function test_un_envoi_sans_jeton_est_refuse(): void
    {
        $this->post("/panier/ajouter/{$this->product->id}");
        $this->post('/commande', array_diff_key($this->payload('x'), ['checkout_token' => 1]))->assertSessionHasErrors('checkout_token');
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_le_journal_du_stock_correspond_toujours_a_la_quantite(): void
    {
        $this->assertSame(5, $this->ledgerSum());                       // création : +5

        $this->post("/panier/ajouter/{$this->product->id}");
        $this->patch("/panier/{$this->product->id}", ['quantity' => 2]);
        $this->post('/commande', $this->payload((string) Str::uuid()));
        $this->assertSame(3, $this->product->fresh()->stock_quantity);
        $this->assertSame(3, $this->ledgerSum());                       // commande : -2
        $this->assertSame(1, StockMovement::where('reason', 'order')->count()); // pas de double écriture

        Order::firstOrFail()->update(['status' => 'cancelled']);
        $this->assertSame(5, $this->product->fresh()->stock_quantity);
        $this->assertSame(5, $this->ledgerSum());                       // annulation : +2

        $this->product->update(['stock_quantity' => 9]);
        $this->assertSame(9, $this->ledgerSum());                       // correction manuelle : +4
        $this->assertDatabaseHas('stock_movements', ['product_id' => $this->product->id, 'quantity' => 4, 'reason' => 'adjustment']);
    }

    public function test_les_modifications_sans_rapport_avec_le_stock_ne_creent_pas_de_mouvement(): void
    {
        $avant = StockMovement::count();
        $this->product->update(['name' => 'Split renommé', 'price_fcfa' => 120000]);
        $this->assertSame($avant, StockMovement::count());
    }
}
