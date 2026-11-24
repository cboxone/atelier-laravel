<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Uniquement de vrais avis, avec l'accord de la personne.
return new class extends Migration {
    public function up(): void
    {
        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->string('author_name');
            $table->text('content');
            $table->boolean('consent_given')->default(false);
            $table->boolean('is_published')->default(false);
            $table->date('given_on')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('testimonials');
    }
};
