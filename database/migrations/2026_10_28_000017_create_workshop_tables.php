<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('interventions', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->foreignId('equipment_id')->nullable()->constrained('equipments')->nullOnDelete(); // « equipment » est invariable pour Laravel : on nomme la table
            $table->foreignId('service_request_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('technician_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->string('status')->default('planned');   // planned | in_progress | done | cancelled
            $table->dateTime('scheduled_at')->nullable();
            $table->dateTime('finished_at')->nullable();
            $table->text('diagnosis')->nullable();
            $table->text('work_done')->nullable();
            $table->timestamps();
        });

        Schema::create('intervention_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('intervention_id')->constrained()->cascadeOnDelete();
            $table->string('kind');                           // labor | part
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('description');
            $table->unsignedInteger('quantity');
            $table->unsignedBigInteger('unit_price_fcfa');    // TTC
            $table->string('tax_group', 2)->default('B');
            $table->timestamps();
        });

        // Registre des fluides frigorigènes : ne se modifie ni ne se supprime jamais. Une erreur se corrige
        // par une écriture inverse. Quantités en GRAMMES (entiers) : pas de décimales, pas d'arrondis.
        Schema::create('fluid_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_id')->constrained('equipments')->restrictOnDelete();
            $table->foreignId('intervention_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('technician_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reverses_id')->nullable()->constrained('fluid_movements')->restrictOnDelete();
            $table->string('operation');                      // initial | charge | recovery | correction
            $table->string('fluid');
            $table->integer('quantity_g');                    // signé : + dans l'équipement, − retiré
            $table->dateTime('occurred_at');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->foreignId('intervention_id')->nullable()->after('order_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropConstrainedForeignId('intervention_id');
        });
        Schema::dropIfExists('fluid_movements');
        Schema::dropIfExists('intervention_lines');
        Schema::dropIfExists('interventions');
    }
};
