<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Catalogue d'AFFICHAGE (étape 1). Le panier et les commandes viendront à l'étape 2.
return new class extends Migration {
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_category_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            // Montant en FCFA, entier (pas de décimales). NULL = prix sur devis.
            $table->unsignedBigInteger('price_fcfa')->nullable();
            $table->string('availability')->default('in_stock'); // in_stock | on_order | unavailable
            $table->string('image_path')->nullable();
            $table->boolean('is_published')->default(true);     // masquer sans supprimer
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_published', 'product_category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
