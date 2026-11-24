<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('maintenance_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->foreignId('contract_plan_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->date('starts_on');
            $table->date('ends_on');
            $table->unsignedTinyInteger('visits_per_year');
            $table->unsignedBigInteger('price_fcfa');          // TTC, pour toute la durée
            $table->string('status')->default('active');       // active | ended | cancelled
            $table->text('notes')->nullable();
            $table->dateTime('invoiced_at')->nullable();       // évite de facturer deux fois
            $table->timestamps();
        });

        Schema::create('contract_equipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('maintenance_contract_id')->constrained()->cascadeOnDelete();
            $table->foreignId('equipment_id')->constrained('equipments')->cascadeOnDelete();
            $table->unique(['maintenance_contract_id', 'equipment_id']);
        });

        Schema::create('contract_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('maintenance_contract_id')->constrained()->cascadeOnDelete();
            $table->date('due_on');
            $table->string('status')->default('planned');      // planned | done | skipped
            $table->dateTime('interventions_created_at')->nullable(); // évite de créer deux fois les interventions
            $table->timestamps();
        });

        Schema::table('interventions', function (Blueprint $table) {
            $table->foreignId('contract_visit_id')->nullable()->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('interventions', fn (Blueprint $t) => $t->dropConstrainedForeignId('contract_visit_id'));
        Schema::dropIfExists('contract_visits');
        Schema::dropIfExists('contract_equipments');
        Schema::dropIfExists('maintenance_contracts');
    }
};
