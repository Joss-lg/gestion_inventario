<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CajaMovimiento extends Model
{
    use HasFactory;

    protected $table = 'caja_movimientos';

    protected $fillable = [
        'user_id',
        'monto_apertura',
        'monto_cierre',
        'fecha_apertura',
        'fecha_cierre',
        'estado',
        'observaciones',
        'metodo_pago',
        'num_referencia',
        'monto_esperado',              // <-- Agregado
        'total_ventas_efectivo',       // <-- Agregado
        'total_ventas_tarjeta',        // <-- Agregado
        'total_ventas_transferencia',  // <-- Agregado
        'total_gastos',                // <-- Agregado
    ];

    // Convertimos los tipos de datos automáticamente al consultar el modelo
    protected $casts = [
        'monto_apertura' => 'decimal:2',
        'monto_cierre' => 'decimal:2',
        'fecha_apertura' => 'datetime',
        'fecha_cierre' => 'datetime',
        'monto_esperado' => 'decimal:2',
        'total_ventas_efectivo' => 'decimal:2',
        'total_ventas_tarjeta' => 'decimal:2',
        'total_ventas_transferencia' => 'decimal:2',
        'total_gastos' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function estaAbierta(): bool
    {
        return $this->estado === 'abierta';
    }
}