<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\Cart;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function show(Cart $cart)
    {
        $lines = $cart->lines();
        return view('shop.cart', ['lines' => $lines, 'subtotal' => (int) $lines->sum('total')]);
    }

    public function add(Product $product, Cart $cart)
    {
        if (! $product->isPurchasable()) {
            return back()->withErrors(['cart' => 'Ce produit ne peut pas être commandé en ligne : demandez un devis.']);
        }
        $cart->add($product);
        return redirect()->route('cart')->with('status', 'Produit ajouté au panier.');
    }

    public function update(Request $request, Product $product, Cart $cart)
    {
        $qty = (int) $request->validate(['quantity' => ['required', 'integer', 'min:0', 'max:' . Cart::MAX_QTY]])['quantity'];
        $cart->set($product, $qty);
        return redirect()->route('cart');
    }

    public function remove(Product $product, Cart $cart)
    {
        $cart->remove($product->id);
        return redirect()->route('cart');
    }
}
