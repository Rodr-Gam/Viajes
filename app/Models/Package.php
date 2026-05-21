<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Package extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'city_id',
        'duration',
        'departure_date',
        'stock',
        'price_adult',
        'price_junior',
        'price_child',
        'image_path',
        'status',
    ];

    /**
     * Obtiene la ciudad (destino) a la que pertenece el paquete.
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }
}