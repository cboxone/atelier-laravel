<?php

namespace Tests\Unit;

use App\Invoicing\{EmecefPayload, InvoiceException, InvoiceTotals};
use App\Support\Mass;
use PHPUnit\Framework\TestCase;

// Tests sans base de données ni Laravel : calculs et formats uniquement.
class BillingMathTest extends TestCase
{
    private array $rates = ['A' => 0, 'B' => 18];

    private function line(int $q, int $p, string $g): array
    {
        return ['quantity' => $q, 'unit_price_fcfa' => $p, 'tax_group' => $g];
    }

    public function test_totaux_et_tva_contenue_dans_un_prix_ttc(): void
    {
        $t = InvoiceTotals::compute([$this->line(2, 1800, 'B'), $this->line(3, 450, 'A')], $this->rates);
        $this->assertSame(4950, $t['total']);
        $this->assertSame(549, $t['tax']);
        $this->assertSame(18, InvoiceTotals::compute([$this->line(1, 118, 'B')], $this->rates)['tax']);
        $this->assertSame(15254, InvoiceTotals::compute([$this->line(1, 100000, 'B')], $this->rates)['tax']);
    }

    public function test_groupe_inconnu_ou_quantite_nulle_refuses(): void
    {
        $this->expectException(InvoiceException::class);
        InvoiceTotals::compute([$this->line(1, 10, 'C')], $this->rates);
    }

    public function test_quantite_nulle_refusee(): void
    {
        $this->expectException(InvoiceException::class);
        InvoiceTotals::compute([$this->line(0, 10, 'B')], $this->rates);
    }

    private function data(array $over = []): array
    {
        return array_merge(['ifu' => '9999900000001', 'type' => 'FV', 'lines' => [['description' => ' Recharge gaz ', 'quantity' => 2, 'unit_price_fcfa' => 1800, 'tax_group' => 'B']],
            'customer' => ['name' => 'Client', 'phone' => '45661122', 'ifu' => null, 'address' => ''], 'operator' => 'Jacques', 'payments' => [['method' => 'cash', 'amount' => 3600]]], $over);
    }

    public function test_message_e_mecef(): void
    {
        $p = EmecefPayload::build($this->data(), ['cash' => 'ESPECES']);
        $this->assertSame(['name' => 'Recharge gaz', 'price' => 1800, 'quantity' => 2, 'taxGroup' => 'B'], $p['items'][0]);
        $this->assertSame(['name' => 'Client', 'contact' => '45661122'], $p['client']);
        $this->assertSame([['name' => 'ESPECES', 'amount' => 3600]], $p['payment']);
        $this->assertArrayNotHasKey('reference', $p);
    }

    public function test_avoir_reference_sans_tirets_et_refus_si_absente(): void
    {
        $fa = EmecefPayload::build($this->data(['type' => 'FA', 'reference' => 'TEST-2TJK-LKV6-722Q']), ['cash' => 'ESPECES']);
        $this->assertSame('TEST2TJKLKV6722Q', $fa['reference']);
        $this->expectException(InvoiceException::class);
        EmecefPayload::build($this->data(['type' => 'FA', 'reference' => null]), ['cash' => 'ESPECES']);
    }

    public function test_message_refuse_si_ifu_vide_ou_moyen_inconnu(): void
    {
        foreach ([['ifu' => ' '], ['payments' => [['method' => 'troc', 'amount' => 1]]], ['lines' => []]] as $over) {
            try {
                EmecefPayload::build($this->data($over), ['cash' => 'ESPECES']);
                $this->fail('Une exception était attendue.');
            } catch (InvoiceException $e) {
                $this->addToAssertionCount(1);
            }
        }
    }

    public function test_conversion_kg_en_grammes(): void
    {
        $this->assertSame(350, Mass::kgToGrams('0,350'));
        $this->assertSame(1500, Mass::kgToGrams('1.5'));
        $this->assertSame(2000, Mass::kgToGrams(' 2 '));
        foreach (['', '0', '-1', 'abc', '1,2345', '12345'] as $bad) $this->assertNull(Mass::kgToGrams($bad), $bad);
        $this->assertSame('0,350', Mass::gramsToKg(350));
    }
}
