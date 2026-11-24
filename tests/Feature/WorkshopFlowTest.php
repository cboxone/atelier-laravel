<?php

namespace Tests\Feature;

use App\Invoicing\{BillingService, InvoiceException, Numbering};
use App\Models\{Customer, Intervention, Invoice, Product, ProductCategory, Quote, StockMovement};
use App\Workshop\{InterventionService, WorkshopException};
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkshopFlowTest extends TestCase
{
    use RefreshDatabase;

    private Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->customer = Customer::create(['name' => 'Client']);
    }

    private function product(?int $stock): Product
    {
        static $n = 0; $n++;
        return Product::create(['product_category_id' => ProductCategory::first()->id, 'name' => "Pièce $n", 'slug' => "piece-$n", 'price_fcfa' => 5000, 'availability' => 'in_stock', 'is_published' => true, 'stock_quantity' => $stock]);
    }

    private function intervention(): Intervention
    {
        return Intervention::create(['customer_id' => $this->customer->id, 'title' => 'Dépannage']);
    }

    public function test_la_numerotation_est_continue_par_type_de_document(): void
    {
        $this->assertSame('F-' . now()->format('Y') . '-00001', Numbering::next('invoice'));
        $this->assertSame('F-' . now()->format('Y') . '-00002', Numbering::next('invoice'));
        $this->assertSame('D-' . now()->format('Y') . '-00001', Numbering::next('quote'));
    }

    public function test_terminer_une_intervention_retire_les_pieces_du_stock_une_seule_fois(): void
    {
        $p = $this->product(5);
        $i = $this->intervention();
        $i->lines()->create(['kind' => 'part', 'product_id' => $p->id, 'description' => 'Pièce', 'quantity' => 2, 'unit_price_fcfa' => 5000, 'tax_group' => 'B']);
        $service = new InterventionService();

        $service->finish($i);
        $service->finish($i->fresh()); // deuxième appel : sans effet

        $this->assertSame(3, $p->fresh()->stock_quantity);
        $this->assertSame('done', $i->fresh()->status);
        $this->assertSame(1, StockMovement::where('reason', 'intervention')->where('intervention_id', $i->id)->count());
    }

    public function test_stock_insuffisant_bloque_la_fin_sans_rien_modifier(): void
    {
        $a = $this->product(10);
        $b = $this->product(1);
        $i = $this->intervention();
        foreach ([[$a, 2], [$b, 2]] as [$p, $q]) $i->lines()->create(['kind' => 'part', 'product_id' => $p->id, 'description' => 'x', 'quantity' => $q, 'unit_price_fcfa' => 1, 'tax_group' => 'B']);

        try { (new InterventionService())->finish($i); $this->fail('fin acceptée à tort'); } catch (WorkshopException $e) { $this->addToAssertionCount(1); }

        $this->assertSame(10, $a->fresh()->stock_quantity); // tout ou rien : la première pièce n'a pas été retirée
        $this->assertSame('planned', $i->fresh()->status);
    }

    public function test_stock_non_suivi_est_ignore(): void
    {
        $p = $this->product(null);
        $i = $this->intervention();
        $i->lines()->create(['kind' => 'part', 'product_id' => $p->id, 'description' => 'x', 'quantity' => 3, 'unit_price_fcfa' => 1, 'tax_group' => 'B']);
        (new InterventionService())->finish($i);
        $this->assertNull($p->fresh()->stock_quantity);
    }

    public function test_facturer_une_intervention_exige_qu_elle_soit_terminee_et_une_seule_fois(): void
    {
        $billing = new BillingService();
        $i = $this->intervention();
        $i->lines()->create(['kind' => 'labor', 'description' => 'Main-d’œuvre', 'quantity' => 1, 'unit_price_fcfa' => 10000, 'tax_group' => 'B']);

        try { $billing->invoiceFromIntervention($i); $this->fail('facturée avant la fin'); } catch (InvoiceException $e) { $this->addToAssertionCount(1); }

        (new InterventionService())->finish($i);
        $invoice = $billing->invoiceFromIntervention($i->fresh());
        $this->assertSame(10000, $invoice->lines->sum(fn ($l) => $l->quantity * $l->unit_price_fcfa));

        $this->expectException(InvoiceException::class);
        $billing->invoiceFromIntervention($i->fresh()); // pas de doublon
    }

    public function test_un_devis_n_est_facturable_qu_une_fois_accepte(): void
    {
        $billing = new BillingService();
        $q = Quote::create(['customer_id' => $this->customer->id]);
        $q->lines()->create(['description' => 'Installation split', 'quantity' => 1, 'unit_price_fcfa' => 200000, 'tax_group' => 'B']);

        try { $billing->invoiceFromQuote($q); $this->fail('devis non accepté facturé'); } catch (InvoiceException $e) { $this->addToAssertionCount(1); }

        $q->update(['status' => 'accepted']);
        $invoice = $billing->invoiceFromQuote($q->fresh());
        $this->assertSame('invoiced', $q->fresh()->status);
        $this->assertSame($q->id, $invoice->quote_id);

        $this->expectException(InvoiceException::class);
        $billing->invoiceFromQuote($q->fresh()); // déjà facturé
    }
}
