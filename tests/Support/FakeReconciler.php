<?php

namespace Tests\Support;

use App\Payments\FedaPayReconciler;

// Remplace l'appel réel à FedaPay dans les tests : on choisit la réponse de « FedaPay ».
class FakeReconciler extends FedaPayReconciler
{
    public static ?object $transaction = null;

    public static function respond(string $status, int $amount): void
    {
        static::$transaction = (object) ['status' => $status, 'amount' => $amount];
    }

    public function canReconcile(): bool
    {
        return true;
    }

    protected function fetchTransaction(string $id): object
    {
        return static::$transaction ?? throw new \RuntimeException('FedaPay injoignable (simulé)');
    }
}
