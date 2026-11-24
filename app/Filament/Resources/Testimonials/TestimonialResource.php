<?php

namespace App\Filament\Resources\Testimonials;

use App\Filament\Resources\Testimonials\Pages\ManageTestimonials;
use App\Models\Testimonial;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TestimonialResource extends Resource
{
    protected static ?string $model = Testimonial::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;
    protected static ?string $navigationLabel = 'Témoignages';
    protected static string|\UnitEnum|null $navigationGroup = 'Contenus publics';
    protected static ?int $navigationSort = 50;
    protected static ?string $modelLabel = 'témoignage';
    protected static ?string $pluralModelLabel = 'témoignages';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('author_name')->label('Nom affiché')->required()->maxLength(255),
                DatePicker::make('given_on')->label('Date de l’avis')->native(false),
                Textarea::make('content')->label('Texte')->required()->rows(5)->maxLength(5000)->columnSpanFull(),
                Toggle::make('consent_given')->label('Consentement à la publication confirmé')->default(false)
                    ->helperText('Ne publier que les avis réels pour lesquels la personne a donné son accord.'),
                Toggle::make('is_published')->label('Publié')->default(false)
                    ->helperText('Un avis sans consentement confirmé reste masqué sur le site.'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('author_name')->label('Nom affiché')->searchable()->sortable(),
                TextColumn::make('content')->label('Avis')->limit(70)->wrap(),
                IconColumn::make('consent_given')->label('Accord confirmé')->boolean(),
                IconColumn::make('is_published')->label('Publié')->boolean(),
                TextColumn::make('given_on')->label('Date')->date()->sortable()->placeholder('—'),
            ])
            ->defaultSort('given_on', 'desc')
            ->recordActions([
                EditAction::make()->label('Modifier'),
                DeleteAction::make()->label('Supprimer'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageTestimonials::route('/'),
        ];
    }
}
