<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Equipment extends Model
{
    protected $fillable = ['customer_id', 'label', 'type', 'brand', 'model', 'serial_number', 'fluid_type'];
    protected $table = 'equipments';

    public function customer() { return $this->belongsTo(Customer::class); }
    public function fluidMovements() { return $this->hasMany(FluidMovement::class); }
}
