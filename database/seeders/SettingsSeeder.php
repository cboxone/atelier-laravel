<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            'business_name' => 'Atelier Froid Cotonou',
            'tagline' => 'Dépannage, installation et vente de climatiseurs',
            'phone' => '+229 97 00 00 00',
            'whatsapp' => '22997000000',
            'address' => 'Cotonou, Bénin',
            'notify_email' => 'admin@example.com',
            'contact_email' => 'contact@example.com',
            'certification' => '',
            'guarantee' => '',
            'response_time' => '',
            'ifu' => '',
            'rccm' => '',
            'publisher_name' => '',
            'hosting_provider' => '',
            'retention_text' => '',
        ];

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
