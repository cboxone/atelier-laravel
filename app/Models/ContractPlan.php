<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContractPlan extends Model
{
    protected $fillable = ['name', 'description', 'price_fcfa', 'visits_per_year', 'sort_order', 'is_published'];
    protected $casts = ['is_published' => 'boolean'];

    public function getPriceLabelAttribute(): string
    {
        return $this->price_fcfa === null ? 'Sur devis' : number_format($this->price_fcfa, 0, ',', ' ') . ' FCFA / an';
    }
}
