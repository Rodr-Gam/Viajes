<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes; // Importante para tu diagrama
use App\Models\Role;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'last_name', // Agregado según tu BD
        'email',
        'password',
        'phone',     // Agregado según tu BD
        'state',     // Agregado según tu BD (active, inactive, banned)
        'role_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function hasRole($role)
    {
        return $this->role && $this->role->name === $role;
    }
}
