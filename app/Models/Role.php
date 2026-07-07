<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    public const ADMIN = 'admin';
    public const CLIENTE = 'cliente';

    /**
     * Los atributos que se pueden asignar masivamente.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * Normaliza nombres de rol para comparar sin depender de mayúsculas
     * ni de variantes en BD (p. ej. "Admin" vs "admin", "Client" vs "cliente").
     */
    public static function normalize(string $name): string
    {
        return match (strtolower(trim($name))) {
            'admin', 'administrador', 'administrator' => self::ADMIN,
            'cliente', 'client', 'customer' => self::CLIENTE,
            default => strtolower(trim($name)),
        };
    }

    public static function findByKey(string $key): ?self
    {
        $normalized = self::normalize($key);

        return self::all()->first(
            fn (self $role) => self::normalize($role->name) === $normalized
        );
    }
}