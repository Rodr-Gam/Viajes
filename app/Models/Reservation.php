<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reservation extends Model
{
   // use SoftDeletes;

    protected $fillable = [
        'user_id', // 
        'package_id',
        'reference_person',
        'reservation_date',
        'departure_date',
        'return_date',
        'reserved_seats',
        'state',
        'observations',
    ];

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function flight()
    {
        return $this->hasOne(Flight::class);
    }
    public function transport()
    {
        return $this->hasOne(Transport::class);
    }
}
