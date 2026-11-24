<?php

namespace Tests\Feature;

use App\Models\{Order, Payment, Setting};
use App\Notifications\PaymentOnCancelledOrder;
use App\Payments\FedaPayReconciler;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\Support\FakeReconciler;
use Tests\TestCase;

class ReconcilerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->app->instance(FedaPayReconciler::class, new FakeReconciler());
        FakeReconciler::$transaction = null;
    }

    private function onlineOrder(string $orderStatus = 'new'): array
    {
        $order = Order::create([
            'customer_name' => 'Test', 'phone' => '+229 01 02 03 04', 'district' => 'Z', 'address' => 'A',
            'subtotal_fcfa' => 100000, 'delivery_fee_fcfa' => 2000, 'total_fcfa' => 102000,
            'payment_method' => 'online', 'payment_status' => 'pending', 'status' => $orderStatus,
        ]);
        $payment = Payment::create(['order_id' => $order->id, 'provider' => 'online', 'provider_reference' => '777', 'amount_fcfa' => 102000]);
        return [$order, $payment];
    }

    private function reconcile(Payment $payment): void
    {
        app(FedaPayReconciler::class)->reconcile($payment);
    }

    public function test_une_transaction_approuvee_au_bon_montant_valide_le_paiement(): void
    {
        [$order, $payment] = $this->onlineOrder();
        FakeReconciler::respond('approved', 102000);
        $this->reconcile($payment);
        $this->assertSame('paid', $payment->fresh()->status);
        $this->assertSame('paid', $order->fresh()->payment_status);
    }

    public function test_une_transaction_transferee_est_aussi_un_paiement_reussi(): void
    {
        [$order, $payment] = $this->onlineOrder();
        FakeReconciler::respond('transferred', 102000);
        $this->reconcile($payment);
        $this->assertSame('paid', $order->fresh()->payment_status);
    }

    public function test_un_montant_different_ne_valide_rien(): void
    {
        [$order, $payment] = $this->onlineOrder();
        FakeReconciler::respond('approved', 1000);
        $this->reconcile($payment);
        $this->assertSame('pending', $payment->fresh()->status);
        $this->assertSame('pending', $order->fresh()->payment_status);
    }

    public function test_le_meme_rappel_deux_fois_ne_change_rien_de_plus(): void
    {
        [$order, $payment] = $this->onlineOrder();
        FakeReconciler::respond('approved', 102000);
        $this->reconcile($payment);
        $updatedAt = $payment->fresh()->updated_at;
        $this->travel(5)->minutes();
        $this->reconcile($payment->fresh());
        $this->assertEquals($updatedAt, $payment->fresh()->updated_at);
    }

    public function test_un_paiement_refuse_ou_annule_est_marque_echoue(): void
    {
        foreach (['declined', 'canceled'] as $status) {
            [$order, $payment] = $this->onlineOrder();
            FakeReconciler::respond($status, 102000);
            $this->reconcile($payment);
            $this->assertSame('failed', $order->fresh()->payment_status, $status);
        }
    }

    public function test_un_remboursement_est_enregistre(): void
    {
        [$order, $payment] = $this->onlineOrder();
        FakeReconciler::respond('approved', 102000);
        $this->reconcile($payment);
        FakeReconciler::respond('refunded', 102000);
        $this->reconcile($payment->fresh());
        $this->assertSame('refunded', $order->fresh()->payment_status);
    }

    public function test_un_paiement_sur_une_commande_annulee_previent_le_gerant(): void
    {
        Notification::fake();
        Setting::updateOrCreate(['key' => 'notify_email'], ['value' => 'gerant@example.com']);
        [$order, $payment] = $this->onlineOrder('cancelled');
        FakeReconciler::respond('approved', 102000);

        $this->reconcile($payment);

        Notification::assertSentOnDemand(PaymentOnCancelledOrder::class);
    }

    public function test_le_retour_du_client_interroge_fedapay_et_ne_croit_pas_l_adresse(): void
    {
        [$order, $payment] = $this->onlineOrder();

        // L'adresse affirme « approved » mais FedaPay répond « pending » : rien ne doit être validé.
        FakeReconciler::respond('pending', 102000);
        $this->get('/commande/paiement/retour?id=777&status=approved')->assertRedirect('/commande/suivi');
        $this->assertSame('pending', $order->fresh()->payment_status);

        FakeReconciler::respond('approved', 102000);
        $this->get('/commande/paiement/retour?id=777')->assertRedirect('/commande/suivi');
        $this->assertSame('paid', $order->fresh()->payment_status);
    }

    public function test_la_commande_de_rattrapage_valide_les_paiements_en_attente(): void
    {
        [$order, $payment] = $this->onlineOrder();
        FakeReconciler::respond('approved', 102000);
        $this->artisan('payments:reconcile')->assertSuccessful();
        $this->assertSame('paid', $order->fresh()->payment_status);
    }
}
