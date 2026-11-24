<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['product_category_id', 'name', 'slug', 'description', 'price_fcfa', 'availability', 'image_path', 'is_published', 'sort_order', 'stock_quantity'];
    protected $casts = ['is_published' => 'boolean'];

    protected static function booted(): void
    {
        // Tout changement de stock fait « à la main » (administration, code) est écrit dans le journal.
        static::created(function (self $p) {
            if ($p->stock_quantity !== null) {
                StockMovement::create(['product_id' => $p->id, 'quantity' => $p->stock_quantity, 'reason' => 'adjustment']);
            }
        });
        static::updated(function (self $p) {
            if (! $p->wasChanged('stock_quantity') || $p->stock_quantity === null) return;
            StockMovement::create(['product_id' => $p->id, 'quantity' => $p->stock_quantity - (int) ($p->getOriginal('stock_quantity') ?? 0), 'reason' => 'adjustment']);
        });
    }

    // Seul moyen de faire bouger le stock depuis une commande : la quantité ET le journal changent ensemble.
    public function adjustStock(int $delta, string $reason, ?int $orderId = null, ?int $interventionId = null): void
    {
        static::whereKey($this->id)->increment('stock_quantity', $delta); // sans événement : pas de double écriture
        StockMovement::create(['product_id' => $this->id, 'order_id' => $orderId, 'intervention_id' => $interventionId, 'quantity' => $delta, 'reason' => $reason]);
        $this->refresh();
    }

    public function category() { return $this->belongsTo(ProductCategory::class, 'product_category_id'); }

    public function scopePublished($q) { return $q->where('is_published', true); }

    // Vendable en ligne : publié, avec un prix, non indisponible et en stock.
    public function isPurchasable(): bool
    {
        return $this->is_published
            && $this->price_fcfa !== null
            && $this->availability !== 'unavailable'
            && ($this->stock_quantity === null || $this->stock_quantity > 0);
    }

    public function getPriceLabelAttribute(): string
    {
        return $this->price_fcfa === null ? 'Sur devis' : number_format($this->price_fcfa, 0, ',', ' ') . ' FCFA';
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path ? asset('storage/' . $this->image_path) : null; // disque public (php artisan storage:link)
    }

    public function getAvailabilityLabelAttribute(): string
    {
        return ['in_stock' => 'En stock', 'on_order' => 'Sur commande', 'unavailable' => 'Indisponible'][$this->availability] ?? '';
    }
}
