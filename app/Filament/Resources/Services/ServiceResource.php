<?php

namespace App\Filament\Resources\Services;

use App\Filament\Resources\Services\Pages\ManageServices;
use App\Models\Service;
use App\Support\ServiceVisual;
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

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWrenchScrewdriver;
    protected static ?string $navigationLabel = 'Prestations';
    protected static string|\UnitEnum|null $navigationGroup = 'Contenus publics';
    protected static ?int $navigationSort = 10;
    protected static ?string $modelLabel = 'prestation';
    protected static ?string $pluralModelLabel = 'prestations';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->label('Nom')->required()->maxLength(255),
                TextInput::make('slug')->label('Identifiant URL')->required()->alphaDash()->unique(ignoreRecord: true)->maxLength(255)
                    ->helperText('Utilisé dans les liens de demande de service.'),
                TextInput::make('summary')->label('Résumé public')->required()->maxLength(255)->columnSpanFull(),
                Textarea::make('description')->label('Description détaillée')->rows(4)->columnSpanFull(),
                Select::make('icon_key')->label('Icône du service')->options(ServiceVisual::options())
                    ->default(ServiceVisual::DEFAULT_ICON)
                    ->afterStateHydrated(function (Select $component, $state, ?Service $record): void {
                        if (! filled($state)) {
                            $component->state(ServiceVisual::iconKey(null, $record?->slug));
                        }
                    })
                    ->required()
                    ->helperText('Choisissez une icône cohérente. Une image ajoutée ci-dessous la remplacera sur le site.'),
                FileUpload::make('image_path')->label('Image du service (facultative)')->image()
                    ->disk('public')->directory('services')->visibility('public')->maxSize(2048)
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->helperText('JPG, PNG ou WebP — 2 Mo maximum. Fichier public. L’icône choisie s’affiche si aucune image n’est chargée.'),
                TextInput::make('sort_order')->label('Ordre d’affichage')->numeric()->integer()->minValue(0)->default(0),
                Toggle::make('is_published')->label('Visible sur le site')->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Prestation')->searchable()->sortable(),
                TextColumn::make('slug')->label('Identifiant URL')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('icon_key')->label('Icône')->formatStateUsing(fn (?string $state, Service $record): string => ServiceVisual::options()[ServiceVisual::iconKey($state, $record->slug)]),
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
            'index' => ManageServices::route('/'),
        ];
    }
}
