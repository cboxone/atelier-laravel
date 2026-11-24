<?php
// `php artisan filament:install --panels` génère app/Providers/Filament/AdminPanelProvider.php.
// Dans la méthode panel(), vérifier / ajuster seulement ces lignes :

return $panel
    ->default()
    ->id('admin')                      // doit rester 'admin' (voir User::canAccessPanel)
    ->path('admin')                    // le panneau sera sur /admin
    ->login()                          // page de connexion
    // NE PAS appeler ->registration() : personne ne doit pouvoir créer un compte.
    ->brandName('Administration de l’atelier');
    // ... le reste généré par l'installateur reste inchangé.
