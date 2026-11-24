<?php

namespace Tests\Feature;

use App\Models\{Customer, Equipment, FluidMovement, User};
use App\Workshop\{FluidRegister, WorkshopException};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FluidRegisterTest extends TestCase
{
    use RefreshDatabase;

    private Equipment $eq;
    private FluidRegister $reg;
    private User $tech;

    protected function setUp(): void
    {
        parent::setUp();
        $c = Customer::create(['name' => 'Client']);
        $this->eq = Equipment::create(['customer_id' => $c->id, 'label' => 'Chambre froide', 'fluid_type' => 'R404A']);
        $this->reg = new FluidRegister();
        $this->tech = User::factory()->create();
    }

    private function rec(string $op, int $g, string $fluid = 'R404A'): FluidMovement
    {
        return $this->reg->record($this->eq, $op, $g, $fluid, $this->tech);
    }

    public function test_le_solde_est_la_somme_des_ecritures(): void
    {
        $this->rec('initial', 2000);
        $this->rec('recovery', 350);
        $this->rec('charge', 800);
        $this->assertSame(2450, $this->reg->balance($this->eq));
    }

    public function test_on_ne_recupere_pas_plus_que_la_charge(): void
    {
        $this->rec('initial', 500);
        $this->expectException(WorkshopException::class);
        $this->rec('recovery', 501);
    }

    public function test_charge_initiale_une_seule_fois_et_avant_le_reste(): void
    {
        $this->rec('charge', 100);
        $this->expectException(WorkshopException::class);
        $this->rec('initial', 2000);
    }

    public function test_fluide_different_de_l_equipement_refuse(): void
    {
        $this->expectException(WorkshopException::class);
        $this->rec('initial', 1000, 'R32');
    }

    public function test_quantite_nulle_ou_negative_refusee(): void
    {
        $this->expectException(WorkshopException::class);
        $this->rec('charge', 0);
    }

    public function test_une_ecriture_ne_se_modifie_ni_ne_se_supprime(): void
    {
        $m = $this->rec('initial', 1000);
        try { $m->update(['quantity_g' => 5]); $this->fail('modification possible'); } catch (\LogicException $e) { $this->addToAssertionCount(1); }
        try { $m->delete(); $this->fail('suppression possible'); } catch (\LogicException $e) { $this->addToAssertionCount(1); }
        $this->assertSame(1000, $m->fresh()->quantity_g);
    }

    public function test_une_correction_est_une_ecriture_inverse_visible(): void
    {
        $this->rec('initial', 1000);
        $erreur = $this->rec('charge', 300);
        $corr = $this->reg->reverse($erreur, $this->tech, 'saisie sur le mauvais équipement');

        $this->assertSame(-300, $corr->quantity_g);
        $this->assertSame('correction', $corr->operation);
        $this->assertSame($erreur->id, $corr->reverses_id);
        $this->assertSame(1000, $this->reg->balance($this->eq));
        $this->assertSame(3, FluidMovement::count()); // l'erreur d'origine reste dans le registre
    }

    public function test_on_ne_corrige_pas_deux_fois_ni_une_correction(): void
    {
        $this->rec('initial', 1000);
        $m = $this->rec('charge', 300);
        $corr = $this->reg->reverse($m, $this->tech, 'erreur');
        foreach ([$m, $corr] as $target) {
            try { $this->reg->reverse($target, $this->tech, 'encore'); $this->fail('correction acceptée à tort'); } catch (WorkshopException $e) { $this->addToAssertionCount(1); }
        }
    }

    public function test_une_correction_qui_rendrait_le_solde_negatif_est_refusee(): void
    {
        $init = $this->rec('initial', 500);
        $this->rec('recovery', 400);
        $this->expectException(WorkshopException::class);
        $this->reg->reverse($init, $this->tech, 'erreur'); // retirer 500 g alors qu'il n'en reste que 100
    }
}
