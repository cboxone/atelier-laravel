<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Gestion des contenus publics

Les administrateurs autorisés utilisent `/admin` (authentification et contrôle d’accès existants) :

- **Site public → Profil et textes du site** : coordonnées, informations légales renseignées par l’entreprise, repères publiables, photos de l’accueil et leurs textes alternatifs, ainsi que les titres, introductions, boutons et légendes des pages publiques.
- **Contenus publics → Prestations** : choix d’une icône par prestation ou ajout d’une image personnalisée, en plus des textes, de l’ordre et de la publication.
- **Autres contenus publics** : formules d’entretien, horaires, zones desservies et témoignages. Un témoignage n’est affiché que si sa publication et le consentement sont confirmés.
- **Boutique** : catégories et produits (dont prix, disponibilité, publication, ordre et photo), moyens de paiement préconfigurés (libellé, consignes, activation, plafond et ordre). Les codes de paiement et le stock ne sont pas modifiables depuis ces écrans.

Sans photo réelle chargée, l’accueil montre une illustration locale de démonstration portant explicitement la mention « Visuel illustratif provisoire — à remplacer par une photo de l’atelier ». Elle n’est pas une photographie documentaire. Une photo ajoutée dans **Site public → Profil et textes du site → Photo de l’atelier** la remplace.

Les photos d’accueil et de galerie sont stockées sur le disque public dans `storage/app/public/homepage`; les visuels des prestations dans `storage/app/public/services`. Les images de prestation acceptent JPG, PNG ou WebP, jusqu’à 2 Mo. Les pièces jointes de demandes et fichiers d’atelier restent dans leurs circuits privés existants. Si le lien public n’est pas encore créé dans un nouvel environnement, exécuter `php artisan storage:link`.

Les textes administrables sont affichés comme texte échappé, sans HTML. Les destinations des liens, formulaires, validations, consentements, prix calculés, commandes, données personnelles et textes juridiquement significatifs ne sont pas éditables dans le CMS. Les pages légales permettent de gérer les coordonnées et certains titres, pas de réécrire les déclarations légales ou de confidentialité.

Le profil et les textes utilisent `settings`; les visuels de prestation sont persistés sur `services`. Exécuter les migrations Laravel avant déploiement pour ajouter les champs d’icône et d’image des prestations. Ne pas lancer de seed sur une base de production.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
