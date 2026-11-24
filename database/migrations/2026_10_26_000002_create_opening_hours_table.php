<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('opening_hours', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('day_of_week')->unique(); // 1 = lundi ... 7 = dimanche
            $table->time('opens_at')->nullable();
            $table->time('closes_at')->nullable();
            $table->boolean('is_closed')->default(false);
            $table->string('note')->nullable(); // ex. « urgences uniquement »
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opening_hours');
    }
};
