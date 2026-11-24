<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuoteLine extends Model
{
    protected $fillable = ['quote_id', 'description', 'quantity', 'unit_price_fcfa', 'tax_group'];
}
