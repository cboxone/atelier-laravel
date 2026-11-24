<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use App\Support\PublicCopy;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use App\Support\HomepageGallery;
use Illuminate\Support\Facades\Storage;
use UnitEnum;

class WebsiteSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedGlobeAlt;
    protected static ?string $navigationLabel = 'Profil et textes du site';
    protected static string|UnitEnum|null $navigationGroup = 'Site public';
    protected static ?int $navigationSort = 1;
    protected static ?string $title = 'Profil et textes du site';

    protected string $view = 'filament.pages.website-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $data = [];

        foreach ([
            'business_name', 'tagline', 'phone', 'whatsapp', 'contact_email', 'address',
            'ifu', 'rccm', 'publisher_name', 'hosting_provider', 'retention_text',
            'certification', 'guarantee', 'response_time',
        ] as $key) {
            $data[$key] = Setting::get($key, '');
        }

        foreach (['homepage_workshop_image', 'homepage_workshop_alt', 'homepage_intervention_image', 'homepage_intervention_alt'] as $key) {
            $data[$key] = Setting::get($key, '');
        }
        $data[HomepageGallery::SETTING_KEY] = HomepageGallery::decode(Setting::get(HomepageGallery::SETTING_KEY));
        $data['homepage_workshop_alt'] = Setting::get('homepage_workshop_alt', 'Atelier de froid et de climatisation');
        $data['homepage_intervention_alt'] = Setting::get('homepage_intervention_alt', 'Intervention sur un équipement de froid ou de climatisation');

        foreach (PublicCopy::groups() as $fields) {
            foreach ($fields as $key => [, $fallback]) {
                $data['copy_' . $key] = Setting::get('copy_' . $key, $fallback);
            }
        }

        $this->form->fill($data);
    }

    public function form(Schema $schema): Schema
    {
        $components = [
            Section::make('Profil public')
                ->description('Ces coordonnées suivent les filtres publics existants : les valeurs manifestement provisoires restent masquées.')
                ->columns(2)
                ->schema([
                    TextInput::make('business_name')->label('Nom de l’entreprise')->maxLength(255),
                    TextInput::make('tagline')->label('Phrase de présentation')->maxLength(500)
                        ->helperText('Un texte générique ou provisoire sera remplacé par la formulation publique de secours.'),
                    TextInput::make('phone')->label('Téléphone public')->tel()->maxLength(40),
                    TextInput::make('whatsapp')->label('WhatsApp public')->tel()->maxLength(40),
                    TextInput::make('contact_email')->label('E-mail public')->email()->maxLength(255),
                    TextInput::make('address')->label('Adresse publique')->maxLength(500),
                ]),
            Section::make('Informations légales et repères communiqués')
                ->description('Renseignez uniquement les informations confirmées par l’entreprise. Les textes légaux de fond restent fixes.')
                ->columns(2)
                ->schema([
                    TextInput::make('ifu')->label('IFU')->maxLength(100),
                    TextInput::make('rccm')->label('RCCM')->maxLength(100),
                    TextInput::make('publisher_name')->label('Responsable de publication')->maxLength(255),
                    TextInput::make('hosting_provider')->label('Hébergeur')->maxLength(255),
                    Textarea::make('retention_text')->label('Durée de conservation affichée')->rows(2)->maxLength(1000)->columnSpanFull(),
                    TextInput::make('certification')->label('Certification communiquée')->maxLength(255),
                    TextInput::make('guarantee')->label('Garantie communiquée')->maxLength(255),
                    TextInput::make('response_time')->label('Délai communiqué')->maxLength(255)
                        ->helperText('Ne publier qu’une information vérifiée.'),
                ]),
            Section::make('Photos de la page d’accueil')
                ->description('Aucune photo réelle de l’atelier n’a été fournie. Le visuel illustratif provisoire de l’accueil est explicitement signalé et disparaîtra lorsqu’une vraie photo sera chargée.')
                ->columns(2)
                ->schema([
                    FileUpload::make('homepage_workshop_image')->label('Photo de l’atelier')->image()
                        ->disk('public')->directory('homepage')->visibility('public')->maxSize(5120)
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                        ->helperText('JPG, PNG ou WebP — 5 Mo maximum. Fichier public. Cette photo remplace l’illustration provisoire sur l’accueil.'),
                    TextInput::make('homepage_workshop_alt')->label('Texte alternatif — atelier')->maxLength(255),
                    FileUpload::make('homepage_intervention_image')->label('Photo d’intervention / détail')->image()
                        ->disk('public')->directory('homepage')->visibility('public')->maxSize(5120)
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                        ->helperText('JPG, PNG ou WebP — 5 Mo maximum. Fichier public.'),
                    TextInput::make('homepage_intervention_alt')->label('Texte alternatif — intervention')->maxLength(255),
                    Repeater::make(HomepageGallery::SETTING_KEY)
                        ->label('Galerie de photos')
                        ->schema([
                            FileUpload::make('image')->label('Photo')->image()
                                ->disk('public')->directory(HomepageGallery::DIRECTORY)->visibility('public')->maxSize(5120)
                                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                ->required()
                                ->helperText('JPG, PNG ou WebP — 5 Mo maximum. Fichier public.'),
                            TextInput::make('alt')->label('Texte alternatif')->required()->maxLength(255)
                                ->helperText('Décrivez précisément ce que montre la photo.'),
                            TextInput::make('caption')->label('Légende (facultative)')->maxLength(255),
                        ])
                        ->defaultItems(0)
                        ->maxItems(6)
                        ->reorderable()
                        ->addActionLabel('Ajouter une photo à la galerie')
                        ->columnSpanFull(),
                ]),
        ];

        foreach (PublicCopy::groups() as $group => $fields) {
            $components[] = Section::make($group)
                ->collapsible()
                ->collapsed()
                ->columns(2)
                ->schema(collect($fields)->map(function (array $definition, string $key) {
                    [$label, , $type] = $definition;
                    $field = $type === 'short'
                        ? TextInput::make('copy_' . $key)->label($label)->maxLength(255)
                        : Textarea::make('copy_' . $key)->label($label)->rows(2)->maxLength(1500);

                    return $field;
                })->all());
        }

        return $schema->components($components)->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $keys = [
            'business_name', 'tagline', 'phone', 'whatsapp', 'contact_email', 'address',
            'ifu', 'rccm', 'publisher_name', 'hosting_provider', 'retention_text',
            'certification', 'guarantee', 'response_time',
        ];

        foreach (PublicCopy::groups() as $fields) {
            foreach (array_keys($fields) as $key) {
                $keys[] = 'copy_' . $key;
            }
        }

        foreach ($keys as $key) {
            $value = trim((string) ($data[$key] ?? ''));
            Setting::updateOrCreate(['key' => $key], ['value' => $value === '' ? null : $value]);
        }

        foreach (['homepage_workshop_image', 'homepage_intervention_image'] as $key) {
            $oldPath = Setting::get($key);
            $newPath = $data[$key] ?? null;
            $newPath = is_string($newPath) ? $newPath : null;

            Setting::updateOrCreate(['key' => $key], ['value' => $newPath]);

            if ($oldPath && $oldPath !== $newPath && $this->isStandaloneHomepageImage($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }
        }

        foreach (['homepage_workshop_alt', 'homepage_intervention_alt'] as $key) {
            $value = trim((string) ($data[$key] ?? ''));
            Setting::updateOrCreate(['key' => $key], ['value' => $value === '' ? null : $value]);
        }

        $oldGalleryPaths = HomepageGallery::paths(Setting::get(HomepageGallery::SETTING_KEY));
        $gallery = HomepageGallery::normalize($data[HomepageGallery::SETTING_KEY] ?? []);
        $newGalleryPaths = array_column($gallery, 'image');
        Setting::updateOrCreate(
            ['key' => HomepageGallery::SETTING_KEY],
            ['value' => json_encode($gallery, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]
        );

        foreach (array_diff($oldGalleryPaths, $newGalleryPaths) as $path) {
            Storage::disk('public')->delete($path);
        }

        Notification::make()->title('Modifications enregistrées')->success()->send();
    }

    private function isStandaloneHomepageImage(string $path): bool
    {
        return preg_match('/\Ahomepage\/[A-Za-z0-9][A-Za-z0-9._-]*\z/D', $path) === 1;
    }
}
