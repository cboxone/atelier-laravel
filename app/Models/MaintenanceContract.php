<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaintenanceContract extends Model
{
    protected $fillable = ['customer_id', 'contract_plan_id', 'title', 'starts_on', 'ends_on', 'visits_per_year', 'price_fcfa', 'status', 'notes', 'invoiced_at'];
    protected $casts = ['starts_on' => 'date', 'ends_on' => 'date', 'invoiced_at' => 'datetime'];

    public function customer() { return $this->belongsTo(Customer::class); }
    public function equipments() { return $this->belongsToMany(Equipment::class, 'contract_equipments'); }
    public function visits() { return $this->hasMany(ContractVisit::class)->orderBy('due_on'); }
}
