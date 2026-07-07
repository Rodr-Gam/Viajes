<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transport extends Model
{
    use SoftDeletes;

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
