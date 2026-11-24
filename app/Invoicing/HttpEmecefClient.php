<?php

namespace App\Invoicing;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

// Client HTTP e-MECeF. Chemins et champs de réponse issus de bibliothèques tierces : à valider contre la documentation DGI.
class HttpEmecefClient implements EmecefClient
{
    public function create(array $payload): array
    {
        return $this->parse($this->http()->post(config('emecef.endpoints.create'), $payload));
    }

    public function finalize(string $uid, string $action): array
    {
        if (! in_array($action, ['confirm', 'cancel'], true)) throw new InvoiceException('Action e-MECeF invalide.');
        $path = str_replace(['{uid}', '{action}'], [rawurlencode($uid), $action], config('emecef.endpoints.finalize'));
        return $this->parse($this->http()->put($path));
    }

    private function http()
    {
        if (! config('emecef.base_url') || ! config('emecef.token')) {
            throw new InvoiceException('e-MECeF non configuré : renseignez EMECEF_BASE_URL et EMECEF_TOKEN.');
        }
        return Http::withToken(config('emecef.token'))->acceptJson()->asJson()
            ->timeout((int) config('emecef.timeout', 30))->baseUrl(rtrim(config('emecef.base_url'), '/'));
    }

    private function parse(Response $r): array
    {
        $json = $r->json() ?? [];
        if ($r->failed() || isset($json['errorCode'])) {
            $msg = $json['errorDesc'] ?? $json['message'] ?? mb_substr($r->body(), 0, 300);
            throw new InvoiceException('e-MECeF a refusé la demande (HTTP ' . $r->status() . ') : ' . $msg);
        }
        return $json;
    }
}
