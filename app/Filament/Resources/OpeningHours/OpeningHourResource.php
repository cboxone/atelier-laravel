<?php

namespace App\Filament\Resources\OpeningHours;

use App\Filament\Resources\OpeningHours\Pages\ManageOpeningHours;
use App\Models\OpeningHour;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OpeningHourResource extends Resource
{
    protected static ?string $model = OpeningHour::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;
    protected static ?string $navigationLabel = 'Horaires d’ouverture';
    protected static string|\UnitEnum|null $navigationGroup = 'Contenus publics';
    protected static ?int $navigationSort = 30;
    protected static ?string $modelLabel = 'horaire';
    protected static ?string $pluralModelLabel = 'horaires';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('day_of_week')->label('Jour')->options(OpeningHour::DAYS)->required()->unique(ignoreRecord: true),
                Toggle::make('is_closed')->label('Fermé ce jour')->default(false),
                TimePicker::make('opens_at')->label('Ouverture')->seconds(false),
                TimePicker::make('closes_at')->label('Fermeture')->seconds(false),
                TextInput::make('note')->label('Précision publique')->maxLength(255)->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('day_of_week')->label('Jour')->formatStateUsing(fn (int $state) => OpeningHour::DAYS[$state] ?? '—')->sortable(),
                TextColumn::make('opens_at')->label('Ouverture')->formatStateUsing(fn ($state) => $state ? substr($state, 0, 5) : '—'),
                TextColumn::make('closes_at')->label('Fermeture')->formatStateUsing(fn ($state) => $state ? substr($state, 0, 5) : '—'),
                IconColumn::make('is_closed')->label('Fermé')->boolean(),
                TextColumn::make('note')->label('Précision')->placeholder('—'),
            ])
            ->defaultSort('day_of_week')
            ->recordActions([
                EditAction::make()->label('Modifier'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageOpeningHours::route('/'),
        ];
    }
}
