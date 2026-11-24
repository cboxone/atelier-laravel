<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // L'atelier active ou désactive chaque moyen de paiement depuis l'administration.
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();                    // identifiant interne, ne pas changer
            $table->string('label');                             // texte montré au client
            $table->text('instructions')->nullable();            // consignes ; {reference} et {total} sont remplacés
            $table->boolean('is_enabled')->default(false);
            $table->unsignedBigInteger('max_amount_fcfa')->nullable(); // plafond par commande, NULL = aucun
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
