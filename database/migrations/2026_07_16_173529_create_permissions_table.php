<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();  // Ej: 'Visualizar valor del inventario'
            $table->string('slug')->unique();  // Ej: 'view-inventory-value'
            $table->string('module');          // Ej: 'INVENTARIO', 'CATÁLOGO'
            $table->timestamps();
        });

        // Los permisos se gestionan por rol (no por usuario individual):
        // cada rol tiene muchos permisos, y cada permiso puede pertenecer a muchos roles.
        Schema::create('permission_role', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            $table->foreignId('permission_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['role_id', 'permission_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permission_role');
        Schema::dropIfExists('permissions');
    }
};
