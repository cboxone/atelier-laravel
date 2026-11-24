<?php

namespace App\Invoicing;

interface EmecefClient
{
    /** Soumet la facture : elle est « en attente » côté DGI. Retourne la réponse (uid, total...). */
    public function create(array $payload): array;

    /** Confirme ($action = 'confirm') ou annule ($action = 'cancel') une facture en attente. */
    public function finalize(string $uid, string $action): array;
}
