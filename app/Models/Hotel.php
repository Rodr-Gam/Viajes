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
        'hgdl_key', 
        'name_supplier',
        'booking_source',
        'provider_cost',
        'observations',
    ];

    /**
     * Obtiene la ciudad a la que pertenece el hotel.
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }
}