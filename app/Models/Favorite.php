<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Favorite extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'package_id',
        'hotel_id',
    ];

    // Relación: Un favorito pertenece a un usuario
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relación: Un favorito puede pertenecer a un paquete
    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    // Relación: Un favorito puede pertenecer a un hotel
    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }
}