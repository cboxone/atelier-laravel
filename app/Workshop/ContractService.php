<?php

namespace App\Workshop;

use App\Invoicing\{BillingService, InvoiceException};
use App\Models\{ContractVisit, Intervention, Invoice, MaintenanceContract};
use App\Support\VisitSchedule;
use Illuminate\Support\Facades\DB;

class ContractService
{
    public function create(array $data, array $equipmentIds): MaintenanceContract
    {
        $start = new \DateTimeImmutable($data['starts_on']);
        $end = new \DateTimeImmutable($data['ends_on']);
        try {
            $dates = VisitSchedule::dates($start, $end, (int) $data['visits_per_year']);
        } catch (\InvalidArgumentException $e) {
            throw new WorkshopException($e->getMessage());
        }
        if (! $equipmentIds) throw new WorkshopException('Choisissez au moins un équipement couvert.');

        return DB::transaction(function () use ($data, $equipmentIds, $dates) {
            $contract = MaintenanceContract::create($data);
            $customerEquipments = $contract->customer->equipments()->whereIn('id', $equipmentIds)->pluck('id');
            if ($customerEquipments->count() !== count(array_unique($equipmentIds))) {
                throw new WorkshopException('Un équipement choisi n’appartient pas à ce client.');
            }
            $contract->equipments()->sync($customerEquipments->all());
            foreach ($dates as $d) $contract->visits()->create(['due_on' => $d]);
            return $contract;
        });
    }

    // Crée les interventions des visites à venir : une par équipement couvert. Sans doublon (relançable à volonté).
    public function generateInterventions(int $daysAhead = 14, ?\DateTimeInterface $today = null): int
    {
        $limit = ($today ? \Carbon\Carbon::instance($today) : now())->copy()->addDays($daysAhead)->toDateString();
        $created = 0;

        $visits = ContractVisit::with('contract.equipments')->where('status', 'planned')->whereNull('interventions_created_at')
            ->whereDate('due_on', '<=', $limit)->whereHas('contract', fn ($q) => $q->where('status', 'active'))->get();

        foreach ($visits as $visit) {
            DB::transaction(function () use ($visit, &$created) {
                $v = ContractVisit::lockForUpdate()->find($visit->id);
                if ($v->interventions_created_at) return; // déjà fait par un autre passage
                foreach ($visit->contract->equipments as $eq) {
                    Intervention::create([
                        'customer_id' => $visit->contract->customer_id, 'equipment_id' => $eq->id, 'contract_visit_id' => $v->id,
                        'title' => 'Entretien : ' . $visit->contract->title . ' (' . $eq->label . ')', 'scheduled_at' => $v->due_on->copy()->setTime(8, 0),
                    ]);
                    $created++;
                }
                $v->update(['interventions_created_at' => now()]);
            });
        }
        return $created;
    }

    // Facture du contrat : une seule fois, un brouillon que l'on émet ensuite comme toute facture.
    public function invoice(MaintenanceContract $contract): Invoice
    {
        return DB::transaction(function () use ($contract) {
            $c = MaintenanceContract::lockForUpdate()->findOrFail($contract->id);
            if ($c->invoiced_at) throw new InvoiceException('Ce contrat a déjà été facturé.');
            if ($c->status !== 'active') throw new InvoiceException('Seul un contrat actif se facture.');
            if ($c->price_fcfa < 1) throw new InvoiceException('Le prix du contrat est à zéro.');
            $invoice = Invoice::create(['customer_id' => $c->customer_id]);
            $invoice->lines()->create(['description' => 'Contrat d’entretien : ' . $c->title . ' (' . $c->starts_on->format('d/m/Y') . ' au ' . $c->ends_on->format('d/m/Y') . ')',
                'quantity' => 1, 'unit_price_fcfa' => $c->price_fcfa, 'tax_group' => config('emecef.default_tax_group')]);
            $c->update(['invoiced_at' => now()]);
            return $invoice;
        });
    }
}
