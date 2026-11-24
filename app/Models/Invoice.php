<?php

namespace App\Models;

use App\Invoicing\InvoiceTotals;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = ['number', 'type', 'customer_id', 'quote_id', 'intervention_id', 'original_invoice_id', 'status', 'normalized',
        'total_fcfa', 'tax_fcfa', 'issued_at', 'emecef_uid', 'emecef_code', 'emecef_nim', 'emecef_counters', 'emecef_datetime', 'emecef_qr', 'error'];
    protected $casts = ['normalized' => 'boolean', 'issued_at' => 'datetime', 'emecef_datetime' => 'datetime'];

    public const STATUSES = ['draft' => 'Brouillon', 'pending' => 'En cours d’émission', 'issued' => 'Émise', 'failed' => 'Échec d’émission'];

    protected static function booted(): void
    {
        // Une facture numérotée est un document comptable : on ne la supprime pas, on émet un avoir.
        static::deleting(function (self $i) {
            if ($i->number) throw new \LogicException('Une facture numérotée ne se supprime pas : émettez un avoir.');
        });
        // Une facture émise ne change plus.
        static::updating(function (self $i) {
            if ($i->getOriginal('status') === 'issued') throw new \LogicException('Une facture émise ne se modifie pas.');
        });
    }

    public function customer() { return $this->belongsTo(Customer::class); }
    public function lines() { return $this->hasMany(InvoiceLine::class); }
    public function payments() { return $this->hasMany(InvoicePayment::class); }
    public function original() { return $this->belongsTo(self::class, 'original_invoice_id'); }
    public function credits() { return $this->hasMany(self::class, 'original_invoice_id'); }

    public function getPaidFcfaAttribute(): int { return (int) $this->payments->sum('amount_fcfa'); }
    public function getBalanceFcfaAttribute(): int { return $this->total_fcfa - $this->paid_fcfa; }

    public function totals(): array
    {
        return InvoiceTotals::compute($this->lines->map->only(['quantity', 'unit_price_fcfa', 'tax_group']), config('emecef.tax_rates'));
    }
}
