<?php

namespace App\Filament\Resources\ContractPlans;

use App\Filament\Resources\ContractPlans\Pages\ManageContractPlans;
use App\Models\ContractPlan;
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

class ContractPlanResource extends Resource
{
    protected static ?string $model = ContractPlan::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;
    protected static ?string $navigationLabel = 'Contrats d’entretien';
    protected static string|\UnitEnum|null $navigationGroup = 'Contenus publics';
    protected static ?int $navigationSort = 20;
    protected static ?string $modelLabel = 'formule';
    protected static ?string $pluralModelLabel = 'formules';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->label('Nom de la formule')->required()->maxLength(255),
                Textarea::make('description')->label('Description')->required()->rows(4)->columnSpanFull(),
                TextInput::make('price_fcfa')->label('Prix annuel (FCFA)')->numeric()->integer()->minValue(0)->helperText('Laisser vide pour afficher « sur devis ».'),
                TextInput::make('visits_per_year')->label('Visites par an')->numeric()->integer()->minValue(0)->maxValue(255),
                TextInput::make('sort_order')->label('Ordre d’affichage')->numeric()->integer()->minValue(0)->default(0),
                Toggle::make('is_published')->label('Visible sur le site')->default(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Formule')->searchable()->sortable(),
                TextColumn::make('price_fcfa')->label('Prix annuel')->formatStateUsing(fn ($state) => $state === null ? 'Sur devis' : number_format((int) $state, 0, ',', ' ') . ' FCFA'),
                TextColumn::make('visits_per_year')->label('Visites/an')->placeholder('—'),
                TextColumn::make('sort_order')->label('Ordre')->sortable(),
                IconColumn::make('is_published')->label('Publiée')->boolean(),
            ])
            ->defaultSort('sort_order')
            ->recordActions([
                EditAction::make()->label('Modifier'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageContractPlans::route('/'),
        ];
    }
}
