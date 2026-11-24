<?php

namespace App\Filament\Resources\ServiceZones\Pages;

use App\Filament\Resources\ServiceZones\ServiceZoneResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageServiceZones extends ManageRecords
{
    protected static string $resource = ServiceZoneResource::class;
    protected static ?string $title = 'Zones desservies';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Ajouter une zone'),
        ];
    }
}
