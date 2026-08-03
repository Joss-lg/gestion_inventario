<?php

namespace App\Http\Controllers;

use App\Models\InventoryMovement;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class StockController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $products = Product::all();

        // Construimos la consulta base
        $query = InventoryMovement::with(['product', 'user'])->latest();

        // Si el usuario no es admin (o no tiene permiso 'manage-users'), solo ve sus movimientos
        if (Gate::denies('manage-users')) {
            $query->where('user_id', $user->id);
        }

        $movements = $query->get();

        // Calculamos métricas basadas únicamente en los registros obtenidos
        $totalMovements = $movements->count();
        $totalEntradas = $movements->where('type', 'entrada')->count();
        $totalSalidas = $movements->where('type', 'salida')->count();

        return view('stock.index', compact(
            'products',
            'movements',
            'totalMovements',
            'totalEntradas',
            'totalSalidas'
        ));
    }

    public function store(Request $request)
    {
        // 1. Bloqueo de seguridad: No se permiten ventas desde el módulo de inventario
        if ($request->reason === 'Venta directa') {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Las ventas únicamente deben realizarse desde el módulo de Punto de Venta (POS).');
        }

        // 2. Validamos únicamente los datos necesarios para un movimiento de inventario
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'type' => 'required|in:entrada,salida',
            'reason' => 'required|string|max:255',
            'notes' => 'nullable|string|max:500',
        ]);

        $user = auth()->user();

        try {
            DB::transaction(function () use ($request, $user) {
                // 3. Obtenemos el producto con bloqueo para evitar inconsistencias
                $product = Product::lockForUpdate()->findOrFail($request->product_id);
                $cantidad = (int) $request->quantity;

                // 4. Actualizamos el stock y validamos existencias disponibles
                if ($request->type === 'salida') {
                    if ($product->stock < $cantidad) {
                        throw new \Exception("Stock insuficiente. Stock actual disponible: {$product->stock} pzas.");
                    }
                    $product->stock -= $cantidad;
                } else {
                    $product->stock += $cantidad;
                }
                $product->save();

                // 5. Guardamos el movimiento de inventario puro
                InventoryMovement::create([
                    'product_id' => $product->id,
                    'user_id' => $user->id,
                    'caja_id' => null, // Los movimientos manuales de inventario no afectan la caja activa
                    'type' => $request->type,
                    'quantity' => $cantidad,
                    'reason' => $request->reason,
                    'unit_price' => $product->price, // Se guarda como referencia
                    'total' => 0, // No genera monto económico en caja
                    'notes' => $request->notes ?? null,
                    'date' => now(),
                ]);
            });

            return redirect()->route('stock.index')->with('success', 'Movimiento de inventario registrado correctamente.');

        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }
}