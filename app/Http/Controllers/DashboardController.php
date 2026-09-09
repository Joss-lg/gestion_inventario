<?php

namespace App\Http\Controllers;

use App\Models\InventoryMovement;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        // 🛑 CONTROL DE ACCESO ABSOLUTO:
        // Si NO tiene el permiso para ver métricas/reportes, lo redirigimos de
        // inmediato al Control de Caja SIN MOSTRAR ALERTAS.
        $esAdmin = $user->isAdmin();

        if (! $esAdmin && ! $user->hasPermission('view-reports')) {
            return redirect()->route('caja.index');
        }

        // --- FILTRO DE MES PARA LA GRÁFICA Y ROTACIÓN ---
        $requestedMonth = $request->input('month');
        $selectedMonth = is_string($requestedMonth) && preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $requestedMonth)
            ? $requestedMonth
            : now()->format('Y-m');
        $startOfMonth = Carbon::parse($selectedMonth.'-01')->startOfMonth();
        $endOfMonth = Carbon::parse($selectedMonth.'-01')->endOfMonth();

        // --- MÉTRICAS REALES DESDE LA BASE DE DATOS (SOLO ADMINISTRADOR) ---

        // A. Total de productos registrados en el catálogo
        $totalProductsCount = Product::count();

        // B. Total de SKUs únicos y activos
        $activeSkusCount = Product::whereNotNull('sku')
            ->where('sku', '!=', '')
            ->distinct('sku')
            ->count();

        // C. Valor económico total del almacén
        $totalInventoryValue = Product::all()->sum(function ($product) {
            return $product->price * $product->stock;
        });

        // C-2. Dinero total de las ventas en el mes seleccionado
        $totalSalesMoney = InventoryMovement::where('type', 'salida')
            ->where('reason', 'like', 'Venta directa%')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->get()
            ->sum(function ($movement) {
                return $movement->total ?? (($movement->price ?? 0) * ($movement->quantity ?? 0));
            });

        // D. Colección de productos con Stock Crítico (Menor o igual a 5 piezas)
        $lowStockProducts = Product::where('stock', '<=', 5)->get();

        // E. Actividad Reciente del Inventario
        $recentActivities = InventoryMovement::with(['product', 'user'])
            ->latest()
            ->take(10)
            ->get();

        $topProducts = InventoryMovement::select('product_id', DB::raw('SUM(quantity) as total_quantity'))
            ->where('type', 'salida')
            ->where('reason', 'like', 'Venta directa%')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->groupBy('product_id')
            ->orderByDesc('total_quantity')
            ->take(5)
            ->with('product')
            ->get();

        $mostSoldProduct = $topProducts->first();

        // Preparamos los arrays que leerá la vista y el JS modular
        $chartLabels = $topProducts->pluck('product.name')->toArray();
        $chartData = $topProducts->pluck('total_quantity')->toArray();

        return view('dashboard', compact(
            'totalProductsCount',
            'activeSkusCount',
            'totalInventoryValue',
            'totalSalesMoney',
            'lowStockProducts',
            'recentActivities',
            'chartLabels',
            'chartData',
            'selectedMonth',
            'mostSoldProduct'
        ));
    }
}