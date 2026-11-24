<?php

namespace App\Filament\Resources\ContractPlans\Pages;

use App\Filament\Resources\ContractPlans\ContractPlanResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageContractPlans extends ManageRecords
{
    protected static string $resource = ContractPlanResource::class;
    protected static ?string $title = 'Contrats d’entretien';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Ajouter une formule'),
        ];
    }
}
