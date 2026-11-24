<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AtelierAccessTest extends TestCase
{
    use RefreshDatabase;

    private array $urls = ['/atelier', '/atelier/devis', '/atelier/devis/nouveau', '/atelier/factures', '/atelier/interventions', '/atelier/interventions/nouvelle'];

    public function test_un_visiteur_est_renvoye_vers_la_connexion(): void
    {
        foreach ($this->urls as $url) $this->get($url)->assertRedirect('/admin/login');
    }

    public function test_un_utilisateur_non_admin_est_refuse(): void
    {
        $this->actingAs(User::factory()->create());
        foreach ($this->urls as $url) $this->get($url)->assertForbidden();
        $this->post('/atelier/factures/1/emettre')->assertForbidden();
    }

    public function test_un_admin_accede_a_l_espace_atelier(): void
    {
        $admin = User::factory()->create();
        $admin->is_admin = true;
        $admin->save();
        $this->actingAs($admin);
        foreach ($this->urls as $url) $this->get($url)->assertOk();
    }
}
