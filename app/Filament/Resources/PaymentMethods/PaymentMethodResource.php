<?php

namespace App\Filament\Resources\PaymentMethods;

use App\Models\PaymentMethod;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PaymentMethodResource extends Resource
{
    protected static ?string $model = PaymentMethod::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;
    protected static ?string $navigationLabel = 'Moyens de paiement';
    protected static string|\UnitEnum|null $navigationGroup = 'Boutique';
    protected static ?int $navigationSort = 30;
    protected static ?string $modelLabel = 'moyen de paiement';
    protected static ?string $pluralModelLabel = 'moyens de paiement';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')->label('Code interne')->disabled()->dehydrated()->helperText('Identifiant technique non modifiable.'),
                TextInput::make('label')->label('Nom affiché au client')->required()->maxLength(255),
                Textarea::make('instructions')->label('Consignes de paiement')->rows(4)->maxLength(5000)->helperText('Variables disponibles : {reference} et {total}.'),
                TextInput::make('max_amount_fcfa')->label('Plafond par commande (FCFA)')->numeric()->integer()->minValue(0)->helperText('Laisser vide sans plafond.'),
                TextInput::make('sort_order')->label('Ordre d’affichage')->numeric()->integer()->minValue(0)->default(0),
                Toggle::make('is_enabled')->label('Proposé au paiement')->default(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('label')->label('Moyen de paiement')->searchable()->sortable(),
                TextColumn::make('code')->label('Code')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('max_amount_fcfa')->label('Plafond')->formatStateUsing(fn ($state) => $state === null ? 'Aucun' : number_format((int) $state, 0, ',', ' ') . ' FCFA'),
                IconColumn::make('is_enabled')->label('Activé')->boolean(),
            ])
            ->recordActions([
                EditAction::make()->label('Modifier'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\PaymentMethods\Pages\ManagePaymentMethods::route('/'),
        ];
    }
}
