<?php

namespace Tests\Support;

use App\Invoicing\EmecefClient;

// « Fausse DGI » pour les tests : on choisit ce qu'elle répond et on garde la trace des appels.
class FakeEmecefClient implements EmecefClient
{
    public array $calls = [];
    public ?array $createResponse = null;
    public ?array $finalizeResponse = null;
    public ?\Throwable $failWith = null;

    public function create(array $payload): array
    {
        $this->calls[] = ['create', $payload];
        if ($this->failWith) throw $this->failWith;
        return $this->createResponse ?? ['uid' => 'uid-1', 'total' => array_sum(array_map(fn ($i) => $i['price'] * $i['quantity'], $payload['items']))];
    }

    public function finalize(string $uid, string $action): array
    {
        $this->calls[] = ['finalize', $uid, $action];
        if ($action === 'cancel') return [];
        return $this->finalizeResponse ?? ['codeMECeFDGI' => 'TEST-AAAA-BBBB-CCCC', 'qrCode' => 'F;TEST;demo', 'nim' => 'EM00000001', 'counters' => '1/1 FV', 'dateTime' => '2026-10-28T14:00:00'];
    }

    public function actions(): array
    {
        return array_map(fn ($c) => $c[0] . ($c[2] ?? ''), $this->calls);
    }
}
