<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    // Lit un réglage dans MySQL (mis en cache), ex. Setting::get('phone').
    public static function get(string $key, ?string $default = null): ?string
    {
        return Cache::rememberForever("setting.$key", fn () => static::where('key', $key)->value('value')) ?? $default;
    }

    public static function publicValue(string $key): ?string
    {
        $value = trim(static::get($key, '') ?? '');

        if ($value === '' || preg_match('/\[[^\]]+\]|x{3,}|placeholder|\b(?:a|à)\s+renseigner\b/iu', $value)) {
            return null;
        }

        return $value;
    }

    public static function publicTagline(): string
    {
        $tagline = static::publicValue('tagline');
        $normalized = mb_strtolower(trim(preg_replace('/[\s[:punct:]]+/u', ' ', $tagline ?? '')));

        if ($tagline === null || $normalized === 'votre froid en panne on vient vite') {
            return 'Installation, entretien et dépannage en froid et climatisation.';
        }

        return $tagline;
    }

    public static function phoneNumber(): ?string
    {
        $value = static::publicValue('phone');
        if ($value === null || ! preg_match('/^\+?[\d\s().-]+$/u', $value)) {
            return null;
        }

        $target = preg_replace('/[\s().-]/u', '', $value);
        $digits = preg_replace('/\D/', '', $target);

        return preg_match('/^\+?\d{7,15}$/D', $target) && $digits !== '22997000000' ? $value : null;
    }

    public static function phoneHref(): ?string
    {
        $phone = static::phoneNumber();

        return $phone === null ? null : 'tel:' . preg_replace('/[^\d+]/', '', $phone);
    }

    public static function whatsappHref(): ?string
    {
        $value = static::publicValue('whatsapp');
        if ($value === null || ! preg_match('/^\+?[\d\s().-]+$/u', $value)) {
            return null;
        }

        $digits = preg_replace('/\D/', '', $value);

        return strlen($digits) >= 7 && strlen($digits) <= 15 && $digits !== '22997000000'
            ? 'https://wa.me/' . $digits
            : null;
    }

    public static function contactEmail(): ?string
    {
        $value = static::publicValue('contact_email');
        if ($value === null || ! filter_var($value, FILTER_VALIDATE_EMAIL) || preg_match('/@example\.(?:com|net|org)$/i', $value)) {
            return null;
        }

        return $value;
    }

    public static function publicAddress(): ?string
    {
        $value = static::publicValue('address');

        return $value !== null && ! preg_match('/^Cotonou,\s*Bénin$/iu', $value) ? $value : null;
    }

    protected static function booted(): void
    {
        static::saved(fn (self $s) => Cache::forget("setting.{$s->key}"));
        static::deleted(fn (self $s) => Cache::forget("setting.{$s->key}"));
    }
}
