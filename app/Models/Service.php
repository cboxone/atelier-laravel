<?php

namespace App\Models;

use App\Support\ServiceVisual;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = ['name', 'slug', 'summary', 'description', 'sort_order', 'is_published', 'icon_key', 'image_path'];

    public function visualIcon(): string
    {
        return ServiceVisual::iconKey($this->icon_key, $this->slug);
    }

    public function visualImagePath(): ?string
    {
        return ServiceVisual::imagePath($this->image_path);
    }
}
