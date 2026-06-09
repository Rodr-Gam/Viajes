<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reservation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'package_id',
        'reference_person',
        'reservation_date',
        'departure_date',
        'return_date',
        'reserved_seats',
        'state',
        'observations',
    ];

    public function package()
    {
        return $this->belongsTo(Package::class);
    }
}
