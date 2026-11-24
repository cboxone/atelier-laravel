<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageProducts extends ManageRecords
{
    protected static string $resource = ProductResource::class;
    protected static ?string $title = 'Produits';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Ajouter un produit'),
        ];
    }
}
