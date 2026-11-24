<?php

namespace Tests\Feature;

use App\Rules\PhoneNumber;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class PhoneNumberTest extends TestCase
{
    public function test_les_numeros_usuels_sont_acceptes(): void
    {
        foreach (['+229 01 97 12 34 56', '+229 0197123456', '0197123456', '97123456', '+22997123456', '01 97 12 34 56', '97-12-34-56'] as $n) {
            $this->assertFalse(Validator::make(['p' => $n], ['p' => new PhoneNumber()])->fails(), "refusé à tort : $n");
        }
    }

    public function test_les_valeurs_invalides_sont_refusees(): void
    {
        foreach (['', 'abc', '1234567', '+229 12', '97123456<script>', '1234567890123456'] as $n) {
            $this->assertTrue(Validator::make(['p' => $n], ['p' => new PhoneNumber()])->fails(), "accepté à tort : $n");
        }
    }
}
