<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackageImage extends Model
{
    protected $fillable = ['package_id', 'user_id', 'image_name', 'url'];

    // Relación inversa: Una imagen pertenece a un paquete
    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }
}