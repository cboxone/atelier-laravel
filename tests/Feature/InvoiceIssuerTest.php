<?php

namespace Tests\Feature;

use App\Invoicing\{BillingService, EmecefClient, InvoiceException, InvoiceIssuer};
use App\Models\{Customer, Invoice};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\FakeEmecefClient;
use Tests\TestCase;

class InvoiceIssuerTest extends TestCase
{
    use RefreshDatabase;

    private FakeEmecefClient $dgi;
    private Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();
        config(['emecef.enabled' => true, 'emecef.required' => true, 'emecef.ifu' => '9999900000001']);
        $this->dgi = new FakeEmecefClient();
        $this->app->instance(EmecefClient::class, $this->dgi);
        $this->customer = Customer::create(['name' => 'Client', 'phone' => '45661122']);
    }

    private function draft(int $price = 1800, int $qty = 2): Invoice
    {
        $i = Invoice::create(['customer_id' => $this->customer->id]);
        $i->lines()->create(['description' => 'Recharge gaz', 'quantity' => $qty, 'unit_price_fcfa' => $price, 'tax_group' => 'B']);
        return $i;
    }

    private function issuer(): InvoiceIssuer
    {
        return $this->app->make(InvoiceIssuer::class);
    }

    public function test_emission_normalisee_reussie(): void
    {
        $inv = $this->issuer()->issue($this->draft(), 'Jacques');

        $this->assertSame('issued', $inv->status);
        $this->assertTrue($inv->normalized);
        $this->assertSame('F-' . now()->format('Y') . '-00001', $inv->number);
        $this->assertSame('TEST-AAAA-BBBB-CCCC', $inv->emecef_code);
        $this->assertSame(3600, $inv->total_fcfa);
        $this->assertSame(549, $inv->tax_fcfa);
        $this->assertSame(['create', 'finalizeconfirm'], $this->dgi->actions());
        $this->assertSame('CREDIT', $this->dgi->calls[0][1]['payment'][0]['name']); // pas encore payée : le reste est « à crédit »
    }

    public function test_les_paiements_deja_saisis_sont_envoyes(): void
    {
        $inv = $this->draft();
        $this->app->make(BillingService::class)->recordPayment($inv, 'cash', 1000);
        $this->issuer()->issue($inv, 'Jacques');
        $this->assertSame([['name' => 'ESPECES', 'amount' => 1000], ['name' => 'CREDIT', 'amount' => 2600]], $this->dgi->calls[0][1]['payment']);
    }

    public function test_total_different_de_la_dgi_annule_et_n_emet_rien(): void
    {
        $this->dgi->createResponse = ['uid' => 'uid-9', 'total' => 3599];
        $inv = $this->draft();
        try { $this->issuer()->issue($inv, 'Jacques'); $this->fail('émission acceptée à tort'); } catch (InvoiceException $e) { $this->assertStringContainsString('Total différent', $e->getMessage()); }

        $inv->refresh();
        $this->assertSame('failed', $inv->status);
        $this->assertNull($inv->emecef_code);
        $this->assertFalse($inv->normalized);
        $this->assertSame(['create', 'finalizecancel'], $this->dgi->actions()); // jamais de « confirm »
    }

    public function test_reponse_sans_total_ou_sans_code_est_refusee(): void
    {
        $this->dgi->createResponse = ['uid' => 'uid-1'];
        $a = $this->draft();
        try { $this->issuer()->issue($a, 'J'); $this->fail(); } catch (InvoiceException $e) { $this->addToAssertionCount(1); }
        $this->assertSame('failed', $a->fresh()->status);

        $this->dgi->createResponse = null;
        $this->dgi->finalizeResponse = ['nim' => 'EM1'];
        $b = $this->draft();
        try { $this->issuer()->issue($b, 'J'); $this->fail(); } catch (InvoiceException $e) { $this->addToAssertionCount(1); }
        $this->assertSame('failed', $b->fresh()->status);
        $this->assertFalse($b->fresh()->normalized);
    }

    public function test_l_echec_garde_le_numero_et_la_reemission_le_reutilise(): void
    {
        $this->dgi->failWith = new InvoiceException('DGI indisponible');
        $inv = $this->draft();
        try { $this->issuer()->issue($inv, 'J'); } catch (InvoiceException $e) {}
        $numero = $inv->fresh()->number;
        $this->assertNotNull($numero);

        $this->dgi->failWith = null;
        $this->issuer()->issue($inv->fresh(), 'J');
        $this->assertSame($numero, $inv->fresh()->number);
        $this->assertSame('issued', $inv->fresh()->status);

        $suivante = $this->issuer()->issue($this->draft(), 'J');
        $this->assertStringEndsWith('00002', $suivante->number); // pas de trou
    }

    public function test_assujetti_tva_sans_e_mecef_actif_ne_peut_rien_emettre(): void
    {
        config(['emecef.enabled' => false, 'emecef.required' => true]);
        $inv = $this->draft();
        $this->expectException(InvoiceException::class);
        try { $this->issuer()->issue($inv, 'J'); } finally { $this->assertSame('failed', $inv->fresh()->status); $this->assertSame([], $this->dgi->calls); }
    }

    public function test_non_assujetti_sans_e_mecef_emet_un_document_non_normalise(): void
    {
        config(['emecef.enabled' => false, 'emecef.required' => false]);
        $inv = $this->issuer()->issue($this->draft(), 'J');
        $this->assertSame('issued', $inv->status);
        $this->assertFalse($inv->normalized);
        $this->assertSame([], $this->dgi->calls);
    }

    public function test_facture_emise_et_facture_numerotee_sont_intouchables(): void
    {
        $inv = $this->issuer()->issue($this->draft(), 'J');
        try { $inv->update(['total_fcfa' => 1]); $this->fail('modification possible'); } catch (\LogicException $e) { $this->addToAssertionCount(1); }
        try { $inv->delete(); $this->fail('suppression possible'); } catch (\LogicException $e) { $this->addToAssertionCount(1); }
        $this->expectException(InvoiceException::class);
        $this->issuer()->issue($inv->fresh(), 'J'); // pas d'émission en double
    }

    public function test_facture_vide_ou_a_zero_refusee(): void
    {
        $vide = Invoice::create(['customer_id' => $this->customer->id]);
        $this->expectException(InvoiceException::class);
        $this->issuer()->issue($vide, 'J');
    }

    public function test_avoir_reference_la_facture_d_origine_et_ne_depasse_pas_son_montant(): void
    {
        $billing = $this->app->make(BillingService::class);
        $orig = $this->issuer()->issue($this->draft(), 'J');

        $credit = $billing->creditNote($orig, $orig->lines->map->only(['description', 'quantity', 'unit_price_fcfa', 'tax_group'])->all());
        $this->issuer()->issue($credit, 'J');
        $this->assertSame('FA', $credit->type);
        $this->assertSame('TESTAAAABBBBCCCC', $this->dgi->calls[2][1]['reference']); // code d'origine, sans tirets
        $this->assertStringStartsWith('AV-', $credit->fresh()->number);

        $this->expectException(InvoiceException::class); // déjà crédité en totalité
        $billing->creditNote($orig->fresh(), [['description' => 'x', 'quantity' => 1, 'unit_price_fcfa' => 1, 'tax_group' => 'B']]);
    }

    public function test_paiement_superieur_au_reste_refuse(): void
    {
        $inv = $this->issuer()->issue($this->draft(), 'J');
        $billing = $this->app->make(BillingService::class);
        $billing->recordPayment($inv, 'cash', 3000);
        $this->expectException(InvoiceException::class);
        $billing->recordPayment($inv->fresh(), 'cash', 601);
    }
}
