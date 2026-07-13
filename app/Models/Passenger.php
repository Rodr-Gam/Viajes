<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Passenger extends Model
{
    public const TYPES = ['adult', 'junior', 'child'];

    protected $fillable = [
        'reservation_id',
        'name',
        'last_name',
        'birth_date',
        'nationality',
        'type',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
        ];
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }
}
