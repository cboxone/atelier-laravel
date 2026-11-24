<?php

namespace Tests\Feature;

use App\Models\{Order, OrderItem, Payment, Product, ProductCategory};
use App\Payments\FedaPayReconciler;
use Tests\Support\FakeReconciler;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpireUnpaidOrdersTest extends TestCase
{
    use RefreshDatabase;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        // Stock après la vente de 2 unités (5 au départ).
        $this->product = Product::create([
            'product_category_id' => ProductCategory::first()->id, 'name' => 'Split', 'slug' => 'split',
            'price_fcfa' => 100000, 'availability' => 'in_stock', 'is_published' => true, 'stock_quantity' => 3,
        ]);
    }

    private function order(string $method, string $paymentStatus, int $minutesAgo): Order
    {
        $order = Order::create([
            'customer_name' => 'Test', 'phone' => '+229 01 02 03 04', 'district' => 'Z', 'address' => 'A',
            'subtotal_fcfa' => 200000, 'delivery_fee_fcfa' => 0, 'total_fcfa' => 200000,
            'payment_method' => $method, 'payment_status' => $paymentStatus,
        ]);
        OrderItem::create(['order_id' => $order->id, 'product_id' => $this->product->id, 'name' => 'Split',
            'unit_price_fcfa' => 100000, 'quantity' => 2, 'line_total_fcfa' => 200000]);
        Order::whereKey($order->id)->update(['created_at' => now()->subMinutes($minutesAgo)]);
        return $order;
    }

    public function test_un_paiement_echoue_depuis_plus_de_24h_libere_le_stock(): void
    {
        $order = $this->order('online', 'failed', 2000);
        $this->artisan('orders:expire-unpaid')->assertSuccessful();
        $this->assertSame('cancelled', $order->fresh()->status);
        $this->assertSame(5, $this->product->fresh()->stock_quantity);
    }

    public function test_une_commande_payee_chez_fedapay_n_est_jamais_annulee(): void
    {
        $this->app->instance(FedaPayReconciler::class, new FakeReconciler());
        FakeReconciler::respond('approved', 200000);
        $order = $this->order('online', 'pending', 600);
        Payment::create(['order_id' => $order->id, 'provider' => 'online', 'provider_reference' => '888', 'amount_fcfa' => 200000]);

        $this->artisan('orders:expire-unpaid')->assertSuccessful();

        $this->assertSame('new', $order->fresh()->status);
        $this->assertSame('paid', $order->fresh()->payment_status);
        $this->assertSame(3, $this->product->fresh()->stock_quantity);
    }

    public function test_si_fedapay_est_injoignable_on_n_annule_pas_par_prudence(): void
    {
        $this->app->instance(FedaPayReconciler::class, new FakeReconciler());
        FakeReconciler::$transaction = null; // simule une panne
        $order = $this->order('online', 'pending', 600);
        Payment::create(['order_id' => $order->id, 'provider' => 'online', 'provider_reference' => '889', 'amount_fcfa' => 200000]);

        $this->artisan('orders:expire-unpaid')->assertSuccessful();

        $this->assertSame('new', $order->fresh()->status);
    }

    public function test_une_commande_en_ligne_abandonnee_est_annulee_et_le_stock_revient(): void
    {
        $order = $this->order('online', 'pending', 600);
        $this->artisan('orders:expire-unpaid')->assertSuccessful();

        $this->assertSame('cancelled', $order->fresh()->status);
        $this->assertSame(5, $this->product->fresh()->stock_quantity);
    }

    public function test_les_commandes_recentes_payees_ou_manuelles_ne_sont_pas_touchees(): void
    {
        $recente = $this->order('online', 'pending', 10);
        $payee = $this->order('online', 'paid', 600);
        $livraison = $this->order('cash_on_delivery', 'pending', 600);
        $echec = $this->order('online', 'failed', 600);

        $this->artisan('orders:expire-unpaid')->assertSuccessful();

        foreach ([$recente, $payee, $livraison, $echec] as $o) {
            $this->assertSame('new', $o->fresh()->status);
        }
        $this->assertSame(3, $this->product->fresh()->stock_quantity);
    }
}
