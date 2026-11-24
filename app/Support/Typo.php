<?php

namespace App\Support;

class Typo
{
    // Typographie française : espace insécable avant ? ! : ; pour qu'un signe ne se retrouve jamais seul en début de ligne.
    public static function fr(?string $s): string
    {
        return preg_replace('/ ([?!:;»])/u', "\u{00A0}" . '$1', (string) $s);
    }
}
