<?php

namespace App\Support;

class Money
{
    public static function fcfa(int $amount): string
    {
        return number_format($amount, 0, ',', ' ') . ' FCFA';
    }
}
