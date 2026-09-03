<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'user_id',
        'caja_id',
        'type',
        'quantity',
        'reason',
        'unit_price',
        'total',
        'monto_recibido',
        'cambio',
        'date',
        'notes', // <-- ¡AGREGADO AQUÍ para evitar errores de asignación masiva!
    ];

    /**
     * Accessor para extraer dinámicamente el método de pago desde las notas.
     */
    public function getMetodoPagoAttribute()
    {
        $notes = strtolower($this->notes ?? '');
        
        if (str_contains($notes, 'tarjeta')) {
            return 'Tarjeta';
        }
        if (str_contains($notes, 'transferencia')) {
            return 'Transferencia';
        }
        
        return 'Efectivo';
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function caja()
    {
        return $this->belongsTo(CajaMovimiento::class, 'caja_id');
    }
}