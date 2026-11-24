<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // NULL = stock non suivi. Sinon quantité disponible à la vente.
            $table->unsignedInteger('stock_quantity')->nullable()->after('availability');
        });
        Schema::table('service_zones', function (Blueprint $table) {
            // NULL = livraison non proposée dans cette zone. Montant en FCFA, entier.
            $table->unsignedBigInteger('delivery_fee_fcfa')->nullable()->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('products', fn (Blueprint $t) => $t->dropColumn('stock_quantity'));
        Schema::table('service_zones', fn (Blueprint $t) => $t->dropColumn('delivery_fee_fcfa'));
    }
};
