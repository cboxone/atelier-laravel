<?php

namespace App\Filament\Resources\ProductCategories;

use App\Filament\Resources\ProductCategories\Pages\ManageProductCategories;
use App\Models\ProductCategory;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductCategoryResource extends Resource
{
    protected static ?string $model = ProductCategory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;
    protected static ?string $navigationLabel = 'Catégories de produits';
    protected static string|\UnitEnum|null $navigationGroup = 'Boutique';
    protected static ?int $navigationSort = 10;
    protected static ?string $modelLabel = 'catégorie';
    protected static ?string $pluralModelLabel = 'catégories';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->label('Nom')->required()->maxLength(255),
                TextInput::make('slug')->label('Identifiant URL')->required()->alphaDash()->unique(ignoreRecord: true)->maxLength(255),
                TextInput::make('sort_order')->label('Ordre d’affichage')->numeric()->integer()->minValue(0)->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Catégorie')->searchable()->sortable(),
                TextColumn::make('slug')->label('Identifiant URL')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('sort_order')->label('Ordre')->sortable(),
            ])
            ->defaultSort('sort_order')
            ->recordActions([
                EditAction::make()->label('Modifier'),
                DeleteAction::make()->label('Supprimer')->visible(fn (ProductCategory $record) => ! $record->products()->exists()),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageProductCategories::route('/'),
        ];
    }
}
