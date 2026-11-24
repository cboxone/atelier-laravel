<?php

namespace App\Invoicing;

// Calcul pur (sans base de données) : les prix sont TTC, la TVA est incluse. Tout en FCFA entiers.
class InvoiceTotals
{
    /** @param iterable<array{quantity:int, unit_price_fcfa:int, tax_group:string}> $lines
     *  @param array<string,int|float> $rates taux en % par groupe de taxe */
    public static function compute(iterable $lines, array $rates): array
    {
        $byGroup = [];
        $total = 0;
        foreach ($lines as $l) {
            $qty = (int) $l['quantity'];
            $price = (int) $l['unit_price_fcfa'];
            if ($qty < 1 || $price < 0) {
                throw new InvoiceException('Quantité ou prix invalide sur une ligne.');
            }
            $group = (string) $l['tax_group'];
            if (! array_key_exists($group, $rates)) {
                throw new InvoiceException("Groupe de taxe « $group » non configuré : la ligne ne peut pas être facturée.");
            }
            $byGroup[$group] = ($byGroup[$group] ?? 0) + $qty * $price;
            $total += $qty * $price;
        }

        $taxes = [];
        foreach ($byGroup as $group => $amount) {
            $rate = $rates[$group];
            $taxes[$group] = (int) round($amount * $rate / (100 + $rate)); // TVA contenue dans un prix TTC
        }

        return ['total' => $total, 'tax' => array_sum($taxes), 'by_group' => $byGroup, 'taxes' => $taxes];
    }
}
