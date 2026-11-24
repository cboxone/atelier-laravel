<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceRequestAttachment extends Model
{
    protected $fillable = ['service_request_id', 'path', 'original_name', 'mime_type', 'size_bytes'];
}
