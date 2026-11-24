<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoicePayment extends Model
{
    protected $fillable = ['invoice_id', 'method', 'amount_fcfa', 'reference', 'paid_at'];
    protected $casts = ['paid_at' => 'datetime'];

    public const METHODS = ['cash' => 'Espèces', 'mobile_money' => 'Mobile Money', 'transfer' => 'Virement', 'card' => 'Carte bancaire', 'cheque' => 'Chèque', 'other' => 'Autre'];
}
