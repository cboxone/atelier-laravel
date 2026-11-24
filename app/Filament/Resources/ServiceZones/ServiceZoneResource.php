<?php

namespace App\Filament\Resources\ServiceZones;

use App\Filament\Resources\ServiceZones\Pages\ManageServiceZones;
use App\Models\ServiceZone;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ServiceZoneResource extends Resource
{
    protected static ?string $model = ServiceZone::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;
    protected static ?string $navigationLabel = 'Zones desservies';
    protected static string|\UnitEnum|null $navigationGroup = 'Contenus publics';
    protected static ?int $navigationSort = 40;
    protected static ?string $modelLabel = 'zone';
    protected static ?string $pluralModelLabel = 'zones';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->label('Quartier ou ville')->required()->maxLength(255),
                TextInput::make('delivery_fee_fcfa')->label('Frais de livraison (FCFA)')->numeric()->integer()->minValue(0)->helperText('Laisser vide si aucun frais ne doit être indiqué.'),
                TextInput::make('sort_order')->label('Ordre d’affichage')->numeric()->integer()->minValue(0)->default(0),
                Toggle::make('is_active')->label('Afficher comme zone desservie')->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Zone')->searchable()->sortable(),
                TextColumn::make('delivery_fee_fcfa')->label('Livraison')->formatStateUsing(fn ($state) => $state === null ? '—' : number_format((int) $state, 0, ',', ' ') . ' FCFA'),
                TextColumn::make('sort_order')->label('Ordre')->sortable(),
                IconColumn::make('is_active')->label('Active')->boolean(),
            ])
            ->defaultSort('sort_order')
            ->recordActions([
                EditAction::make()->label('Modifier'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageServiceZones::route('/'),
        ];
    }
}
