<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Registre des fluides frigorigènes : AUCUNE modification ni suppression, jamais.
// Une erreur se corrige avec une écriture inverse (FluidRegister::reverse).
class FluidMovement extends Model
{
    protected $fillable = ['equipment_id', 'intervention_id', 'technician_id', 'reverses_id', 'operation', 'fluid', 'quantity_g', 'occurred_at', 'notes'];
    protected $casts = ['occurred_at' => 'datetime'];

    public const OPERATIONS = ['initial' => 'Charge initiale déclarée', 'charge' => 'Ajout de fluide', 'recovery' => 'Récupération', 'correction' => 'Correction'];

    protected static function booted(): void
    {
        static::updating(fn () => throw new \LogicException('Le registre des fluides ne se modifie pas : créez une écriture de correction.'));
        static::deleting(fn () => throw new \LogicException('Le registre des fluides ne se supprime pas : créez une écriture de correction.'));
    }

    public function equipment() { return $this->belongsTo(Equipment::class); }
    public function technician() { return $this->belongsTo(User::class, 'technician_id'); }
    public function reverses() { return $this->belongsTo(self::class, 'reverses_id'); }

    public function getKgAttribute(): string
    {
        return number_format($this->quantity_g / 1000, 3, ',', ' ');
    }
}
