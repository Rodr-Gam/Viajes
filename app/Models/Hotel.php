<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Hotel extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'city_id',
        'address',
        'stars',
        'price_per_night',
        'status',
        'image_path', // Agregado aquí para que permita guardar la foto
        'hgdl_key', 
        'name_supplier',
        'booking_source',
        'provider_cost',
        'observations',
    ];

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }
}