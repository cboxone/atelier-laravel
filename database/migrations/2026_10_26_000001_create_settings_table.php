<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Réglages de l'atelier (nom, téléphone, WhatsApp, adresse, textes d'accueil...).
// Rien de ceci n'est écrit en dur dans le code : cela prépare la revente à d'autres ateliers.
return new class extends Migration {
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();   // ex. business_name, phone, whatsapp, address
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
