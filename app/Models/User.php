<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Role;
use App\Models\Package;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'last_name', 
        'email',
        'password',
        'phone',     
        'state',     
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

    /**
     * Obtiene el rol asignado al usuario.
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * 📦 NUEVO: Obtiene todos los paquetes turísticos creados por este usuario.
     */
    public function packages()
    {
        return $this->hasMany(Package::class);
    }

    /**
     * Comprueba si el usuario tiene un rol específico.
     */
    public function hasRole($role)
    {
        return $this->role && $this->role->name === $role;
    }
}