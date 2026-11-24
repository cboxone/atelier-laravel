<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

// Accepte les numéros écrits librement (« +229 01 97 12 34 56 », « 0197123456 », « 97-12-34-56 »)
// et vérifie le nombre de chiffres, pas la longueur du texte.
class PhoneNumber implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $value = (string) $value;
        $digits = strlen(preg_replace('/\D+/', '', $value));

        if (! preg_match('/^\+?[0-9 .\-]+$/', $value) || $digits < 8 || $digits > 15) {
            $fail('Le numéro de téléphone n’est pas valide.');
        }
    }
}
