<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ServiceRequest extends Model
{
    protected $fillable = ['reference', 'service_id', 'equipment', 'problem', 'district', 'phone', 'preferred_date', 'is_urgent', 'status', 'internal_note'];
    protected $casts = ['is_urgent' => 'boolean', 'preferred_date' => 'date'];

    protected static function booted(): void
    {
        static::creating(function (self $r) {
            if (! $r->reference) {
                do {
                    $ref = 'DI-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6));
                } while (static::where('reference', $ref)->exists());
                $r->reference = $ref;
            }
        });
    }

    public function service() { return $this->belongsTo(Service::class); }
    public function attachments() { return $this->hasMany(ServiceRequestAttachment::class); }
}
