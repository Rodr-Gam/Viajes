<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transport extends Model
{
    protected $fillable = [
        'reservation_id',
        'company',
        'hgdl_key',
        'destination',
        'horary',
        'supplier',
        'provider_cost',
        'booking_source',
        'observations',
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }
}
