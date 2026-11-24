<?php

namespace App\Filament\Resources\OpeningHours\Pages;

use App\Filament\Resources\OpeningHours\OpeningHourResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageOpeningHours extends ManageRecords
{
    protected static string $resource = OpeningHourResource::class;
    protected static ?string $title = 'Horaires d’ouverture';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Ajouter un jour'),
        ];
    }
}
