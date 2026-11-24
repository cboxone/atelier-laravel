<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Order extends Model
{
    protected $fillable = [
        'reference', 'customer_name', 'phone', 'email', 'district', 'address', 'delivery_notes',
        'subtotal_fcfa', 'delivery_fee_fcfa', 'total_fcfa', 'payment_method', 'payment_status',
        'status', 'want_installation', 'stock_restored_at', 'checkout_token',
    ];
    protected $casts = ['want_installation' => 'boolean', 'stock_restored_at' => 'datetime'];

    public const STATUSES = [
        'new' => 'Reçue', 'confirmed' => 'Confirmée', 'out_for_delivery' => 'En livraison',
        'delivered' => 'Livrée', 'cancelled' => 'Annulée',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $o) {
            if (! $o->reference) {
                do {
                    $ref = 'CMD-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6));
                } while (static::where('reference', $ref)->exists());
                $o->reference = $ref;
            }
        });

        // Quelle que soit la façon dont le statut passe à « annulée » (administration, code), le stock revient.
        static::saved(function (self $o) {
            if ($o->wasChanged('status') && $o->status === 'cancelled') {
                $o->restoreStock();
            }
        });
    }

    public function items() { return $this->hasMany(OrderItem::class); }
    public function payments() { return $this->hasMany(Payment::class); }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    // Compare deux numéros en ignorant espaces, « + » et indicatif : on garde les 8 derniers chiffres.
    public static function samePhone(string $a, string $b): bool
    {
        $n = fn (string $p) => substr(preg_replace('/\D+/', '', $p), -8);
        return $n($a) !== '' && $n($a) === $n($b);
    }

    public function restoreStock(): void
    {
        DB::transaction(function () {
            $order = static::whereKey($this->id)->lockForUpdate()->first();
            if (! $order || $order->stock_restored_at) {
                return; // déjà remis en stock : jamais deux fois
            }
            foreach ($order->items()->whereNotNull('product_id')->get() as $item) {
                $product = Product::lockForUpdate()->find($item->product_id);
                if ($product && $product->stock_quantity !== null) {
                    $product->adjustStock($item->quantity, 'order_cancelled', $order->id);
                }
            }
            $order->forceFill(['stock_restored_at' => now()])->saveQuietly();
        });
    }
}
