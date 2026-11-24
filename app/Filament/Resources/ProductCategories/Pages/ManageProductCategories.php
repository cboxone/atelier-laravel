<?php

namespace App\Filament\Resources\ProductCategories\Pages;

use App\Filament\Resources\ProductCategories\ProductCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageProductCategories extends ManageRecords
{
    protected static string $resource = ProductCategoryResource::class;
    protected static ?string $title = 'Catégories de produits';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Ajouter une catégorie'),
        ];
    }
}
