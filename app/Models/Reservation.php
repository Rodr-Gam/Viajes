<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reservation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
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
    public function hotel()
    {
        return $this->hasOne(Hotel::class);
    }

    //Para ver el detalle completo, archivado o no
    public function flightWithTrashed()
    {
        return $this->hasOne(Flight::class)->withTrashed();
    }

    public function hotelWithTrashed()
    {
        return $this->hasOne(Hotel::class)->withTrashed();
    }

    public function transportWithTrashed()
    {
        return $this->hasOne(Transport::class)->withTrashed();
    }

    //Hooks para propagar el archivado y restauración a vuelos, transporte y hotel
    protected static function booted(): void
    {
        static::deleting(function (Reservation $reservation) {
            $reservation->flight?->delete();
            $reservation->hotel?->delete();
            $reservation->transport?->delete();
        });

        static::restoring(function (Reservation $reservation) {
            $reservation->flight()->withTrashed()->first()?->restore();
            $reservation->hotel()->withTrashed()->first()?->restore();
            $reservation->transport()->withTrashed()->first()?->restore();
        });
    }
}
