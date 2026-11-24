<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = ['name', 'phone', 'email', 'address', 'ifu'];

    public function equipments() { return $this->hasMany(Equipment::class); }
    public function invoices() { return $this->hasMany(Invoice::class); }
}
