<?php

namespace App\Http\Controllers;

use App\Models\CajaMovimiento;
use App\Models\Category;
use App\Models\InventoryMovement;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CajaController extends Controller
{
    /**
     * Calcula los totales de ventas (por método) y gastos de una caja específica.
     */
    private function calcularTotalesTurno($cajaId)
    {
        $movimientosVentas = InventoryMovement::where('caja_id', $cajaId)
            ->where('type', 'salida')
            ->where('reason', 'like', 'Venta directa%')
            ->get();

        $totalEfectivo = $movimientosVentas->filter(fn($v) => str_contains(strtolower($v->reason ?? ''), 'efectivo'))->sum('total');
        $totalTarjeta = $movimientosVentas->filter(fn($v) => str_contains(strtolower($v->reason ?? ''), 'tarjeta'))->sum('total');
        $totalTransferencia = $movimientosVentas->filter(fn($v) => str_contains(strtolower($v->reason ?? ''), 'transferencia'))->sum('total');

        $totalGastos = InventoryMovement::where('caja_id', $cajaId)
            ->where('type', 'salida')
            ->where('reason', 'not like', 'Venta directa%')
            ->sum('total');

        return [
            'ventas_efectivo' => $totalEfectivo,
            'ventas_tarjeta' => $totalTarjeta,
            'ventas_transferencia' => $totalTransferencia,
            'gastos' => $totalGastos,
        ];
    }

    /**
     * Genera un folio de ticket consecutivo y seguro ante ventas concurrentes de distintos usuarios,
     * SIN necesidad de tablas ni migraciones nuevas.
     *
     * Usa Cache::lock() (candado atómico de Laravel) para que, si dos cajeros venden al mismo
     * tiempo, el segundo espere unos milisegundos a que el primero termine antes de calcular
     * su folio. Mientras tiene el candado, busca el último folio ya guardado en la columna
     * "reason" (que ya existe en inventory_movements) y le suma 1. Formato: T-000019, T-000020, ...
     *
     * IMPORTANTE: en la tabla pueden existir folios viejos con el formato anterior
     * (T-20260820154710-K9BF7I). El "______" del LIKE de abajo son exactamente 6 guiones
     * bajos, uno por cada caracter, así que SOLO hace match con folios de exactamente 6
     * dígitos (el formato nuevo). Los folios viejos tienen más caracteres ahí y quedan
     * ignorados aunque sean más recientes que los del formato corto.
     */
    private function generarFolioConsecutivo(): string
    {
        $lock = Cache::lock('folio_ticket_lock', 10);

        try {
            // Espera hasta 5 segundos si otro proceso está generando un folio en ese instante
            $lock->block(5);

            $ultimaReason = InventoryMovement::where('reason', 'like', 'Venta directa (T-______)%')
                ->orderByDesc('id')
                ->value('reason');

            $siguienteNumero = 1;
            if ($ultimaReason && preg_match('/T-(\d{6})\)/', $ultimaReason, $matches)) {
                $siguienteNumero = ((int) $matches[1]) + 1;
            }

            return 'T-' . str_pad($siguienteNumero, 6, '0', STR_PAD_LEFT);
        } finally {
            $lock->release();
        }
    }

    /**
     * Muestra la vista operativa del módulo de caja (Apertura / Corte del usuario actual).
     */
    public function index()
    {
        $user = auth()->user();

        $cajaActiva = CajaMovimiento::where('user_id', $user->id)
            ->where('estado', 'abierta')
            ->first();

        $totalVentasEfectivo = 0;
        $totalVentasTarjeta = 0;
        $totalVentasTransferencia = 0;
        $totalGastos = 0;
        $ventasTurno = collect();
        $gastosTurno = collect();

        if ($cajaActiva) {
            $movimientosVentas = InventoryMovement::with(['product', 'user'])
                ->where('caja_id', $cajaActiva->id)
                ->where('type', 'salida')
                ->where('reason', 'like', 'Venta directa%')
                ->latest()
                ->get();

            $totalVentasEfectivo = $movimientosVentas->filter(fn($v) => str_contains(strtolower($v->reason ?? ''), 'efectivo'))->sum('total');
            $totalVentasTarjeta = $movimientosVentas->filter(fn($v) => str_contains(strtolower($v->reason ?? ''), 'tarjeta'))->sum('total');
            $totalVentasTransferencia = $movimientosVentas->filter(fn($v) => str_contains(strtolower($v->reason ?? ''), 'transferencia'))->sum('total');

            $ventasTurno = $movimientosVentas->groupBy('reason')->map(function ($items) {
                $primerItem = $items->first();

                $ventaAgrupada = clone $primerItem;
                $ventaAgrupada->total = $items->sum('total');
                $ventaAgrupada->monto_recibido = $items->sum('monto_recibido');
                $ventaAgrupada->cambio = $items->sum('cambio');
                $ventaAgrupada->cantidad_items = $items->sum('quantity');

                return $ventaAgrupada;
            })->values();

            $gastosTurno = InventoryMovement::with(['product', 'user'])
                ->where('caja_id', $cajaActiva->id)
                ->where('type', 'salida')
                ->where('reason', 'not like', 'Venta directa%')
                ->latest()
                ->get();

            $totalGastos = $gastosTurno->sum('total');
        }

        return view('caja.index', compact(
            'cajaActiva',
            'ventasTurno',
            'totalVentasEfectivo',
            'totalVentasTarjeta',
            'totalVentasTransferencia',
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

        $cajaActiva = CajaMovimiento::where('user_id', $user->id)
            ->where('estado', 'abierta')
            ->first();

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

        $cajaActiva = CajaMovimiento::where('user_id', $user->id)
            ->where('estado', 'abierta')
            ->first();

        if (! $cajaActiva) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes una caja abierta para realizar ventas.',
            ], 422);
        }

        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'monto_recibido' => 'required|numeric|min:0',
            'metodo_pago' => 'required|in:efectivo,tarjeta,transferencia',
            'num_referencia' => 'nullable|string|max:100',
        ]);

        try {
            DB::beginTransaction();

            $totalVenta = 0;
            $itemsDetalle = [];
            $items = collect($request->input('items'))
                ->groupBy('id')
                ->map(fn ($group, $id) => [
                    'id' => (int) $id,
                    'quantity' => $group->sum('quantity'),
                ])
                ->values();

            foreach ($items as $item) {
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

            if ($request->metodo_pago === 'efectivo' && $request->monto_recibido < $totalVenta) {
                throw new \Exception('El monto recibido es menor al total de la venta.');
            }

            $montoRecibidoGlobal = $request->metodo_pago === 'efectivo' ? $request->monto_recibido : $totalVenta;
            $cambioGlobal = $request->metodo_pago === 'efectivo' ? max(0, $request->monto_recibido - $totalVenta) : 0;

            // Folio consecutivo y seguro ante concurrencia (T-000019, T-000020, ...)
            $folioTicket = $this->generarFolioConsecutivo();

            $razonMovimiento = "Venta directa ({$folioTicket}) - " . ucfirst($request->metodo_pago);
            if ($request->filled('num_referencia')) {
                $razonMovimiento .= ' | Ref: ' . $request->num_referencia;
            }

            foreach ($itemsDetalle as $index => $detalle) {
                $detalle['product']->decrement('stock', $detalle['quantity']);

                $esPrimerProducto = ($index === 0);

                InventoryMovement::create([
                    'product_id'     => $detalle['product']->id,
                    'user_id'        => $user->id,
                    'caja_id'        => $cajaActiva->id,
                    'type'           => 'salida',
                    'quantity'       => $detalle['quantity'],
                    'unit_price'     => $detalle['price'],
                    'total'          => $detalle['subtotal'],
                    'monto_recibido' => $esPrimerProducto ? $montoRecibidoGlobal : 0,
                    'cambio'         => $esPrimerProducto ? $cambioGlobal : 0,
                    'reason'         => $razonMovimiento,
                    'notes'          => $razonMovimiento,
                ]);
            }

            DB::commit();

            // === DATOS PARA EL TICKET IMPRIMIBLE ===
            $ticket = [
                'folio'          => $folioTicket,
                'fecha'          => Carbon::now()->format('d/m/Y h:i A'),
                'cajero'         => $user->name,
                'metodo_pago'    => $request->metodo_pago,
                'num_referencia' => $request->num_referencia,
                'items'          => collect($itemsDetalle)->map(function ($d) {
                    return [
                        'name'     => $d['product']->name,
                        'quantity' => $d['quantity'],
                        'price'    => $d['price'],
                        'subtotal' => $d['subtotal'],
                    ];
                })->values(),
                'total'          => $totalVenta,
                'monto_recibido' => $montoRecibidoGlobal,
                'cambio'         => $cambioGlobal,
            ];

            return response()->json([
                'success' => true,
                'message' => 'Venta completada con éxito.',
                'total'   => $totalVenta,
                'cambio'  => $cambioGlobal,
                'ticket'  => $ticket,
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
                'quantity' => 0,
                'unit_price' => $request->monto,
                'total' => $request->monto,
                'reason' => $request->concepto,
                'notes' => $request->concepto,
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
     * Devuelve el detalle completo de un turno (Montos, Ventas agrupadas y Gastos) para el modal del Historial.
     */
    public function detallesHistorial($id)
    {
        $caja = CajaMovimiento::with('user')->findOrFail($id);

        $movimientosVentas = InventoryMovement::with('product')
            ->where('caja_id', $caja->id)
            ->where('type', 'salida')
            ->where('reason', 'like', 'Venta directa%')
            ->get();

        $ventas = $movimientosVentas->groupBy('reason')->map(function ($items) {
            $primerItem = $items->first();

            $productosLista = $items->groupBy('product_id')->map(function ($grupoProducto) {
                $prod = $grupoProducto->first();

                return [
                    'nombre'   => $prod->product ? $prod->product->name : 'Producto eliminado',
                    'cantidad' => $grupoProducto->sum('quantity'),
                ];
            })->sortByDesc('cantidad')->values();

            // Extrae el número de referencia (voucher/autorización o rastreo bancario)
            // guardado dentro del texto de "reason", con formato "... | Ref: XXXXX"
            // (ver CajaController@registrarVenta, donde se concatena num_referencia).
            $referencia = null;
            if (preg_match('/\|\s*Ref:\s*(.+)$/i', $primerItem->reason ?? '', $matchesRef)) {
                $referencia = trim($matchesRef[1]);
            }

            return [
                'id'            => $primerItem->id,
                'hora'          => $primerItem->created_at->format('H:i:s'),
                'productos'     => $productosLista,
                'cantidad_items' => $items->sum('quantity'),
                'total'         => $items->sum('total'),
                'monto_recibido' => $items->sum('monto_recibido'),
                'cambio'         => $items->sum('cambio'),
                'metodo'         => str_contains(strtolower($primerItem->reason), 'tarjeta') ? 'TARJETA' :
                                   (str_contains(strtolower($primerItem->reason), 'transferencia') ? 'TRANSFERENCIA' : 'EFECTIVO'),
                'referencia'     => $referencia,
            ];
        })->values();

        $gastos = InventoryMovement::where('caja_id', $caja->id)
            ->where('type', 'salida')
            ->where('reason', 'not like', 'Venta directa%')
            ->get()
            ->map(function ($gasto) {
                return [
                    'concepto' => $gasto->reason,
                    'monto'    => $gasto->total,
                ];
            });

        if ($caja->estado === 'cerrada') {
            $montoEsperado = $caja->monto_esperado;
            $montoTarjeta = $caja->total_ventas_tarjeta;
            $montoTransferencia = $caja->total_ventas_transferencia;
        } else {
            $totales = $this->calcularTotalesTurno($caja->id);
            $montoEsperado = $caja->monto_apertura + $totales['ventas_efectivo'] - $totales['gastos'];
            $montoTarjeta = $totales['ventas_tarjeta'];
            $montoTransferencia = $totales['ventas_transferencia'];
        }

           return response()->json([
        'id'                  => $caja->id,
        'user'                => $caja->user->name ?? 'N/A',
        'monto_inicial'       => (float) $caja->monto_apertura,
        'monto_esperado'      => (float) $montoEsperado,
        'monto_cierre'        => $caja->monto_cierre !== null ? (float) $caja->monto_cierre : null,   // ← LÍNEA NUEVA
        'monto_tarjeta'       => (float) $montoTarjeta,
        'monto_transferencia' => (float) $montoTransferencia,
        'ventas'              => $ventas,
        'gastos'              => $gastos,
    ]);
    }

    /**
     * Procesa la apertura de la caja.
     * Protegido con Cache::lock() para evitar que un doble clic (o doble submit
     * por red lenta) genere dos cajas "abiertas" para el mismo usuario.
     */
    public function abrir(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'monto_apertura' => 'required|numeric|min:0',
            'observaciones' => 'nullable|string|max:500',
        ]);

        $lock = Cache::lock('abrir_caja_user_' . $user->id, 10);

        try {
            // Espera hasta 5 segundos si ya hay otra petición de apertura en curso
            $lock->block(5);

            $tieneCajaAbierta = CajaMovimiento::where('user_id', $user->id)
                ->where('estado', 'abierta')
                ->exists();

            if ($tieneCajaAbierta) {
                if ($request->wantsJson()) {
                    return response()->json(['status' => 'error', 'message' => 'Ya tienes una sesión de caja activa.'], 422);
                }

                return redirect()->back()->with('error', 'Ya tienes una sesión de caja activa.');
            }

            $caja = CajaMovimiento::create([
                'user_id' => $user->id,
                'monto_apertura' => $request->monto_apertura,
                'fecha_apertura' => Carbon::now(),
                'estado' => 'abierta',
                'observaciones' => $request->observaciones,
            ]);
        } finally {
            $lock->release();
        }

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
            $nuevasObservaciones .= ($nuevasObservaciones ? ' | ' : '') . 'Cierre: ' . $request->observaciones;
        }

        $totales = $this->calcularTotalesTurno($cajaActiva->id);
        $montoEsperado = $cajaActiva->monto_apertura + $totales['ventas_efectivo'] - $totales['gastos'];

        $cajaActiva->update([
            'monto_cierre' => $request->monto_cierre,
            'fecha_cierre' => Carbon::now(),
            'estado' => 'cerrada',
            'observaciones' => $nuevasObservaciones,
            'monto_esperado' => $montoEsperado,
            'total_ventas_efectivo' => $totales['ventas_efectivo'],
            'total_ventas_tarjeta' => $totales['ventas_tarjeta'],
            'total_ventas_transferencia' => $totales['ventas_transferencia'],
            'total_gastos' => $totales['gastos'],
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