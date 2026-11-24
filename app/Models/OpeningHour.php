<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OpeningHour extends Model
{
    protected $fillable = ['day_of_week', 'opens_at', 'closes_at', 'is_closed', 'note'];
    protected $casts = ['is_closed' => 'boolean'];

    public const DAYS = [1 => 'Lundi', 2 => 'Mardi', 3 => 'Mercredi', 4 => 'Jeudi', 5 => 'Vendredi', 6 => 'Samedi', 7 => 'Dimanche'];

    public function getDayNameAttribute(): string
    {
        return self::DAYS[$this->day_of_week];
    }

    public function getLabelAttribute(): string
    {
        if ($this->is_closed) return 'Fermé' . ($this->note ? ' (' . $this->note . ')' : '');
        if (! $this->opens_at || ! $this->closes_at) return 'À renseigner';
        return substr($this->opens_at, 0, 5) . ' – ' . substr($this->closes_at, 0, 5);
    }

    // true / false, ou null si les horaires du jour ne sont pas renseignés.
    public static function isOpenNow(): ?bool
    {
        $now = now(); // fuseau défini par APP_TIMEZONE
        $today = static::where('day_of_week', $now->dayOfWeekIso)->first();
        if (! $today) return null;
        if ($today->is_closed) return false;
        if (! $today->opens_at || ! $today->closes_at) return null;
        $t = $now->format('H:i:s');
        return $t >= $today->opens_at && $t < $today->closes_at;
    }
}
