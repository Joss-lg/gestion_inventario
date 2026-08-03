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

            // Relaciones con tablas que ya existen (AQUÍ AGREGAMOS ->nullable())
            $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // Guardamos caja_id como campo numérico simple sin FK directa para evitar conflictos de orden
            $table->unsignedBigInteger('caja_id')->nullable();

            // Datos del movimiento
            $table->string('type'); 
            $table->integer('quantity')->default(1); // Opcional: poner un default por si el gasto no manda cantidad
            $table->string('reason');

            // Campos financieros para Venta Directa
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->decimal('monto_recibido', 10, 2)->default(0);
            $table->decimal('cambio', 10, 2)->default(0);

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