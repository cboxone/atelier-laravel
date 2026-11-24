<?php

namespace App\Support;

class Mass
{
    // « 0,350 » ou « 1.5 » (kg) → grammes entiers. Aucune décimale n'est stockée : pas d'erreur d'arrondi dans le registre.
    public static function kgToGrams(string $kg): ?int
    {
        $kg = str_replace([' ', ','], ['', '.'], trim($kg));
        if (! preg_match('/^\d{1,4}(\.\d{1,3})?$/', $kg)) return null;
        $g = (int) round(((float) $kg) * 1000);
        return $g > 0 ? $g : null;
    }

    public static function gramsToKg(int $g): string
    {
        return number_format($g / 1000, 3, ',', ' ');
    }
}
