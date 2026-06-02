<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    protected $fillable = [
        'package_id',
        'reference_person',
        'reservation_date',
        'departure_date',
        'return_date',
        'state',
        'observations',
    ];

    public function package()
    {
        return $this->belongsTo(Package::class);
    }
}
