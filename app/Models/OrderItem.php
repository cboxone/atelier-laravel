<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = ['order_id', 'product_id', 'name', 'unit_price_fcfa', 'quantity', 'line_total_fcfa'];
}
