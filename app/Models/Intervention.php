<?php

namespace App\Models;

use App\Invoicing\Numbering;
use Illuminate\Database\Eloquent\Model;

class Intervention extends Model
{
    protected $fillable = ['reference', 'customer_id', 'equipment_id', 'service_request_id', 'technician_id', 'title', 'status', 'scheduled_at', 'started_at', 'finished_at', 'diagnosis', 'work_done', 'signature_path', 'signed_by', 'signed_at', 'contract_visit_id'];
    protected $casts = ['scheduled_at' => 'datetime', 'started_at' => 'datetime', 'finished_at' => 'datetime', 'signed_at' => 'datetime'];

    public const STATUSES = ['planned' => 'Planifiée', 'in_progress' => 'En cours', 'done' => 'Terminée', 'cancelled' => 'Annulée'];

    protected static function booted(): void
    {
        static::creating(function (self $i) {
            $i->reference ??= Numbering::next('intervention');
        });
    }

    public function customer() { return $this->belongsTo(Customer::class); }
    public function equipment() { return $this->belongsTo(Equipment::class); }
    public function technician() { return $this->belongsTo(User::class, 'technician_id'); }
    public function lines() { return $this->hasMany(InterventionLine::class); }
    public function fluidMovements() { return $this->hasMany(FluidMovement::class); }
    public function invoices() { return $this->hasMany(Invoice::class); }
    public function photos() { return $this->hasMany(InterventionPhoto::class); }
}
