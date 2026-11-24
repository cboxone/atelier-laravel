<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    protected $fillable = ['author_name', 'content', 'consent_given', 'is_published', 'given_on'];
}
