<?php

use App\Http\Controllers\{AdminAttachmentController, CartController, CheckoutController, FedaPayWebhookController, OrderTrackingController, PageController, ServiceRequestController};
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/services', [PageController::class, 'services'])->name('services');
Route::get('/produits', [PageController::class, 'products'])->name('products');
Route::get('/contrats', [PageController::class, 'contracts'])->name('contracts');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::get('/mentions-legales', [PageController::class, 'legal'])->name('legal');
Route::get('/confidentialite', [PageController::class, 'privacy'])->name('privacy');
Route::get('/sitemap.xml', [PageController::class, 'sitemap']);

Route::get('/demande', [ServiceRequestController::class, 'create'])->name('requests.create');
Route::post('/demande', [ServiceRequestController::class, 'store'])->middleware('throttle:5,1')->name('requests.store');
Route::get('/demande/merci', [ServiceRequestController::class, 'thanks'])->name('requests.thanks');

Route::get('/admin/pieces-jointes/{attachment}', [AdminAttachmentController::class, 'show'])
    ->whereNumber('attachment')
    ->name('admin.attachments.show');

// Étape 2 : boutique
Route::get('/panier', [CartController::class, 'show'])->name('cart');
Route::post('/panier/ajouter/{product}', [CartController::class, 'add'])->name('cart.add');
Route::patch('/panier/{product}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/panier/{product}', [CartController::class, 'remove'])->name('cart.remove');
Route::get('/commande', [CheckoutController::class, 'create'])->name('checkout');
Route::post('/commande', [CheckoutController::class, 'store'])->middleware('throttle:10,1')->name('checkout.store');
Route::get('/commande/merci', [CheckoutController::class, 'thanks'])->name('orders.thanks');
Route::get('/commande/suivi', [OrderTrackingController::class, 'form'])->name('orders.track');
Route::post('/commande/suivi', [OrderTrackingController::class, 'lookup'])->middleware('throttle:10,1')->name('orders.lookup');
Route::get('/commande/paiement/retour', [CheckoutController::class, 'paymentReturn'])->name('orders.payment.return');
Route::post('/webhooks/fedapay', FedaPayWebhookController::class)->name('webhooks.fedapay');
// Dans bootstrap/app.php : ->withMiddleware(fn ($m) => $m->validateCsrfTokens(except: ['webhooks/*']))

