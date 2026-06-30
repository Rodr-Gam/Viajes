<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RoomPrice extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'room_prices';

    protected $fillable = [
        'hotel_id',
        'occupancy_type',
        'nightly_rate',
        'total_rooms',
    ];

    protected $casts = [
        'nightly_rate'  => 'decimal:4',
        'total_rooms'   => 'integer',
    ];
    
    const OCCUPANCY_TYPES = [
        'single',
        'double',
        'triple',
        'quadruple',
        'suite',
    ];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }
    
    public function scopeAvailable($query)
    {
        return $query->where('total_rooms', '>', 0);
    }
}
