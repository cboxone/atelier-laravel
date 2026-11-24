<?php

namespace App\Invoicing;

// Construit le message JSON envoyé à l'e-MECeF à partir de données simples (fonction pure, testable sans Laravel).
// Format d'après des bibliothèques tierces : à comparer avec le manuel officiel de la DGI avant usage réel.
class EmecefPayload
{
    /** @param array{ifu:string,type:string,lines:array,customer:array,operator:string,payments:array,reference?:?string} $d
     *  @param array<string,string> $paymentNames moyen de paiement interne => nom attendu par l'API */
    public static function build(array $d, array $paymentNames): array
    {
        if (! in_array($d['type'], ['FV', 'FA'], true)) {
            throw new InvoiceException('Type de facture non géré : ' . $d['type']);
        }
        if (trim((string) $d['ifu']) === '') {
            throw new InvoiceException('L’IFU de l’atelier n’est pas renseigné (EMECEF_IFU).');
        }

        $items = [];
        foreach ($d['lines'] as $l) {
            $name = trim((string) $l['description']);
            if ($name === '') throw new InvoiceException('Une ligne n’a pas de désignation.');
            $items[] = ['name' => $name, 'price' => (int) $l['unit_price_fcfa'], 'quantity' => (int) $l['quantity'], 'taxGroup' => (string) $l['tax_group']];
        }
        if (! $items) throw new InvoiceException('La facture est vide.');

        $payments = [];
        foreach ($d['payments'] as $p) {
            $name = $paymentNames[$p['method']] ?? null;
            if (! $name) throw new InvoiceException('Moyen de paiement inconnu : ' . $p['method']);
            $payments[] = ['name' => $name, 'amount' => (int) $p['amount']];
        }

        $client = array_filter([
            'name' => $d['customer']['name'] ?? null, 'contact' => $d['customer']['phone'] ?? null,
            'ifu' => $d['customer']['ifu'] ?? null, 'address' => $d['customer']['address'] ?? null,
        ], fn ($v) => $v !== null && $v !== '');

        $payload = ['ifu' => $d['ifu'], 'type' => $d['type'], 'items' => $items, 'client' => $client,
            'operator' => ['name' => $d['operator']], 'payment' => $payments];

        if ($d['type'] === 'FA') {
            $ref = str_replace('-', '', (string) ($d['reference'] ?? '')); // code MECeF de la facture d'origine, sans tirets
            if ($ref === '') throw new InvoiceException('Un avoir doit référencer le code MECeF de la facture d’origine.');
            $payload['reference'] = $ref;
        }

        return $payload;
    }
}
