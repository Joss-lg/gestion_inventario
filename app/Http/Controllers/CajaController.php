<?php

namespace App\Http\Controllers;

use App\Models\CajaMovimiento;
use App\Models\Category;
use App\Models\InventoryMovement;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CajaController extends Controller
{
    /**
     * Muestra la vista operativa del módulo de caja (Apertura / Corte del usuario actual).
     */
    public function index()
    {
        $user = auth()->user();

        // Buscar si el usuario actual tiene una caja abierta
        $cajaActiva = CajaMovimiento::where('user_id', $user->id)
            ->where('estado', 'abierta')
            ->first();

        $totalVentasEfectivo = 0;
        $totalGastos = 0;
        $ventasTurno = collect();
        $gastosTurno = collect();

        // Si la caja está abierta, obtenemos los movimientos ligados a esta caja
        if ($cajaActiva) {
            // Ventas directas registradas en este turno de caja
            $ventasTurno = InventoryMovement::with(['product', 'user'])
                ->where('caja_id', $cajaActiva->id)
                ->where('type', 'salida')
                ->where('reason', 'Venta directa')
                ->latest()
                ->get();

            // Suma del total de las ventas registradas
            $totalVentasEfectivo = $ventasTurno->sum('total');

            // Gastos o salidas adicionales de efectivo en este turno
            $gastosTurno = InventoryMovement::with(['product', 'user'])
                ->where('caja_id', $cajaActiva->id)
                ->where('type', 'salida')
                ->where('reason', '!=', 'Venta directa')
                ->latest()
                ->get();

            $totalGastos = $gastosTurno->sum('total');
        }

        return view('caja.index', compact(
            'cajaActiva',
            'ventasTurno',
            'totalVentasEfectivo',
            'gastosTurno',
            'totalGastos'
        ));
    }

    /**
     * Muestra la interfaz del Punto de Venta (POS).
     */
    public function pos()
    {
        $user = auth()->user();

        // Obtener la caja activa del usuario
        $cajaActiva = CajaMovimiento::where('user_id', $user->id)
            ->where('estado', 'abierta')
            ->first();

        // Obtener productos disponibles
        $products = Product::with('category')
            ->where('stock', '>', 0)
            ->orderBy('name', 'asc')
            ->get();

        $categories = Category::orderBy('name', 'asc')->get();

        return view('caja.pos', compact('products', 'categories', 'cajaActiva'));
    }

    /**
     * Procesa y registra la venta desde el Punto de Venta.
     */
    public function registrarVenta(Request $request)
    {
        $user = auth()->user();

        // 1. Verificar sesión de caja activa
        $cajaActiva = CajaMovimiento::where('user_id', $user->id)
            ->where('estado', 'abierta')
            ->first();

        if (! $cajaActiva) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes una caja abierta para realizar ventas.',
            ], 422);
        }

        // 2. Validación de los datos del request
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'monto_recibido' => 'required|numeric|min:0',
            'metodo_pago' => 'required|in:efectivo,tarjeta,transferencia',
        ]);

        try {
            DB::beginTransaction();

            $totalVenta = 0;
            $itemsDetalle = [];

            // 3. Validar stock y preparar ítems
            foreach ($request->items as $item) {
                $product = Product::lockForUpdate()->find($item['id']);

                if ($product->stock < $item['quantity']) {
                    throw new \Exception("Stock insuficiente para: {$product->name}");
                }

                $subtotal = $product->price * $item['quantity'];
                $totalVenta += $subtotal;

                $itemsDetalle[] = [
                    'product' => $product,
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                    'subtotal' => $subtotal,
                ];
            }

            // Validar pago en efectivo
            if ($request->metodo_pago === 'efectivo' && $request->monto_recibido < $totalVenta) {
                throw new \Exception('El monto recibido es menor al total de la venta.');
            }

            // Calcular montos globales para efectivo u otros métodos
            $montoRecibidoGlobal = $request->metodo_pago === 'efectivo' ? $request->monto_recibido : $totalVenta;
            $cambioGlobal = $request->metodo_pago === 'efectivo' ? max(0, $request->monto_recibido - $totalVenta) : 0;

            // 4. Descontar stock y registrar movimientos de inventario
            foreach ($itemsDetalle as $detalle) {
                $detalle['product']->decrement('stock', $detalle['quantity']);

                // Registrar el movimiento de inventario ligado a la caja con los datos de dinero
                InventoryMovement::create([
                    'product_id' => $detalle['product']->id,
                    'user_id' => $user->id,
                    'caja_id' => $cajaActiva->id,
                    'type' => 'salida',
                    'quantity' => $detalle['quantity'],
                    'unit_price' => $detalle['price'],
                    'total' => $detalle['subtotal'],
                    'monto_recibido' => $montoRecibidoGlobal,
                    'cambio' => $cambioGlobal,
                    'reason' => 'Venta directa',
                    'notes' => 'Venta POS - Método: '.$request->metodo_pago,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Venta completada con éxito.',
                'total' => $totalVenta,
                'cambio' => $cambioGlobal,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Registra un gasto o salida de efectivo en la caja activa.
     */
    public function storeGasto(Request $request)
    {
        $user = auth()->user();

        $cajaActiva = CajaMovimiento::where('user_id', $user->id)
            ->where('estado', 'abierta')
            ->first();

        if (! $cajaActiva) {
            if ($request->wantsJson()) {
                return response()->json(['status' => 'error', 'message' => 'No tienes una caja abierta.'], 422);
            }
            return redirect()->back()->with('error', 'No tienes una caja abierta.');
        }

        $request->validate([
            'concepto' => 'required|string|max:255',
            'monto' => 'required|numeric|min:0.01',
        ]);

        try {
            InventoryMovement::create([
                'product_id' => null,
                'user_id' => $user->id,
                'caja_id' => $cajaActiva->id,
                'type' => 'salida',
                'quantity' => 1,
                'unit_price' => $request->monto,
                'total' => $request->monto,
                'reason' => $request->concepto,
                'notes' => 'Salida / Gasto de caja',
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Gasto registrado correctamente.',
                ]);
            }

            return redirect()->route('caja.index')->with('success', 'Gasto registrado correctamente.');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
            }
            return redirect()->back()->with('error', 'Error al registrar el gasto: ' . $e->getMessage());
        }
    }

    /**
     * Muestra el historial general de caja (Exclusivo para Administradores / Supervisores).
     */
    public function historial()
    {
        $historial = CajaMovimiento::with('user')->latest()->paginate(10);

        return view('caja.historial', compact('historial'));
    }

    /**
     * Procesa la apertura de la caja.
     */
    public function abrir(Request $request)
    {
        $user = auth()->user();

        $tieneCajaAbierta = CajaMovimiento::where('user_id', $user->id)
            ->where('estado', 'abierta')
            ->exists();

        if ($tieneCajaAbierta) {
            if ($request->wantsJson()) {
                return response()->json(['status' => 'error', 'message' => 'Ya tienes una sesión de caja activa.'], 422);
            }

            return redirect()->back()->with('error', 'Ya tienes una sesión de caja activa.');
        }

        $request->validate([
            'monto_apertura' => 'required|numeric|min:0',
            'observaciones' => 'nullable|string|max:500',
        ]);

        $caja = CajaMovimiento::create([
            'user_id' => $user->id,
            'monto_apertura' => $request->monto_apertura,
            'fecha_apertura' => Carbon::now(),
            'estado' => 'abierta',
            'observaciones' => $request->observaciones,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => '¡Caja abierta exitosamente!',
                'caja' => $caja,
            ]);
        }

        return redirect()->route('caja.index')->with('success', '¡Caja abierta exitosamente! Ya puedes realizar ventas.');
    }

    /**
     * Procesa el cierre o corte de la caja.
     */
    public function cerrar(Request $request)
    {
        $user = auth()->user();

        $cajaActiva = CajaMovimiento::where('user_id', $user->id)
            ->where('estado', 'abierta')
            ->first();

        if (! $cajaActiva) {
            if ($request->wantsJson()) {
                return response()->json(['status' => 'error', 'message' => 'No se encontró ninguna caja abierta para cerrar.'], 404);
            }

            return redirect()->back()->with('error', 'No se encontró ninguna caja abierta para cerrar.');
        }

        $request->validate([
            'monto_cierre' => 'required|numeric|min:0',
            'observaciones' => 'nullable|string|max:500',
        ]);

        $nuevasObservaciones = $cajaActiva->observaciones;
        if ($request->filled('observaciones')) {
            $nuevasObservaciones .= ($nuevasObservaciones ? ' | ' : '').'Cierre: '.$request->observaciones;
        }

        $cajaActiva->update([
            'monto_cierre' => $request->monto_cierre,
            'fecha_cierre' => Carbon::now(),
            'estado' => 'cerrada',
            'observaciones' => $nuevasObservaciones,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Caja cerrada correctamente. Turno finalizado.',
                'caja' => $cajaActiva,
            ]);
        }

        return redirect()->route('caja.index')->with('success', 'Caja cerrada correctamente. Turno finalizado.');
    }
}