<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class HomepageGallery
{
    public const SETTING_KEY = 'homepage_gallery';

    public const DIRECTORY = 'homepage/gallery';

    public static function decode(?string $value, bool $requireExistingFiles = false): array
    {
        $rows = json_decode($value ?? '', true);

        return is_array($rows) ? self::normalize($rows, $requireExistingFiles) : [];
    }

    public static function normalize(mixed $rows, bool $requireExistingFiles = false): array
    {
        if (! is_array($rows)) {
            return [];
        }

        $items = [];

        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }

            $path = $row['image'] ?? null;
            if (is_array($path)) {
                $path = array_values($path)[0] ?? null;
            }
            $alt = $row['alt'] ?? null;
            $caption = $row['caption'] ?? null;

            if (! self::isSafePath($path) || ! is_string($alt)) {
                continue;
            }

            $alt = trim($alt);
            $caption = is_string($caption) ? trim($caption) : '';

            if ($alt === '' || mb_strlen($alt) > 255 || mb_strlen($caption) > 255) {
                continue;
            }

            if ($requireExistingFiles && ! Storage::disk('public')->exists($path)) {
                continue;
            }

            $items[] = array_filter([
                'image' => $path,
                'alt' => $alt,
                'caption' => $caption !== '' ? $caption : null,
            ], fn ($value) => $value !== null);

            if (count($items) === 6) {
                break;
            }
        }

        return $items;
    }

    public static function paths(?string $value): array
    {
        $rows = json_decode($value ?? '', true);

        if (! is_array($rows)) {
            return [];
        }

        $paths = [];
        foreach ($rows as $row) {
            $path = is_array($row) ? ($row['image'] ?? null) : null;

            if (self::isSafePath($path)) {
                $paths[] = $path;
            }
        }

        return array_values(array_unique($paths));
    }

    private static function isSafePath(mixed $path): bool
    {
        if (! is_string($path) || ! str_starts_with($path, self::DIRECTORY . '/')) {
            return false;
        }

        $relativePath = substr($path, strlen(self::DIRECTORY) + 1);

        return preg_match('/\A[A-Za-z0-9][A-Za-z0-9._\/-]*\z/D', $relativePath) === 1
            && preg_match('~(?:^|/)\.{1,2}(?:/|$)|//|/$~', $relativePath) !== 1;
    }
}
