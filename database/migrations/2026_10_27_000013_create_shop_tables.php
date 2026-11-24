<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->string('customer_name');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->string('district');                         // nom de la zone de livraison
            $table->text('address');
            $table->text('delivery_notes')->nullable();
            $table->unsignedBigInteger('subtotal_fcfa');
            $table->unsignedBigInteger('delivery_fee_fcfa')->default(0);
            $table->unsignedBigInteger('total_fcfa');
            $table->string('payment_method');                   // code de payment_methods (cash_on_delivery, online, bank_transfer…)
            $table->string('payment_status')->default('pending'); // pending | paid | failed | refunded
            $table->string('status')->default('new');          // new | confirmed | out_for_delivery | delivered | cancelled
            $table->boolean('want_installation')->default(false);
            $table->timestamp('stock_restored_at')->nullable(); // évite de remettre le stock deux fois
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');                              // copie : le prix et le nom au moment de la commande
            $table->unsignedBigInteger('unit_price_fcfa');
            $table->unsignedInteger('quantity');
            $table->unsignedBigInteger('line_total_fcfa');
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('provider');                          // code de payment_methods (cash_on_delivery, online, bank_transfer…)
            $table->string('provider_reference')->nullable();    // identifiant de transaction du prestataire
            $table->unsignedBigInteger('amount_fcfa');
            $table->string('status')->default('pending');        // pending | paid | failed
            $table->json('raw_payload')->nullable();
            $table->timestamps();

            $table->unique(['provider', 'provider_reference']);  // garantit l'idempotence des webhooks
        });

        // Journal du stock : chaque entrée ou sortie est une ligne, on ne modifie jamais l'historique.
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('quantity');                         // négatif = sortie
            $table->string('reason');                            // order | order_cancelled | adjustment
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
