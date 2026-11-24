<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderTrackingController extends Controller
{
    public function form()
    {
        return view('shop.track');
    }

    // Référence ET téléphone obligatoires, même message d'erreur dans tous les cas : pas d'énumération possible.
    public function lookup(Request $request)
    {
        $d = $request->validate(['reference' => ['required', 'string', 'max:40'], 'phone' => ['required', 'string', 'max:30']]);
        $order = Order::where('reference', strtoupper(trim($d['reference'])))->first();

        if (! $order || ! Order::samePhone($order->phone, $d['phone'])) {
            return back()->withErrors(['reference' => 'Aucune commande ne correspond à ces informations.'])->withInput();
        }

        return view('shop.track', ['order' => $order->load('items')]);
    }
}
