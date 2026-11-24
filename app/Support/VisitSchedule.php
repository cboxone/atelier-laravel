<?php

namespace App\Support;

// Calcul pur des dates de visite d'un contrat : réparties régulièrement, jamais le premier ni le dernier jour.
class VisitSchedule
{
    /** @return string[] dates « Y-m-d » */
    public static function dates(\DateTimeImmutable $start, \DateTimeImmutable $end, int $visits): array
    {
        if ($visits < 1 || $visits > 52) throw new \InvalidArgumentException('Le nombre de visites doit être compris entre 1 et 52.');
        $days = (int) $start->diff($end)->format('%r%a');
        if ($days < $visits + 1) throw new \InvalidArgumentException('Période trop courte pour ce nombre de visites.');

        $dates = [];
        for ($i = 1; $i <= $visits; $i++) {
            $dates[] = $start->modify('+' . (int) round($i * $days / ($visits + 1)) . ' days')->format('Y-m-d');
        }
        return $dates;
    }
}
