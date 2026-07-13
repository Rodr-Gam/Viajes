<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Flight extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'reservation_id',
        'airline_name',
        'destination',
        'flight_schedule',
        'hgdl_key',
        'booking_source',
        'provider_cost',
        'observations',
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }
}
