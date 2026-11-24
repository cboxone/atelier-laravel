<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('document_sequences', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedSmallInteger('year');
            $table->unsignedInteger('last_number')->default(0);
            $table->timestamps();
            $table->unique(['name', 'year']);
        });

        Schema::create('quotes', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->foreignId('intervention_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->default('draft');       // draft | sent | accepted | refused | invoiced
            $table->date('valid_until')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('quote_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_id')->constrained()->cascadeOnDelete();
            $table->string('description');
            $table->unsignedInteger('quantity');
            $table->unsignedBigInteger('unit_price_fcfa');    // TTC
            $table->string('tax_group', 2)->default('B');
            $table->timestamps();
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('number')->nullable()->unique();   // attribué à l'émission, sans trou
            $table->string('type', 2)->default('FV');         // FV facture de vente | FA facture d'avoir
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->foreignId('quote_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('intervention_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('original_invoice_id')->nullable()->constrained('invoices')->restrictOnDelete();
            $table->string('status')->default('draft');       // draft | pending | issued | failed
            $table->boolean('normalized')->default(false);    // validée par l'e-MECeF de la DGI
            $table->unsignedBigInteger('total_fcfa')->default(0);
            $table->unsignedBigInteger('tax_fcfa')->default(0);
            $table->dateTime('issued_at')->nullable();
            $table->string('emecef_uid')->nullable();
            $table->string('emecef_code')->nullable();        // code MECeF/DGI
            $table->string('emecef_nim')->nullable();
            $table->string('emecef_counters')->nullable();
            $table->dateTime('emecef_datetime')->nullable();
            $table->text('emecef_qr')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();
        });

        Schema::create('invoice_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->string('description');
            $table->unsignedInteger('quantity');
            $table->unsignedBigInteger('unit_price_fcfa');    // TTC
            $table->string('tax_group', 2)->default('B');
            $table->timestamps();
        });

        Schema::create('invoice_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->string('method');                         // cash | mobile_money | transfer | card | cheque | other
            $table->unsignedBigInteger('amount_fcfa');
            $table->string('reference')->nullable();
            $table->dateTime('paid_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['invoice_payments', 'invoice_lines', 'invoices', 'quote_lines', 'quotes', 'document_sequences'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
