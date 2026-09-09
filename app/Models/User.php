<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed', // Laravel hashea la contraseña automáticamente al asignar 'password'
        'is_active' => 'boolean',
    ];

    /**
     * Relación con el rol del usuario.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Relación: Un usuario tiene muchos turnos de caja.
     */
    public function cajaMovimientos(): HasMany
    {
        return $this->hasMany(CajaMovimiento::class);
    }

    /**
     * Devuelve la caja que el usuario tiene abierta actualmente (o null si está cerrada).
     */
    public function cajaActiva()
    {
        return $this->cajaMovimientos()->where('estado', 'abierta')->first();
    }

    /**
     * Comprobar si el usuario tiene un permiso específico por su slug.
     * Los permisos ahora dependen del rol asignado, no del usuario individual.
     */
    public function hasPermissionTo(string $permissionSlug): bool
    {
        if (! $this->role) {
            return false;
        }

        return $this->role->hasPermissionTo($permissionSlug);
    }

    /**
     * Comprobar de forma rápida si el usuario tiene el rol de Administrador.
     */
    public function isAdmin(): bool
    {
        return $this->role && $this->role->name === 'Administrador';
    }

    /**
     * Comprobación unificada de permisos (ID 1 tiene pase maestro).
     */
    public function hasPermission(string $permissionSlug): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return $this->hasPermissionTo($permissionSlug);
    }
}
