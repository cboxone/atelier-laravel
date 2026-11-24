<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->string('icon_key', 32)->nullable();
            $table->string('image_path')->nullable();
        });

        foreach ([
            'depannage-urgent' => 'repair',
            'installation' => 'wrench',
            'entretien-et-contrats' => 'shield',
            'recharge-de-gaz' => 'droplet',
            'chambres-froides' => 'snowflake',
            'reparation-en-atelier' => 'repair',
        ] as $slug => $icon) {
            DB::table('services')->where('slug', $slug)->whereNull('icon_key')->update(['icon_key' => $icon]);
        }
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn(['icon_key', 'image_path']);
        });
    }
};
