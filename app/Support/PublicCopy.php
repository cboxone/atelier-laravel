<?php

namespace App\Support;

use App\Models\Setting;

class PublicCopy
{
    public static function text(string $key, string $fallback): string
    {
        return Setting::publicValue('copy_' . $key) ?? $fallback;
    }

    /**
     * The editable copy is intentionally limited to presentation text. Form
     * fields, legal statements, prices, and transactional instructions remain fixed.
     */
    public static function groups(): array
    {
        return [
            'En-tête et pied de page' => [
                'shared_nav_home' => ['Accueil', 'Accueil', 'short'],
                'shared_nav_services' => ['Services', 'Services', 'short'],
                'shared_nav_products' => ['Produits', 'Produits', 'short'],
                'shared_nav_contracts' => ['Contrats', 'Contrats', 'short'],
                'shared_nav_contact' => ['Contact', 'Contact', 'short'],
                'shared_nav_cart' => ['Panier', 'Panier', 'short'],
                'shared_request_button' => ['Bouton d’intervention', 'Demander une intervention', 'short'],
                'shared_footer_tagline' => ['Phrase de pied de page', 'Votre confort, notre métier.', 'short'],
                'shared_footer_contact' => ['Pied de page — coordonnées', 'Nous joindre', 'short'],
                'shared_footer_services' => ['Pied de page — prestations', 'À votre service', 'short'],
                'shared_footer_services_link' => ['Pied de page — lien prestations', 'Nos services', 'short'],
                'shared_footer_products_link' => ['Pied de page — lien produits', 'Produits et prix', 'short'],
                'shared_footer_request_link' => ['Pied de page — lien demande', 'Demander une intervention', 'short'],
                'shared_footer_information' => ['Pied de page — informations', 'Informations', 'short'],
                'shared_footer_legal_link' => ['Pied de page — lien mentions légales', 'Mentions légales', 'short'],
                'shared_footer_privacy_link' => ['Pied de page — lien confidentialité', 'Confidentialité', 'short'],
                'shared_footer_track_link' => ['Pied de page — lien suivi', 'Suivre ma commande', 'short'],
            ],
            'Accueil' => [
                'home_meta_description' => ['Description pour les moteurs de recherche', 'dépannage, installation et vente de climatiseurs et de froid à Cotonou.', 'long'],
                'home_hero_eyebrow' => ['Introduction du bandeau', 'Froid · Climatisation · Services', 'short'],
                'home_hero_intro' => ['Texte d’introduction', 'Installation, entretien et dépannage pour vos équipements de froid et de climatisation. Parlons de votre besoin.', 'long'],
                'home_hero_note' => ['Conseil avant la demande', 'Pour préparer votre demande : l’équipement, le problème et votre quartier.', 'long'],
                'home_call_button' => ['Bouton — appel', 'Appeler maintenant', 'short'],
                'home_urgent_button' => ['Bouton — urgence', 'Signaler une panne urgente', 'short'],
                'home_request_button' => ['Bouton — demande', 'Demander une intervention', 'short'],
                'home_products_button' => ['Bouton — boutique', 'Découvrir les produits', 'short'],
                'home_open_label' => ['Statut horaire — ouvert', 'Ouvert maintenant', 'short'],
                'home_closed_label' => ['Statut horaire — fermé', 'Fermé actuellement.', 'short'],
                'home_scope_eyebrow' => ['Repères — surtitre', 'Repères utiles', 'short'],
                'home_scope_heading' => ['Repères — titre', 'Un champ d’intervention clair.', 'short'],
                'home_scope_label' => ['Libellé des prestations', 'Prestations publiées', 'short'],
                'home_scope_empty' => ['Texte si aucune prestation', 'Installation, entretien et dépannage de vos équipements de froid et de climatisation.', 'long'],
                'home_zones_label' => ['Libellé des zones', 'Zones indiquées', 'short'],
                'home_services_heading' => ['Services — titre', 'Nos services', 'short'],
                'home_services_intro' => ['Services — introduction', 'Des solutions adaptées à votre équipement et à votre quotidien.', 'long'],
                'home_services_empty' => ['Services — aucun résultat', 'Contactez-nous pour en savoir plus sur nos prestations.', 'long'],
                'home_request_eyebrow' => ['Préparer une demande — surtitre', 'Une demande bien préparée', 'short'],
                'home_request_heading' => ['Préparer une demande — titre', 'Comment préparer votre demande', 'short'],
                'home_request_intro' => ['Préparer une demande — introduction', 'Quelques précisions suffisent pour décrire votre besoin dans le formulaire.', 'long'],
                'home_request_step_equipment' => ['Étape — équipement', 'L’équipement', 'short'],
                'home_request_step_equipment_text' => ['Détail — équipement', 'Indiquez le type d’appareil ou d’installation concerné.', 'long'],
                'home_request_step_issue' => ['Étape — problème', 'Le problème', 'short'],
                'home_request_step_issue_text' => ['Détail — problème', 'Décrivez ce que vous constatez, même en quelques mots.', 'long'],
                'home_request_step_contact' => ['Étape — quartier et contact', 'Le quartier et un contact', 'short'],
                'home_request_step_contact_text' => ['Détail — quartier et contact', 'Précisez votre zone et un numéro pour vous joindre.', 'long'],
                'home_request_link' => ['Lien de demande', 'Préparer ma demande', 'short'],
                'home_gallery_heading' => ['Galerie — titre', 'Dans les coulisses de l’atelier', 'short'],
                'home_gallery_intro' => ['Galerie — introduction', 'Quelques images de nos espaces et de nos interventions.', 'long'],
                'home_workshop_caption' => ['Légende de la photo atelier', 'Atelier', 'short'],
                'home_intervention_caption' => ['Légende de la photo intervention', 'Intervention', 'short'],
                'home_proof_heading' => ['Repères renseignés — titre', 'Repères renseignés', 'short'],
                'home_proof_intro' => ['Repères renseignés — introduction', 'Les informations ci-dessous sont celles communiquées par l’atelier.', 'long'],
                'home_shop_heading' => ['Boutique — titre', 'À découvrir en boutique', 'short'],
                'home_shop_link' => ['Boutique — lien', 'Voir tous les produits', 'short'],
                'home_hours_heading' => ['Horaires — titre', 'Quand nous trouver', 'short'],
                'home_hours_intro' => ['Horaires — introduction', 'Consultez nos horaires avant de passer.', 'long'],
                'home_zones_heading' => ['Zones — titre', 'Zones d’intervention', 'short'],
                'home_testimonials_heading' => ['Avis — titre', 'Ils nous font confiance', 'short'],
                'home_final_eyebrow' => ['Dernière section — surtitre', 'Un projet ou une panne ?', 'short'],
                'home_final_heading' => ['Dernière section — titre', 'Parlons de votre besoin.', 'short'],
                'home_final_intro' => ['Dernière section — introduction', 'Décrivez votre besoin ou consultez les moyens de contact disponibles.', 'long'],
                'home_final_request_button' => ['Dernière section — bouton demande', 'Décrire mon besoin', 'short'],
                'home_final_contact_link' => ['Dernière section — lien contact', 'Voir les options de contact', 'short'],
            ],
            'Services et contrats' => [
                'services_eyebrow' => ['Services — surtitre', 'Des solutions concrètes', 'short'],
                'services_title' => ['Services — titre', 'Nos services', 'short'],
                'services_lead' => ['Services — introduction', 'De l’installation au dépannage, nous vous aidons à garder vos équipements fiables et performants.', 'long'],
                'services_empty_title' => ['Services — aucun résultat, titre', 'Nous restons à votre écoute', 'short'],
                'services_empty_body' => ['Services — aucun résultat, texte', 'Contactez-nous pour parler de votre équipement et de votre besoin.', 'long'],
                'services_cta' => ['Services — appel à l’action', 'Vous ne savez pas par où commencer ? Décrivez-nous votre besoin.', 'long'],
                'services_item_link' => ['Services — lien de prestation', 'En savoir plus', 'short'],
                'services_request_link' => ['Services — bouton prestation', 'Demander ce service', 'short'],
                'products_eyebrow' => ['Produits — surtitre', 'La boutique', 'short'],
                'products_title' => ['Produits — titre', 'Produits et prix', 'short'],
                'products_lead' => ['Produits — introduction', 'Parcourez notre sélection. Pour un équipement adapté à votre espace, contactez-nous avant de choisir.', 'long'],
                'products_empty_title' => ['Produits — catalogue vide, titre', 'Catalogue en cours de mise à jour', 'short'],
                'products_empty_body' => ['Produits — catalogue vide, texte', 'Contactez-nous pour parler de votre besoin et demander un devis.', 'long'],
                'products_note' => ['Produits — note de prix', 'Prix en FCFA, pose en option. Contactez-nous pour un devis.', 'long'],
                'products_add_button' => ['Produits — bouton panier', 'Ajouter au panier', 'short'],
                'products_quote_button' => ['Produits — bouton devis', 'Demander un devis', 'short'],
                'contracts_eyebrow' => ['Contrats — surtitre', 'Préserver vos équipements', 'short'],
                'contracts_title' => ['Contrats — titre', 'Contrats d’entretien', 'short'],
                'contracts_lead' => ['Contrats — introduction', 'Des visites régulières pour garder votre froid et votre climatisation en bon état.', 'long'],
                'contracts_empty_title' => ['Contrats — aucune formule, titre', 'Une formule adaptée à votre besoin', 'short'],
                'contracts_empty_body' => ['Contrats — aucune formule, texte', 'Nos formules sont en cours de définition. Demandez un devis personnalisé.', 'long'],
            ],
            'Contact et demande' => [
                'contact_eyebrow' => ['Contact — surtitre', 'Une question ?', 'short'],
                'contact_title' => ['Contact — titre', 'Contact', 'short'],
                'contact_lead' => ['Contact — introduction', 'Contactez-nous au sujet de votre équipement ou demandez une intervention.', 'long'],
                'contact_hours_eyebrow' => ['Horaires — surtitre', 'Horaires d’ouverture', 'short'],
                'contact_hours_title' => ['Horaires — titre', 'À quel moment passer ?', 'short'],
                'contact_missing_title' => ['Coordonnées absentes — titre', 'Coordonnées non disponibles', 'short'],
                'contact_missing_body' => ['Coordonnées absentes — texte', 'Les coordonnées de l’atelier ne sont pas encore renseignées. Vous pouvez tout de même nous décrire votre besoin.', 'long'],
                'contact_cta' => ['Contact — appel à l’action', 'Besoin d’une intervention ou d’un devis ? Décrivez-nous votre situation.', 'long'],
                'contact_button' => ['Contact — bouton', 'Faire une demande', 'short'],
                'request_eyebrow' => ['Demande — surtitre', 'Nous sommes à votre écoute', 'short'],
                'request_title' => ['Demande — titre', 'Demander une intervention ou un devis', 'short'],
                'request_lead' => ['Demande — introduction', 'Quelques informations nous aideront à mieux comprendre votre besoin et à vous recontacter.', 'long'],
            ],
            'Boutique et suivi' => [
                'cart_title' => ['Panier — titre', 'Mon panier', 'short'],
                'cart_empty_title' => ['Panier vide — titre', 'Votre panier est vide.', 'short'],
                'cart_empty_body' => ['Panier vide — texte', 'Parcourez la boutique pour découvrir les produits disponibles.', 'long'],
                'checkout_eyebrow' => ['Commande — surtitre', 'La boutique · Étape finale', 'short'],
                'checkout_title' => ['Commande — titre', 'Finaliser la commande', 'short'],
                'checkout_lead' => ['Commande — introduction', 'Renseignez vos informations de livraison et choisissez votre moyen de paiement.', 'long'],
                'checkout_unavailable' => ['Commande indisponible — titre', 'Commande en ligne momentanément indisponible', 'short'],
                'shop_thanks_title' => ['Confirmation commande — titre', 'Commande reçue', 'short'],
                'track_title' => ['Suivi — titre', 'Suivre ma commande', 'short'],
                'track_lead' => ['Suivi — introduction', 'Retrouvez l’avancement avec le numéro de commande et le téléphone utilisés lors de l’achat.', 'long'],
                'thanks_title' => ['Confirmation demande — titre', 'Demande reçue', 'short'],
            ],
            'Informations légales' => [
                'legal_eyebrow' => ['Mentions légales — surtitre', 'Informations', 'short'],
                'legal_title' => ['Mentions légales — titre', 'Mentions légales', 'short'],
                'privacy_eyebrow' => ['Confidentialité — surtitre', 'Vos données', 'short'],
                'privacy_title' => ['Confidentialité — titre', 'Politique de confidentialité', 'short'],
            ],
        ];
    }
}
