<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_technician')->default(false)->after('is_admin'); // jamais modifiable par formulaire
        });

        Schema::table('interventions', function (Blueprint $table) {
            $table->dateTime('started_at')->nullable()->after('scheduled_at');
            $table->string('signature_path')->nullable();      // signature du client, disque privé
            $table->string('signed_by')->nullable();
            $table->dateTime('signed_at')->nullable();
        });

        Schema::create('intervention_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('intervention_id')->constrained()->cascadeOnDelete();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('path');                            // disque privé : jamais d'adresse publique
            $table->string('caption')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('intervention_photos');
        Schema::table('interventions', fn (Blueprint $t) => $t->dropColumn(['started_at', 'signature_path', 'signed_by', 'signed_at']));
        Schema::table('users', fn (Blueprint $t) => $t->dropColumn('is_technician'));
    }
};
