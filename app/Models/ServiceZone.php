<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceZone extends Model
{
    protected $fillable = ['name', 'delivery_fee_fcfa', 'sort_order', 'is_active'];
}
