<?php

namespace App\Models;

use App\Invoicing\{InvoiceTotals, Numbering};
use Illuminate\Database\Eloquent\Model;

class Quote extends Model
{
    protected $fillable = ['number', 'customer_id', 'intervention_id', 'status', 'valid_until', 'notes'];
    protected $casts = ['valid_until' => 'date'];

    public const STATUSES = ['draft' => 'Brouillon', 'sent' => 'Envoyé', 'accepted' => 'Accepté', 'refused' => 'Refusé', 'invoiced' => 'Facturé'];

    protected static function booted(): void
    {
        static::creating(function (self $q) {
            $q->number ??= Numbering::next('quote');
        });
    }

    public function customer() { return $this->belongsTo(Customer::class); }
    public function lines() { return $this->hasMany(QuoteLine::class); }

    public function totals(): array
    {
        return InvoiceTotals::compute($this->lines->map->only(['quantity', 'unit_price_fcfa', 'tax_group']), config('emecef.tax_rates'));
    }
}
