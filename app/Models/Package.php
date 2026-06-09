<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// ❌ Eliminamos: use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Package extends Model
{
    // ❌ Quitamos "SoftDeletes" de los traits activos
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'user_id', 
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }
}