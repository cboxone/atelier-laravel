<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentSequence extends Model
{
    protected $fillable = ['name', 'year', 'last_number'];
}
