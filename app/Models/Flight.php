<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Flight extends Model
{
    protected $fillable = [
        'reservation_id',
        'airline_name',
        'destination',
        'flight_schedule',
        'hgdl_key',
        'booking_source',
        'provider_cost',
        'observations,'
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }
}
