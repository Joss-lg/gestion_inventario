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
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();

            // Relaciones con tablas existentes
            // 🛡️ 'restrict' (no 'cascade'): si un producto ya tiene historial de
            // movimientos (ventas, entradas, salidas), no se permite borrarlo,
            // para no perder el rastro contable de cortes de caja ya cerrados.
            $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('restrict');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // Guardamos caja_id como campo numérico simple sin FK directa
            $table->unsignedBigInteger('caja_id')->nullable();

            // Datos del movimiento
            $table->string('type'); 
            $table->integer('quantity')->default(1);
            $table->string('reason');

            // === CAMPOS INTEGRADOS DE OTRAS MIGRACIONES ===
            $table->string('num_referencia')->nullable();
            $table->text('notes')->nullable();

            // Campos financieros para Venta Directa
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->decimal('monto_recibido', 10, 2)->default(0);
            $table->decimal('cambio', 10, 2)->default(0); // Corregido: $table en lugar de $table0

            $table->timestamp('date')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
    }
};