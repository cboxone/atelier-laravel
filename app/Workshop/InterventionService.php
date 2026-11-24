<?php

namespace App\Workshop;

use App\Models\{Intervention, Product};
use Illuminate\Support\Facades\DB;

class InterventionService
{
    // Termine l'intervention ET retire du stock les pièces utilisées, ensemble ou pas du tout.
    public function finish(Intervention $intervention, ?string $workDone = null): Intervention
    {
        return DB::transaction(function () use ($intervention, $workDone) {
            $i = Intervention::with('lines')->lockForUpdate()->findOrFail($intervention->id);
            if ($i->status === 'done') return $i;
            if ($i->status === 'cancelled') throw new WorkshopException('Intervention annulée.');

            foreach ($i->lines->where('kind', 'part')->whereNotNull('product_id')->groupBy('product_id') as $productId => $lines) {
                $product = Product::lockForUpdate()->find($productId);
                $qty = (int) $lines->sum('quantity');
                if (! $product || $product->stock_quantity === null) continue;          // stock non suivi
                if ($product->stock_quantity < $qty) {
                    throw new WorkshopException("Stock insuffisant pour « {$product->name} » (reste {$product->stock_quantity}, {$qty} utilisé(s)).");
                }
                $product->adjustStock(-$qty, 'intervention', null, $i->id);
            }

            $i->update(['status' => 'done', 'finished_at' => now(), 'work_done' => $workDone ?? $i->work_done]);

            if ($i->contract_visit_id) { // visite d'un contrat d'entretien : faite quand TOUTES ses interventions sont terminées
                $open = Intervention::where('contract_visit_id', $i->contract_visit_id)->whereNotIn('status', ['done', 'cancelled'])->exists();
                if (! $open) \App\Models\ContractVisit::whereKey($i->contract_visit_id)->update(['status' => 'done']);
            }
            return $i;
        });
    }
}
