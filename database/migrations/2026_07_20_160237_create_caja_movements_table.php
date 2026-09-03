<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('caja_movimientos', function (Blueprint $table) {
            $table->id();
            
            // Relación con el usuario (quién abre/cierra la caja)
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Montos de dinero base
            $table->decimal('monto_apertura', 10, 2);
            $table->decimal('monto_cierre', 10, 2)->nullable(); // Será null hasta que se cierre

            // === CAMPOS DE PAGO Y REFERENCIA AGREGADOS ===
            $table->enum('metodo_pago', ['efectivo', 'tarjeta', 'transferencia'])->default('efectivo');
            $table->string('num_referencia')->nullable();

            // === CAMPOS TOTALES INTEGRADOS ===
            $table->decimal('monto_esperado', 12, 2)->nullable();
            $table->decimal('total_ventas_efectivo', 12, 2)->default(0);
            $table->decimal('total_ventas_tarjeta', 12, 2)->default(0);
            $table->decimal('total_ventas_transferencia', 12, 2)->default(0);
            $table->decimal('total_gastos', 12, 2)->default(0);

            // Fechas de control
            $table->timestamp('fecha_apertura')->useCurrent();
            $table->timestamp('fecha_cierre')->nullable();

            // Estado del turno de la caja
            $table->enum('estado', ['abierta', 'cerrada'])->default('abierta');
            $table->text('observaciones')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('caja_movimientos');
    }
};