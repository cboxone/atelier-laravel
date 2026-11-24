<?php

namespace App\Console\Commands;

use App\Workshop\ContractService;
use Illuminate\Console\Command;

class GenerateContractInterventions extends Command
{
    protected $signature = 'contracts:generate-interventions {--days=14 : Visites dues dans les N prochains jours}';
    protected $description = 'Crée les interventions des visites d’entretien à venir (sans doublon)';

    public function handle(ContractService $service): int
    {
        $n = $service->generateInterventions((int) $this->option('days'));
        $this->info("$n intervention(s) créée(s).");
        return self::SUCCESS;
    }
}
