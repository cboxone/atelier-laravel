<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('service_requests', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();             // ex. DI-20261026-0001
            $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete();
            $table->string('equipment');                       // équipement concerné
            $table->text('problem');
            $table->string('district');                        // quartier
            $table->string('phone');                           // téléphone ou WhatsApp
            $table->date('preferred_date')->nullable();
            $table->boolean('is_urgent')->default(false);
            $table->string('status')->default('new');          // new | contacted | done | cancelled
            $table->text('internal_note')->nullable();
            $table->timestamps();

            $table->index(['status', 'is_urgent', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_requests');
    }
};
