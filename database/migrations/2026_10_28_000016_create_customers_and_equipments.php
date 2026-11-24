<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('ifu')->nullable();        // IFU du client (factures aux entreprises)
            $table->timestamps();
        });

        Schema::create('equipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('label');                   // ex. « Split salon »
            $table->string('type')->nullable();        // climatiseur, chambre froide, vitrine...
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('fluid_type')->nullable();  // ex. R410A
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipments');
        Schema::dropIfExists('customers');
    }
};
