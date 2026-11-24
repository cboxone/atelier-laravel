<?php

namespace Tests\Feature;

use App\Invoicing\InvoiceException;
use App\Models\{Customer, Equipment, Intervention, MaintenanceContract};
use App\Workshop\{ContractService, InterventionService, WorkshopException};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContractTest extends TestCase
{
    use RefreshDatabase;

    private Customer $customer;
    private Equipment $a;
    private Equipment $b;
    private ContractService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->customer = Customer::create(['name' => 'Hôtel']);
        $this->a = Equipment::create(['customer_id' => $this->customer->id, 'label' => 'Split salon']);
        $this->b = Equipment::create(['customer_id' => $this->customer->id, 'label' => 'Split chambre']);
        $this->service = new ContractService();
    }

    private function data(array $over = []): array
    {
        return array_merge(['customer_id' => $this->customer->id, 'title' => 'Entretien annuel', 'starts_on' => '2026-01-01', 'ends_on' => '2026-12-31', 'visits_per_year' => 4, 'price_fcfa' => 480000], $over);
    }

    public function test_le_contrat_planifie_ses_visites_reparties_sur_la_periode(): void
    {
        $c = $this->service->create($this->data(), [$this->a->id, $this->b->id]);
        $this->assertSame(['2026-03-15', '2026-05-27', '2026-08-07', '2026-10-19'], $c->visits->map(fn ($v) => $v->due_on->toDateString())->all());
        $this->assertSame(2, $c->equipments()->count());
    }

    public function test_equipement_d_un_autre_client_refuse_et_rien_n_est_cree(): void
    {
        $autre = Equipment::create(['customer_id' => Customer::create(['name' => 'Autre'])->id, 'label' => 'Split']);
        try { $this->service->create($this->data(), [$this->a->id, $autre->id]); $this->fail('contrat accepté à tort'); } catch (WorkshopException $e) { $this->addToAssertionCount(1); }
        $this->assertSame(0, MaintenanceContract::count());
    }

    public function test_periode_trop_courte_ou_sans_equipement_refusee(): void
    {
        foreach ([[$this->data(['ends_on' => '2026-01-03']), [$this->a->id]], [$this->data(), []]] as [$data, $eq]) {
            try { $this->service->create($data, $eq); $this->fail('accepté à tort'); } catch (WorkshopException $e) { $this->addToAssertionCount(1); }
        }
    }

    public function test_les_interventions_sont_creees_une_fois_par_equipement_sans_doublon(): void
    {
        $c = $this->service->create($this->data(), [$this->a->id, $this->b->id]);

        $today = new \DateTimeImmutable('2026-03-10'); // la première visite (15 mars) est dans moins de 14 jours
        $this->assertSame(2, $this->service->generateInterventions(14, $today));
        $this->assertSame(0, $this->service->generateInterventions(14, $today)); // relancer ne duplique rien
        $this->assertSame(2, Intervention::where('contract_visit_id', $c->visits->first()->id)->count());
        $this->assertSame('2026-03-15 08:00:00', Intervention::first()->scheduled_at->format('Y-m-d H:i:s'));
    }

    public function test_un_contrat_annule_ne_genere_rien(): void
    {
        $c = $this->service->create($this->data(), [$this->a->id]);
        $c->update(['status' => 'cancelled']);
        $this->assertSame(0, $this->service->generateInterventions(14, new \DateTimeImmutable('2026-03-10')));
    }

    public function test_la_visite_est_faite_quand_toutes_ses_interventions_sont_terminees(): void
    {
        $c = $this->service->create($this->data(), [$this->a->id, $this->b->id]);
        $this->service->generateInterventions(14, new \DateTimeImmutable('2026-03-10'));
        [$i1, $i2] = Intervention::orderBy('id')->get()->all();
        $svc = new InterventionService();

        $svc->finish($i1);
        $this->assertSame('planned', $c->visits()->first()->status);
        $svc->finish($i2);
        $this->assertSame('done', $c->visits()->first()->status);
    }

    public function test_facturation_du_contrat_une_seule_fois_et_seulement_si_actif(): void
    {
        $c = $this->service->create($this->data(), [$this->a->id]);
        $invoice = $this->service->invoice($c);
        $this->assertSame(480000, $invoice->lines->sum(fn ($l) => $l->quantity * $l->unit_price_fcfa));
        $this->assertNotNull($c->fresh()->invoiced_at);

        try { $this->service->invoice($c->fresh()); $this->fail('double facturation'); } catch (InvoiceException $e) { $this->addToAssertionCount(1); }

        $c2 = $this->service->create($this->data(['title' => 'Autre']), [$this->a->id]);
        $c2->update(['status' => 'cancelled']);
        $this->expectException(InvoiceException::class);
        $this->service->invoice($c2->fresh());
    }
}
