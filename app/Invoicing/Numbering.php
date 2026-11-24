<?php

namespace App\Invoicing;

use App\Models\DocumentSequence;
use Illuminate\Database\UniqueConstraintViolationException;

// Numérotation continue, sans trou, par année. À appeler DANS la transaction qui enregistre le document :
// si elle échoue, le numéro n'est pas consommé.
class Numbering
{
    private const PREFIX = ['invoice' => 'F', 'credit_note' => 'AV', 'quote' => 'D', 'intervention' => 'I'];

    public static function next(string $name): string
    {
        $year = (int) now()->format('Y');
        $row = self::lock($name, $year);
        if (! $row) {
            try {
                DocumentSequence::create(['name' => $name, 'year' => $year, 'last_number' => 0]);
            } catch (UniqueConstraintViolationException $e) {
                // créée au même instant par une autre requête : on la relit
            }
            $row = self::lock($name, $year);
        }
        $row->last_number++;
        $row->save();

        return sprintf('%s-%d-%05d', self::PREFIX[$name], $year, $row->last_number);
    }

    private static function lock(string $name, int $year): ?DocumentSequence
    {
        return DocumentSequence::where('name', $name)->where('year', $year)->lockForUpdate()->first();
    }
}
