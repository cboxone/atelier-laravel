<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InterventionLine extends Model
{
    protected $fillable = ['intervention_id', 'kind', 'product_id', 'description', 'quantity', 'unit_price_fcfa', 'tax_group'];

    public function product() { return $this->belongsTo(Product::class); }
}
