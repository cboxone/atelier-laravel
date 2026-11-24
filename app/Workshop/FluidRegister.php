<?php

namespace App\Workshop;

use App\Models\{Equipment, FluidMovement, Intervention, User};
use Illuminate\Support\Facades\DB;

// Registre des fluides frigorigènes. Quantités en grammes (entiers). Le solde d'un équipement est la somme de ses écritures.
class FluidRegister
{
    public function balance(Equipment $equipment): int
    {
        return (int) FluidMovement::where('equipment_id', $equipment->id)->sum('quantity_g');
    }

    public function record(Equipment $equipment, string $operation, int $grams, string $fluid, ?User $technician = null, ?Intervention $intervention = null, ?string $notes = null): FluidMovement
    {
        if ($grams < 1) throw new WorkshopException('La quantité doit être strictement positive.');
        if (! in_array($operation, ['initial', 'charge', 'recovery'], true)) throw new WorkshopException('Opération inconnue : les corrections passent par reverse().');
        if ($intervention && $intervention->equipment_id !== $equipment->id) throw new WorkshopException('Cet équipement n’est pas celui de l’intervention.');
        $fluid = trim($fluid);
        if ($fluid === '') throw new WorkshopException('Indiquez le fluide.');
        if ($equipment->fluid_type && strcasecmp($equipment->fluid_type, $fluid) !== 0) {
            throw new WorkshopException("Cet équipement fonctionne avec {$equipment->fluid_type}, pas avec $fluid.");
        }

        return DB::transaction(function () use ($equipment, $operation, $grams, $fluid, $technician, $intervention, $notes) {
            Equipment::lockForUpdate()->findOrFail($equipment->id); // les écritures d'un même équipement passent l'une après l'autre
            $balance = $this->balance($equipment);

            if ($operation === 'initial' && FluidMovement::where('equipment_id', $equipment->id)->exists()) {
                throw new WorkshopException('La charge initiale ne se déclare qu’une fois, avant toute autre écriture.');
            }
            $signed = $operation === 'recovery' ? -$grams : $grams;
            if ($balance + $signed < 0) {
                throw new WorkshopException('Récupération supérieure à la charge enregistrée (' . number_format($balance / 1000, 3, ',', ' ') . ' kg). Déclarez d’abord la charge initiale.');
            }

            return FluidMovement::create([
                'equipment_id' => $equipment->id, 'intervention_id' => $intervention?->id, 'technician_id' => $technician?->id,
                'operation' => $operation, 'fluid' => $fluid, 'quantity_g' => $signed, 'occurred_at' => now(), 'notes' => $notes,
            ]);
        });
    }

    // Correction : une nouvelle écriture de sens inverse, qui référence l'erreur. L'écriture d'origine reste visible.
    public function reverse(FluidMovement $movement, User $by, string $reason): FluidMovement
    {
        $reason = trim($reason);
        if ($reason === '') throw new WorkshopException('Indiquez la raison de la correction.');

        return DB::transaction(function () use ($movement, $by, $reason) {
            Equipment::lockForUpdate()->findOrFail($movement->equipment_id);
            if ($movement->operation === 'correction') throw new WorkshopException('Une correction ne se corrige pas : saisissez une nouvelle écriture.');
            if (FluidMovement::where('reverses_id', $movement->id)->exists()) throw new WorkshopException('Cette écriture a déjà été corrigée.');
            $signed = -$movement->quantity_g;
            $balance = (int) FluidMovement::where('equipment_id', $movement->equipment_id)->sum('quantity_g');
            if ($balance + $signed < 0) throw new WorkshopException('Cette correction rendrait le solde de l’équipement négatif.');

            return FluidMovement::create([
                'equipment_id' => $movement->equipment_id, 'intervention_id' => $movement->intervention_id, 'technician_id' => $by->id,
                'reverses_id' => $movement->id, 'operation' => 'correction', 'fluid' => $movement->fluid,
                'quantity_g' => $signed, 'occurred_at' => now(), 'notes' => 'Correction : ' . $reason,
            ]);
        });
    }
}
