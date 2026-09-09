<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CajaController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// ==========================================
// RUTAS PÚBLICAS / INVITADOS
// ==========================================
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');
});

// ==========================================
// RUTAS PROTEGIDAS (SOLO USUARIOS AUTENTICADOS)
// ==========================================
Route::middleware('auth')->group(function () {

    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/', [DashboardController::class, 'index'])
        ->middleware('caja.abierta')
        ->name('dashboard');

    // 🟢 RUTA DE IMAGEN LIBRE DE RESTRICCIÓN DE CAJA (Evita el error 403)
    Route::get('/productos/imagen/{path}', [ProductController::class, 'showImage'])->where('path', '.*')->name('products.image');

    /**
     *--------------------------------------------------------------------------
     * Módulo Operativo: Control de Caja (Aperturas / Cierres del Turno Actual)
     *--------------------------------------------------------------------------
     */
    Route::get('/caja', [CajaController::class, 'index'])->name('caja.index');
    Route::post('/caja/abrir', [CajaController::class, 'abrir'])->name('caja.abrir');
    Route::post('/caja/cerrar', [CajaController::class, 'cerrar'])->name('caja.cerrar');

    /**
     *--------------------------------------------------------------------------
     * Módulo Administrativo: Usuarios (permiso independiente por acción)
     *--------------------------------------------------------------------------
     */
    Route::get('/usuarios', [UserController::class, 'index'])->middleware('permission:view-users')->name('users.index');
    Route::post('/usuarios', [UserController::class, 'store'])->middleware('permission:create-users')->name('users.store');
    Route::put('/usuarios/{user}', [UserController::class, 'update'])->middleware('permission:edit-users')->name('users.update');
    Route::delete('/usuarios/{user}', [UserController::class, 'destroy'])->middleware('permission:delete-users')->name('users.destroy');

    /**
     *--------------------------------------------------------------------------
     * 🟢 Roles y Permisos (vista independiente de gestión de usuarios)
     *--------------------------------------------------------------------------
     */
    Route::get('/roles', [RoleController::class, 'index'])->middleware('permission:view-roles')->name('roles.index');
    Route::post('/roles', [RoleController::class, 'store'])->middleware('permission:create-roles')->name('roles.store');
    Route::put('/roles/{role}', [RoleController::class, 'update'])->middleware('permission:edit-roles')->name('roles.update');
    Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->middleware('permission:delete-roles')->name('roles.destroy');

    /**
     *--------------------------------------------------------------------------
     * 🛡️ Historial de Turnos y Cajas (Soporta ambas estructuras de URL para evitar 404)
     *--------------------------------------------------------------------------
     */
    Route::middleware('permission:view-caja-historial')->group(function () {
        Route::get('/caja/historial', [CajaController::class, 'historial'])->name('caja.historial');
        Route::get('/caja/historial/{id}', [CajaController::class, 'detallesHistorial'])->name('caja.historial.detalles');
        Route::get('/caja/historial/{id}/detalles', [CajaController::class, 'detallesHistorial']);
    });

    /**
     *--------------------------------------------------------------------------
     * 📦 GESTIÓN DE INVENTARIO (LIBRE — NO requiere caja abierta)
     *--------------------------------------------------------------------------
     */

    // Catálogo de Consulta
    Route::middleware('permission:view-products|view-categories')->group(function () {
        Route::get('/catalogo', [CatalogController::class, 'index'])->name('catalogo.index');
    });

    // Categorías de Productos (permiso independiente por acción)
    Route::get('/categorias', [CategoryController::class, 'index'])->middleware('permission:view-categories')->name('categories.index');
    Route::post('/categorias', [CategoryController::class, 'store'])->middleware('permission:create-categories')->name('categories.store');
    Route::put('/categorias/{category}', [CategoryController::class, 'update'])->middleware('permission:edit-categories')->name('categories.update');
    Route::delete('/categorias/{category}', [CategoryController::class, 'destroy'])->middleware('permission:delete-categories')->name('categories.destroy');

    // Productos del Catálogo (permiso independiente por acción)
    Route::get('/productos', [ProductController::class, 'index'])->middleware('permission:view-products')->name('products.index');
    Route::post('/productos', [ProductController::class, 'store'])->middleware('permission:create-products')->name('products.store');
    Route::put('/productos/{product}', [ProductController::class, 'update'])->middleware('permission:edit-products')->name('products.update');
    Route::delete('/productos/{product}', [ProductController::class, 'destroy'])->middleware('permission:delete-products')->name('products.destroy');

    // Control y Movimientos de Inventario
    Route::get('/stock', [StockController::class, 'index'])->middleware('permission:view-stock')->name('stock.index');
    Route::post('/stock', [StockController::class, 'store'])->middleware('permission:create-stock')->name('stock.store');

    /**
     *--------------------------------------------------------------------------
     * 🔒 GRUPO DE RUTAS OPERATIVAS (REQUIEREN CAJA ABIERTA — ni con URL directa)
     *--------------------------------------------------------------------------
     */
    Route::middleware(['permission:access-pos', 'caja.abierta'])->group(function () {

        // 🛒 Punto de Venta (POS) - Requiere tener la caja abierta para vender
        Route::get('/caja/pos', [CajaController::class, 'pos'])->name('caja.pos');
        Route::post('/caja/venta', [CajaController::class, 'registrarVenta'])->name('caja.venta');

        // 💸 Registro de Gastos / Salidas de Caja
        Route::post('/caja/gastos', [CajaController::class, 'storeGasto'])->name('caja.gastos.store');

    });

});
