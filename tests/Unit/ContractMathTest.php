<?php

namespace Tests\Unit;

use App\Support\{SignatureImage, VisitSchedule};
use PHPUnit\Framework\TestCase;

class ContractMathTest extends TestCase
{
    private const PNG_OK = 'iVBORw0KGgoAAAANSUhEUgAAAMgAAABQCAIAAADTD63nAAABRElEQVR42u3aMQqEQBBE0R3Z+195TDeQDbRaepj3M1N5dIE45pwfKd3hFQgsgSWwpOeNMX4fv96Igp7AUhUpsJTxdPnFCizdv09/PoKCpaQnsJSZPLD0hiewlJk8sFR7osBSoSeweKoiBRZPhb/igWXywFJ7T2CZPLC0wokCiyew1H7ywOIJLK0zeWDxBBZP60weWE4UWDwt6wkskwcWT2Bpt8kDiyewTB5YcqLA4gkskwcWTzyBZfLA4gkskyewnCiweALL5AksnsAyeWDxpB1hmTywnCiweNImsEweWDyBZfK0CSyewDJ5YDlR2gQWT2CZPHWFxRNYJk+NYfEElslTY1hOFFg8qSsskwcWT2oMy+QpCYsnJWGZPIVhOVFKwuJJSVgmT0lYPCkMy+QpCYsnlU8hUkrC4knPO6jSG7AksNS3E/ew2S1PcqKzAAAAAElFTkSuQmCC';
    private const PNG_1X1 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAIAAACQd1PeAAAADElEQVR42mNgYGAAAAAEAAHI6uv5AAAAAElFTkSuQmCC';

    public function test_visites_reparties_regulierement_sans_premier_ni_dernier_jour(): void
    {
        $dates = VisitSchedule::dates(new \DateTimeImmutable('2026-01-01'), new \DateTimeImmutable('2026-12-31'), 4);
        $this->assertSame(['2026-03-15', '2026-05-27', '2026-08-07', '2026-10-19'], $dates);
        $this->assertSame(['2026-07-02'], VisitSchedule::dates(new \DateTimeImmutable('2026-01-01'), new \DateTimeImmutable('2026-12-31'), 1));
    }

    public function test_periode_trop_courte_ou_nombre_invalide_refuse(): void
    {
        foreach ([[0, '2026-12-31'], [53, '2026-12-31'], [4, '2026-01-03'], [2, '2025-12-01']] as [$n, $end]) {
            try {
                VisitSchedule::dates(new \DateTimeImmutable('2026-01-01'), new \DateTimeImmutable($end), $n);
                $this->fail("accepté à tort : $n visites jusqu'au $end");
            } catch (\InvalidArgumentException $e) {
                $this->addToAssertionCount(1);
            }
        }
    }

    public function test_signature_png_valide_acceptee(): void
    {
        $bin = SignatureImage::decode('data:image/png;base64,' . self::PNG_OK);
        $this->assertSame("\x89PNG\r\n\x1a\n", substr($bin, 0, 8));
    }

    public function test_signatures_invalides_refusees(): void
    {
        $cases = [
            'pas une image' => 'data:image/png;base64,' . base64_encode('bonjour'),
            'mauvais type' => 'data:image/jpeg;base64,' . self::PNG_OK,
            'texte brut' => 'hello',
            'script' => 'data:image/png;base64,<script>alert(1)</script>',
            'trop petite' => 'data:image/png;base64,' . self::PNG_1X1,
            'base64 invalide' => 'data:image/png;base64,@@@@',
            'vide' => '',
        ];
        foreach ($cases as $label => $value) {
            try {
                SignatureImage::decode($value);
                $this->fail("acceptée à tort : $label");
            } catch (\InvalidArgumentException $e) {
                $this->addToAssertionCount(1);
            }
        }
        try {
            SignatureImage::decode('data:image/png;base64,' . self::PNG_OK, 100);
            $this->fail('trop lourde acceptée');
        } catch (\InvalidArgumentException $e) {
            $this->addToAssertionCount(1);
        }
    }
}
