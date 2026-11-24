<?php

namespace App\Models;

use App\Payments\{ManualGateway, PaymentGateway};
use App\Support\Money;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class PaymentMethod extends Model
{
    protected $fillable = ['code', 'label', 'instructions', 'is_enabled', 'max_amount_fcfa', 'sort_order'];
    protected $casts = ['is_enabled' => 'boolean'];

    public function gateway(): PaymentGateway
    {
        return app(config('shop.gateways.' . $this->code, ManualGateway::class));
    }

    // Moyens proposés au client : activés ET réellement utilisables.
    public static function available(): Collection
    {
        return static::where('is_enabled', true)->orderBy('sort_order')->get()
            ->filter(fn (self $m) => $m->gateway()->isConfigured())->values();
    }

    public function instructionsFor(Order $order): ?string
    {
        return $this->instructions
            ? str_replace(['{reference}', '{total}'], [$order->reference, Money::fcfa($order->total_fcfa)], $this->instructions)
            : null;
    }
}
