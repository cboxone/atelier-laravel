<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class ServiceVisual
{
    public const DEFAULT_ICON = 'wrench';

    public static function options(): array
    {
        return [
            'repair' => 'Dépannage / réparation',
            'snowflake' => 'Froid / réfrigération',
            'wrench' => 'Installation / outillage',
            'fan' => 'Climatisation / ventilation',
            'shield' => 'Entretien / protection',
            'droplet' => 'Fluide frigorigène',
        ];
    }

    public static function iconKey(?string $iconKey, ?string $slug = null): string
    {
        if (is_string($iconKey) && array_key_exists($iconKey, self::options())) {
            return $iconKey;
        }

        return match ($slug) {
            'depannage-urgent', 'reparation-en-atelier' => 'repair',
            'installation' => 'wrench',
            'entretien-et-contrats' => 'shield',
            'recharge-de-gaz' => 'droplet',
            'chambres-froides' => 'snowflake',
            default => self::DEFAULT_ICON,
        };
    }

    public static function imagePath(?string $path): ?string
    {
        if (! is_string($path)
            || preg_match('/\Aservices\/[A-Za-z0-9][A-Za-z0-9._\/-]*\z/D', $path) !== 1
            || preg_match('~(?:^|/)\.{1,2}(?:/|$)|//|/$~', $path) === 1) {
            return null;
        }

        return Storage::disk('public')->exists($path) ? $path : null;
    }
}
