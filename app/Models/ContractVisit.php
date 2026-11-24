<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContractVisit extends Model
{
    protected $fillable = ['maintenance_contract_id', 'due_on', 'status', 'interventions_created_at'];
    protected $casts = ['due_on' => 'date', 'interventions_created_at' => 'datetime'];

    public function contract() { return $this->belongsTo(MaintenanceContract::class, 'maintenance_contract_id'); }
    public function interventions() { return $this->hasMany(Intervention::class); }
}
