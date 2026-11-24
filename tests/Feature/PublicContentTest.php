<?php

namespace Tests\Feature;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_keeps_its_photo_placeholder_when_a_saved_file_is_missing(): void
    {
        Setting::create(['key' => 'homepage_workshop_image', 'value' => 'homepage/photo-absente.jpg']);

        $this->get('/')
            ->assertOk()
            ->assertSee('Photo de l’atelier à ajouter')
            ->assertDontSee('storage/homepage/photo-absente.jpg');
    }

    public function test_homepage_uses_a_saved_public_workshop_image_and_managed_alt_text(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put(
            'homepage/atelier.png',
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jXioAAAAASUVORK5CYII=')
        );
        Setting::create(['key' => 'homepage_workshop_image', 'value' => 'homepage/atelier.png']);
        Setting::create(['key' => 'homepage_workshop_alt', 'value' => 'Vue intérieure de l’atelier']);

        $this->get('/')
            ->assertOk()
            ->assertSee('src="' . asset('storage/homepage/atelier.png') . '"', false)
            ->assertSee('alt="Vue intérieure de l’atelier"', false)
            ->assertDontSee('Photo de l’atelier à ajouter');
    }

    public function test_public_page_copy_is_editable_and_escaped_as_plain_text(): void
    {
        Setting::create(['key' => 'copy_home_services_heading', 'value' => '<strong>Nos prestations</strong>']);

        $this->get('/')
            ->assertOk()
            ->assertSee('&lt;strong&gt;Nos prestations&lt;/strong&gt;', false)
            ->assertDontSee('<strong>Nos prestations</strong>', false);
    }
}
