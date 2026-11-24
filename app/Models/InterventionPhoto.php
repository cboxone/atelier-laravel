<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InterventionPhoto extends Model
{
    protected $fillable = ['intervention_id', 'uploaded_by', 'path', 'caption'];

    public function intervention() { return $this->belongsTo(Intervention::class); }
}
