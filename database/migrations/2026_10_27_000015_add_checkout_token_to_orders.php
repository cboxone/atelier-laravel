<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Jeton unique par formulaire de commande : la base de données elle-même empêche
            // deux commandes identiques (double clic, deux onglets, requêtes simultanées).
            $table->string('checkout_token', 64)->nullable()->unique()->after('reference');
        });
    }

    public function down(): void
    {
        Schema::table('orders', fn (Blueprint $t) => $t->dropColumn('checkout_token'));
    }
};