// Étape 3 : espace atelier (réservé à l'administrateur)
// Dans bootstrap/app.php : ->withMiddleware(fn ($m) => $m->alias(['staff' => \App\Http\Middleware\EnsureStaff::class]))
Route::middleware('staff')->prefix('atelier')->name('atelier.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Atelier\DashboardController::class, 'index'])->name('dashboard');

    Route::get('/devis', [\App\Http\Controllers\Atelier\QuoteController::class, 'index'])->name('quotes.index');
    Route::get('/devis/nouveau', [\App\Http\Controllers\Atelier\QuoteController::class, 'create'])->name('quotes.create');
    Route::post('/devis', [\App\Http\Controllers\Atelier\QuoteController::class, 'store'])->name('quotes.store');
    Route::get('/devis/{quote}', [\App\Http\Controllers\Atelier\QuoteController::class, 'show'])->name('quotes.show');
    Route::post('/devis/{quote}/statut', [\App\Http\Controllers\Atelier\QuoteController::class, 'status'])->name('quotes.status');
    Route::post('/devis/{quote}/facturer', [\App\Http\Controllers\Atelier\QuoteController::class, 'invoice'])->name('quotes.invoice');

    Route::get('/factures', [\App\Http\Controllers\Atelier\InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/factures/{invoice}', [\App\Http\Controllers\Atelier\InvoiceController::class, 'show'])->name('invoices.show');
    Route::post('/factures/{invoice}/emettre', [\App\Http\Controllers\Atelier\InvoiceController::class, 'issue'])->name('invoices.issue');
    Route::post('/factures/{invoice}/paiements', [\App\Http\Controllers\Atelier\InvoiceController::class, 'pay'])->name('invoices.pay');
    Route::post('/factures/{invoice}/avoir', [\App\Http\Controllers\Atelier\InvoiceController::class, 'credit'])->name('invoices.credit');

    Route::get('/interventions', [\App\Http\Controllers\Atelier\InterventionController::class, 'index'])->name('interventions.index');
    Route::get('/interventions/nouvelle', [\App\Http\Controllers\Atelier\InterventionController::class, 'create'])->name('interventions.create');
    Route::post('/interventions', [\App\Http\Controllers\Atelier\InterventionController::class, 'store'])->name('interventions.store');
    Route::get('/interventions/{intervention}', [\App\Http\Controllers\Atelier\InterventionController::class, 'show'])->name('interventions.show');
    Route::post('/interventions/{intervention}/lignes', [\App\Http\Controllers\Atelier\InterventionController::class, 'addLine'])->name('interventions.lines');
    Route::post('/interventions/{intervention}/fluide', [\App\Http\Controllers\Atelier\InterventionController::class, 'addFluid'])->name('interventions.fluid');
    Route::post('/interventions/{intervention}/terminer', [\App\Http\Controllers\Atelier\InterventionController::class, 'finish'])->name('interventions.finish');
    Route::post('/interventions/{intervention}/facturer', [\App\Http\Controllers\Atelier\InterventionController::class, 'invoice'])->name('interventions.invoice');

    Route::get('/registre-fluides/{equipment}', [\App\Http\Controllers\Atelier\FluidRegisterController::class, 'show'])->name('fluid.show');
    Route::post('/registre-fluides/ecritures/{movement}/corriger', [\App\Http\Controllers\Atelier\FluidRegisterController::class, 'reverse'])->name('fluid.reverse');

    Route::get('/contrats', [\App\Http\Controllers\Atelier\ContractController::class, 'index'])->name('contracts.index');
    Route::get('/contrats/nouveau', [\App\Http\Controllers\Atelier\ContractController::class, 'create'])->name('contracts.create');
    Route::post('/contrats', [\App\Http\Controllers\Atelier\ContractController::class, 'store'])->name('contracts.store');
    Route::get('/contrats/{contract}', [\App\Http\Controllers\Atelier\ContractController::class, 'show'])->name('contracts.show');
    Route::post('/contrats/{contract}/interventions', [\App\Http\Controllers\Atelier\ContractController::class, 'generate'])->name('contracts.generate');
    Route::post('/contrats/{contract}/facturer', [\App\Http\Controllers\Atelier\ContractController::class, 'invoice'])->name('contracts.invoice');
    Route::post('/contrats/{contract}/annuler', [\App\Http\Controllers\Atelier\ContractController::class, 'cancel'])->name('contracts.cancel');
});

// Application technicien (mobile). Dans bootstrap/app.php : alias 'technician' => \App\Http\Middleware\EnsureTechnician::class
use App\Http\Controllers\Technician\{AuthController as TechAuth, InterventionController as TechIntervention};
Route::prefix('technicien')->name('tech.')->group(function () {
    Route::get('/connexion', [TechAuth::class, 'show'])->name('login');
    Route::post('/connexion', [TechAuth::class, 'login'])->middleware('throttle:6,1')->name('login.post');
    Route::get('/hors-ligne', fn () => response()->file(public_path('offline.html')))->name('offline');
    Route::get('/sw.js', fn () => response()->file(public_path('sw.js'), ['Content-Type' => 'application/javascript', 'Cache-Control' => 'no-cache']))->name('sw');

    Route::middleware('technician')->group(function () {
        Route::post('/deconnexion', [TechAuth::class, 'logout'])->name('logout');
        Route::get('/', [TechIntervention::class, 'index'])->name('index');
        Route::get('/interventions/{intervention}', [TechIntervention::class, 'show'])->name('show');
        Route::post('/interventions/{intervention}/demarrer', [TechIntervention::class, 'start'])->name('start');
        Route::post('/interventions/{intervention}/lignes', [TechIntervention::class, 'addLine'])->name('lines');
        Route::post('/interventions/{intervention}/fluide', [TechIntervention::class, 'addFluid'])->name('fluid');
        Route::post('/interventions/{intervention}/photos', [TechIntervention::class, 'addPhoto'])->name('photos');
        Route::post('/interventions/{intervention}/signature', [TechIntervention::class, 'sign'])->name('sign');
        Route::get('/interventions/{intervention}/signature', [TechIntervention::class, 'signature'])->name('signature');
        Route::post('/interventions/{intervention}/terminer', [TechIntervention::class, 'finish'])->name('finish');
        Route::get('/photos/{photo}', [TechIntervention::class, 'photo'])->name('photo');
    });
});
