<?php

namespace Database\Seeders;

use App\Models\{PaymentMethod, ContractPlan, OpeningHour, ProductCategory, Product, Service, ServiceZone, Setting};
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

// Données de DÉPART : à remplacer par les vraies informations de l'atelier.
// Aucun prix ni horaire n'est inventé : prix = « sur devis », horaires = « à renseigner ».
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'business_name' => '[NOM DE L’ATELIER]',
            'phone'         => '+229XXXXXXXX',
            'whatsapp'      => '229XXXXXXXX',
            'address'       => '[ADRESSE / REPÈRE], Cotonou',
            'tagline'       => 'Votre froid en panne ? On vient vite.',
            'notify_email'  => null, // OBLIGATOIRE : e-mail qui reçoit les nouvelles demandes
            'contact_email' => null,
            'labor_rate_fcfa' => null, // tarif horaire TTC de la main-d'œuvre, utilisé par les techniciens (ils ne saisissent jamais de prix)
        ] as $k => $v) {
            Setting::updateOrCreate(['key' => $k], ['value' => $v]);
        }

        foreach (OpeningHour::DAYS as $n => $_) {
            OpeningHour::updateOrCreate(['day_of_week' => $n], ['is_closed' => false]);
        }

        foreach ([
            ['Dépannage urgent', 'Panne de climatiseur, de frigo ou de chambre froide : intervention chez vous.'],
            ['Installation', 'Pose de splits et de systèmes de froid pour maisons, bureaux et commerces.'],
            ['Entretien et contrats', 'Nettoyage, contrôle des gaz et visites régulières.'],
            ['Recharge de gaz', 'Détection de fuite et recharge.'],
            ['Chambres froides', 'Étude, montage et maintenance pour professionnels.'],
            ['Réparation en atelier', 'Compresseurs, cartes électroniques, congélateurs et vitrines.'],
        ] as $i => [$name, $summary]) {
            Service::updateOrCreate(['slug' => Str::slug($name)], ['name' => $name, 'summary' => $summary, 'sort_order' => $i]);
        }

        ServiceZone::updateOrCreate(['name' => 'Cotonou'], ['sort_order' => 0]);
        // Moyens de paiement. Seuls ceux qui n'ont besoin d'aucune information sont actifs au départ.
        // Les autres s'activent dans l'administration après avoir saisi vos numéros / votre RIB dans « consignes ».
        foreach ([
            ['cash_on_delivery', 'Paiement à la livraison', 'Vous payez en espèces au livreur, à la remise de votre commande {reference} ({total}).', true],
            ['pay_at_workshop', 'Paiement à l’atelier (retrait sur place)', 'Venez retirer et payer votre commande {reference} ({total}) à l’atelier.', true],
            ['manual_mobile_money', 'Mobile Money : envoi à un numéro de l’atelier (MTN, Moov, Celtiis)', null, false],
            ['bank_transfer', 'Virement bancaire', null, false],
            ['online', 'Paiement en ligne (Mobile Money, carte)', null, false],
        ] as $i => [$code, $label, $instructions, $enabled]) {
            PaymentMethod::updateOrCreate(['code' => $code], ['label' => $label, 'instructions' => $instructions, 'is_enabled' => $enabled, 'sort_order' => $i]);
        }


        // Formules de contrat : non publiées tant que l'atelier n'a pas défini son offre.
        foreach ([['Essentiel', 'Visites régulières, nettoyage et contrôle général.'], ['Confort', 'Visites régulières, contrôle des fluides, priorité en cas de panne.'], ['Pro commerce', 'Pour chambres froides et vitrines : dépannage prioritaire et rapport écrit.']] as $i => [$n, $d]) {
            ContractPlan::updateOrCreate(['name' => $n], ['description' => $d, 'sort_order' => $i, 'is_published' => false]);
        }

        foreach (['Climatiseurs', 'Réfrigérateurs et congélateurs', 'Vitrines réfrigérées', 'Gaz et fluides', 'Pièces détachées'] as $i => $c) {
            $cat = ProductCategory::updateOrCreate(['slug' => Str::slug($c)], ['name' => $c, 'sort_order' => $i]);
            if ($i === 0) {
                Product::updateOrCreate(['slug' => 'split-mural-12000-btu'], [
                    'product_category_id' => $cat->id, 'name' => 'Split mural 12 000 BTU',
                    'price_fcfa' => null, 'availability' => 'in_stock', 'is_published' => false, // masqué tant que non renseigné
                ]);
            }
        }
    }
}
