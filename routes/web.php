<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CajaController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
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
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

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
     * Módulo Administrativo: Administración de Usuarios (Exclusivo manage-users)
     *--------------------------------------------------------------------------
     */
    Route::middleware('permission:manage-users')->group(function () {
        Route::get('/usuarios', [UserController::class, 'index'])->name('users.index');
        Route::post('/usuarios', [UserController::class, 'store'])->name('users.store');
        Route::put('/usuarios/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/usuarios/{user}', [UserController::class, 'destroy'])->name('users.destroy');

        // 🛡️ Historial de Turnos y Cajas (Separado y exclusivo para el Administrador)
        Route::get('/caja/historial', [CajaController::class, 'historial'])->name('caja.historial');
        Route::get('/caja/historial/{id}', [CajaController::class, 'detallesHistorial'])->name('caja.historial.detalles');
    });

    /**
     *--------------------------------------------------------------------------
     * 🔒 GRUPO DE RUTAS OPERATIVAS (REQUIEREN CAJA ABIERTA)
     *--------------------------------------------------------------------------
     */
    Route::middleware('caja.abierta')->group(function () {

        // 🛒 Punto de Venta (POS) - Requiere tener la caja abierta para vender
        Route::get('/caja/pos', [CajaController::class, 'pos'])->name('caja.pos');
        Route::post('/caja/venta', [CajaController::class, 'registrarVenta'])->name('caja.venta');

        // 💸 Registro de Gastos / Salidas de Caja
        Route::post('/caja/gastos', [CajaController::class, 'storeGasto'])->name('caja.gastos.store');

        // Categorías de Productos y Catálogo
        Route::middleware('permission:manage-categories')->group(function () {
            Route::get('/categorias', [CategoryController::class, 'index'])->name('categories.index');
            Route::post('/categorias', [CategoryController::class, 'store'])->name('categories.store');
            Route::put('/categorias/{category}', [CategoryController::class, 'update'])->name('categories.update');
            Route::get('/catalogo', [CatalogController::class, 'index'])->name('catalogo.index');
        });

        // Productos del Catálogo
        Route::middleware('permission:manage-products')->group(function () {
            Route::get('/productos', [ProductController::class, 'index'])->name('products.index');
            Route::post('/productos', [ProductController::class, 'store'])->name('products.store');
            Route::put('/productos/{product}', [ProductController::class, 'update'])->name('products.update');
        });

        // Eliminaciones Críticas del Catálogo
        Route::middleware('permission:delete-catalog')->group(function () {
            Route::delete('/categorias/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
            Route::delete('/productos/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
        });

        // Control y Movimientos de Inventario
        Route::middleware('permission:register-movements')->group(function () {
            Route::get('/stock', [StockController::class, 'index'])->name('stock.index');
            Route::post('/stock', [StockController::class, 'store'])->name('stock.store');
        });

    });

});