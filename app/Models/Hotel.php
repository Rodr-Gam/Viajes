<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// ❌ Eliminamos: use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Hotel extends Model
{
    // ❌ Quitamos SoftDeletes de los traits
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'city_id',
        'address',
        'stars',
        'price_per_night',
        'status',
        'image_path',
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