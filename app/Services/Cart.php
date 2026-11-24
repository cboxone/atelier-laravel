<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;

// Panier en session : on ne garde que {id produit => quantité}. Les prix sont TOUJOURS relus en base.
class Cart
{
    public const MAX_QTY = 20;

    public function raw(): array
    {
        return session('cart', []);
    }

    public function count(): int
    {
        return array_sum($this->raw());
    }

    public function add(Product $p, int $qty = 1): void
    {
        $cart = $this->raw();
        $cart[$p->id] = $this->clamp($p, ($cart[$p->id] ?? 0) + $qty);
        session(['cart' => $cart]);
    }

    public function set(Product $p, int $qty): void
    {
        $cart = $this->raw();
        if ($qty <= 0) {
            unset($cart[$p->id]);
        } else {
            $cart[$p->id] = $this->clamp($p, $qty);
        }
        session(['cart' => $cart]);
    }

    public function remove(int $id): void
    {
        $cart = $this->raw();
        unset($cart[$id]);
        session(['cart' => $cart]);
    }

    public function clear(): void
    {
        session()->forget('cart');
    }

    // Lignes valides uniquement : un produit masqué, sans prix ou épuisé disparaît du panier.
    public function lines(): Collection
    {
        $raw = $this->raw();
        if (! $raw) return collect();

        $products = Product::whereIn('id', array_keys($raw))->get()->keyBy('id');
        $valid = [];
        $lines = collect();
        foreach ($raw as $id => $qty) {
            $p = $products[$id] ?? null;
            if (! $p || ! $p->isPurchasable()) continue;
            $q = $this->clamp($p, (int) $qty);
            $valid[$id] = $q;
            $lines->push(['product' => $p, 'qty' => $q, 'total' => $p->price_fcfa * $q]);
        }
        session(['cart' => $valid]);

        return $lines;
    }

    public function subtotal(): int
    {
        return (int) $this->lines()->sum('total');
    }

    private function clamp(Product $p, int $qty): int
    {
        $max = $p->stock_quantity === null ? self::MAX_QTY : min(self::MAX_QTY, $p->stock_quantity);
        return max(1, min($qty, max(1, $max)));
    }
}
