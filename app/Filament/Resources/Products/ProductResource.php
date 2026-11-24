<?php

namespace App\Filament\Resources\Products;

use App\Filament\Resources\Products\Pages\ManageProducts;
use App\Models\Product;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingBag;
    protected static ?string $navigationLabel = 'Produits';
    protected static string|\UnitEnum|null $navigationGroup = 'Boutique';
    protected static ?int $navigationSort = 20;
    protected static ?string $modelLabel = 'produit';
    protected static ?string $pluralModelLabel = 'produits';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('product_category_id')->label('Catégorie')->relationship('category', 'name')->required()->searchable()->preload(),
                TextInput::make('name')->label('Nom')->required()->maxLength(255),
                TextInput::make('slug')->label('Identifiant URL')->required()->alphaDash()->unique(ignoreRecord: true)->maxLength(255),
                Textarea::make('description')->label('Description')->rows(3)->columnSpanFull(),
                TextInput::make('price_fcfa')->label('Prix (FCFA)')->numeric()->integer()->minValue(0)->helperText('Laisser vide pour afficher « sur devis ».'),
                Select::make('availability')->label('Disponibilité')->options([
                    'in_stock' => 'En stock',
                    'on_order' => 'Sur commande',
                    'unavailable' => 'Indisponible',
                ])->required()->default('in_stock'),
                FileUpload::make('image_path')->label('Photo du produit')->image()->disk('public')->directory('products')
                    ->visibility('public')->maxSize(5120)->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->helperText('JPG, PNG ou WebP — 5 Mo maximum. Fichier publié dans le catalogue.')->columnSpanFull(),
                TextInput::make('sort_order')->label('Ordre d’affichage')->numeric()->integer()->minValue(0)->default(0),
                Toggle::make('is_published')->label('Visible sur le site')->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Produit')->searchable()->sortable(),
                TextColumn::make('category.name')->label('Catégorie')->sortable(),
                TextColumn::make('price_fcfa')->label('Prix')->formatStateUsing(fn ($state) => $state === null ? 'Sur devis' : number_format((int) $state, 0, ',', ' ') . ' FCFA'),
                TextColumn::make('availability')->label('Disponibilité')->formatStateUsing(fn (string $state) => [
                    'in_stock' => 'En stock',
                    'on_order' => 'Sur commande',
                    'unavailable' => 'Indisponible',
                ][$state] ?? $state),
                IconColumn::make('is_published')->label('Publié')->boolean(),
            ])
            ->defaultSort('sort_order')
            ->recordActions([
                EditAction::make()->label('Modifier'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageProducts::route('/'),
        ];
    }
}
