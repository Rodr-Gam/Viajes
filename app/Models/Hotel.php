<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Hotel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'hotels';

    protected $fillable = [
        'reservation_id',
        'name',
        'destination',
        'hgdl_key',
        'supplier',
        'booking_source',
        'provider_cost',
        'observations',
    ];

    protected $casts = [
        'provider_cost' => 'decimal:4',
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }
    
    public function roomPrices()
    {
        return $this->hasMany(RoomPrice::class);
    }

    public function scopeByDestination($query, string $destination)
    {
        return $query->where('destination', $destination);
    }

    public function scopeBySupplier($query, string $supplier)
    {
        return $query->where('supplier', $supplier);
    }
}
