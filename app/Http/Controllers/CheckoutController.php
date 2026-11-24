<?php

namespace App\Http\Controllers;

use App\Exceptions\ShopException;
use App\Models\{Order, OrderItem, Payment, PaymentMethod, Product, ServiceZone, Setting, StockMovement};
use App\Notifications\NewOrder;
use App\Rules\PhoneNumber;
use App\Services\Cart;
use App\Support\Money;
use Illuminate\Http\Request;
use App\Payments\FedaPayReconciler;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\{DB, Log, Notification};
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CheckoutController extends Controller
{
    public function create(Cart $cart)
    {
        $lines = $cart->lines();
        if ($lines->isEmpty()) return redirect()->route('cart');

        return view('shop.checkout', [
            'lines' => $lines,
            'subtotal' => (int) $lines->sum('total'),
            'zones' => $this->zones(),
            'methods' => $this->methods(),
            'checkoutToken' => (string) Str::uuid(), // un jeton par affichage du formulaire
        ]);
    }

    public function store(Request $request, Cart $cart)
    {
        $data = $request->validate([
            'customer_name'  => ['required', 'string', 'max:120'],
            'phone'          => ['required', 'string', new PhoneNumber()],
            'email'          => ['nullable', 'email', 'max:255'],
            'zone_id'        => ['required', Rule::exists('service_zones', 'id')->where(fn ($q) => $q->where('is_active', true)->whereNotNull('delivery_fee_fcfa'))],
            'address'        => ['required', 'string', 'max:500'],
            'delivery_notes' => ['nullable', 'string', 'max:500'],
            'payment_method' => ['required', Rule::in($this->methods()->pluck('code')->all())],
            'want_installation' => ['boolean'],
            'checkout_token' => ['required', 'uuid'],
            'expected_subtotal' => ['nullable', 'integer'],
            'consent'        => ['accepted'],
            'website'        => ['prohibited'], // champ piège anti-robot
        ], [
            'zone_id.required' => 'Choisissez votre zone de livraison.',
            'consent.accepted' => 'Vous devez accepter l’utilisation de vos informations pour traiter la commande.',
        ]);

        // Même formulaire envoyé deux fois (double clic, retour arrière) : on renvoie vers la commande déjà créée.
        if ($existing = Order::where('checkout_token', $data['checkout_token'])->first()) {
            $cart->clear();
            return redirect()->route('orders.thanks')->with('reference', $existing->reference);
        }

        $lines = $cart->lines();
        if ($lines->isEmpty()) return redirect()->route('cart');

        try {
            $order = DB::transaction(fn () => $this->placeOrder($data, $lines->mapWithKeys(fn ($l) => [$l['product']->id => $l['qty']])));
        } catch (ShopException|UniqueConstraintViolationException $e) {
            // Deux envois simultanés : l'un a gagné, l'autre voit le stock baissé ou le jeton déjà pris. Pas une erreur pour le client.
            if ($existing = Order::where('checkout_token', $data['checkout_token'])->first()) {
                $cart->clear();
                return redirect()->route('orders.thanks')->with('reference', $existing->reference);
            }
            if ($e instanceof UniqueConstraintViolationException) throw $e;
            return redirect()->route('cart')->withErrors(['cart' => $e->getMessage()]);
        }

        $cart->clear();
        $this->notifyManager($order);

        $redirect = redirect()->route('orders.thanks')->with(['reference' => $order->reference]);

        // Moyen manuel : rien à faire en ligne. Moyen en ligne : on envoie le client payer chez le prestataire.
        $method = PaymentMethod::where('code', $order->payment_method)->first();
        $payment = $order->payments()->first();
        try {
            if ($url = $method?->gateway()->initiate($order, $payment)) {
                return redirect()->away($url);
            }
        } catch (\Throwable $e) {
            report($e);
            $payment->update(['status' => 'failed']);
            $order->update(['payment_status' => 'failed']);
            return $redirect->with('warning', 'Le paiement en ligne n’a pas pu démarrer. Nous vous appelons pour finaliser, ou choisissez un autre moyen de paiement en nous contactant.');
        }

        return $redirect;
    }

    // Retour du client depuis la page de paiement. Ce retour ne prouve RIEN : seul le rappel serveur (webhook) confirme.
    public function paymentReturn(Request $request, FedaPayReconciler $reconciler)
    {
        // L'adresse de retour contient l'identifiant de la transaction, mais on ne croit PAS son contenu :
        // on s'en sert uniquement pour retrouver NOTRE paiement, puis FedaPay est interrogé directement.
        $id = (string) $request->query('id', '');
        if (ctype_digit($id) && $reconciler->canReconcile()) {
            $payment = Payment::where('provider', 'online')->where('provider_reference', $id)->first();
            if ($payment) {
                try {
                    $reconciler->reconcile($payment);
                } catch (\Throwable $e) {
                    report($e);
                }
            }
        }

        return redirect()->route('orders.track')->with('status', 'Merci. Dès que votre paiement est confirmé, votre commande est validée. Vous pouvez la suivre ici avec votre numéro de commande et votre téléphone.');
    }

    public function thanks()
    {
        if (! session()->has('reference')) return redirect()->route('cart');
        $order = Order::where('reference', session('reference'))->first();
        if (! $order) return redirect()->route('cart');
        $method = PaymentMethod::where('code', $order->payment_method)->first();

        return view('shop.thanks', [
            'order' => $order,
            'method' => $method,
            'instructions' => $method?->instructionsFor($order),
            'warning' => session('warning'),
        ]);
    }

    // Tout est recalculé et vérifié ICI, sous verrou : prix, stock, frais de livraison. Rien ne vient du navigateur.
    private function placeOrder(array $data, $wanted): Order
    {
        $products = Product::whereIn('id', $wanted->keys())->lockForUpdate()->get()->keyBy('id');
        $subtotal = 0;
        $rows = [];
        foreach ($wanted as $id => $qty) {
            $p = $products[$id] ?? null;
            if (! $p || ! $p->isPurchasable()) {
                throw new ShopException('Un produit de votre panier n’est plus disponible.');
            }
            if ($p->stock_quantity !== null && $p->stock_quantity < $qty) {
                throw new ShopException("Stock insuffisant pour « {$p->name} » (reste {$p->stock_quantity}).");
            }
            $rows[] = [$p, $qty];
            $subtotal += $p->price_fcfa * $qty;
        }

        // Le prix a pu changer entre l'affichage du récapitulatif et l'envoi : on ne facture jamais un total que le client n'a pas vu.
        if (isset($data['expected_subtotal']) && (int) $data['expected_subtotal'] !== $subtotal) {
            throw new ShopException('Les prix de votre panier ont changé. Vérifiez le nouveau total avant de commander.');
        }

        $zone = ServiceZone::where('is_active', true)->whereNotNull('delivery_fee_fcfa')->findOrFail($data['zone_id']);
        $fee = (int) $zone->delivery_fee_fcfa;

        $limit = PaymentMethod::where('code', $data['payment_method'])->value('max_amount_fcfa');
        if ($limit !== null && $subtotal + $fee > $limit) {
            throw new ShopException('Ce moyen de paiement n’accepte pas ce montant. Choisissez-en un autre.');
        }

        $order = Order::create([
            'checkout_token' => $data['checkout_token'],
            'customer_name' => $data['customer_name'], 'phone' => $data['phone'], 'email' => $data['email'] ?? null,
            'district' => $zone->name, 'address' => $data['address'], 'delivery_notes' => $data['delivery_notes'] ?? null,
            'subtotal_fcfa' => $subtotal, 'delivery_fee_fcfa' => $fee, 'total_fcfa' => $subtotal + $fee,
            'payment_method' => $data['payment_method'], 'want_installation' => (bool) ($data['want_installation'] ?? false),
        ]);

        foreach ($rows as [$p, $qty]) {
            OrderItem::create([
                'order_id' => $order->id, 'product_id' => $p->id, 'name' => $p->name,
                'unit_price_fcfa' => $p->price_fcfa, 'quantity' => $qty, 'line_total_fcfa' => $p->price_fcfa * $qty,
            ]);
            if ($p->stock_quantity !== null) {
                $p->adjustStock(-$qty, 'order', $order->id);
            }
        }

        Payment::create(['order_id' => $order->id, 'provider' => $data['payment_method'], 'amount_fcfa' => $order->total_fcfa]);

        return $order;
    }

    private function zones()
    {
        return ServiceZone::where('is_active', true)->whereNotNull('delivery_fee_fcfa')->orderBy('sort_order')->get();
    }

    private function methods()
    {
        return PaymentMethod::available();
    }

    // Une panne d'e-mail ne doit jamais faire perdre la commande : elle est déjà enregistrée.
    private function notifyManager(Order $order): void
    {
        $to = Setting::get('notify_email');
        if (! $to) {
            Log::warning('Réglage notify_email absent : le gérant n’est pas prévenu des nouvelles commandes.');
            return;
        }
        try {
            Notification::route('mail', $to)->notify(new NewOrder($order));
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
