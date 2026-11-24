<?php

namespace App\Support;

// Vérifie la signature envoyée par le navigateur (image PNG en base64) avant de la stocker.
class SignatureImage
{
    public static function decode(string $dataUrl, int $maxBytes = 300000): string
    {
        if (! preg_match('#^data:image/png;base64,([A-Za-z0-9+/]+={0,2})$#', $dataUrl, $m)) {
            throw new \InvalidArgumentException('Signature invalide.');
        }
        $bin = base64_decode($m[1], true);
        if ($bin === false || $bin === '' || strlen($bin) > $maxBytes) throw new \InvalidArgumentException('Signature illisible ou trop lourde.');
        if (! str_starts_with($bin, "\x89PNG\r\n\x1a\n")) throw new \InvalidArgumentException('La signature n’est pas une image PNG.');

        $info = @getimagesizefromstring($bin);
        if (! $info || $info['mime'] !== 'image/png' || $info[0] > 2000 || $info[1] > 1000 || $info[0] < 20 || $info[1] < 20) {
            throw new \InvalidArgumentException('Dimensions de signature invalides.');
        }
        return $bin;
    }
}
