@extends('layouts.app')

@section('header_title', 'Control de Caja y Turno')

@section('content')
<x-app-container>
<div class="max-w-7xl mx-auto space-y-4 sm:space-y-6" x-data="{ 
    montoCierreInput: '',
    saldoEstimado: {{ $cajaActiva ? ($cajaActiva->monto_apertura + ($totalVentasEfectivo ?? 0) - ($totalGastos ?? 0)) : 0 }},
    openGastoModal: false,
    
    get diferenciaCierre() {
        let finalCaja = parseFloat(this.montoCierreInput) || 0;
        let diferencia = finalCaja - this.saldoEstimado;
        return diferencia.toFixed(2);
    }
}" @keydown.escape.window="openGastoModal = false">

    {{-- Encabezado Principal --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-4 border-b border-slate-200/80 dark:border-slate-800/80">
        <div>
            <h1 class="page-title">Movimientos de Caja</h1>
            <p class="page-subtitle">Gestión de apertura de turno, corte de caja y registro de flujo de efectivo.</p>
        </div>
        <div class="self-start sm:self-auto flex items-center gap-3">
            @if($cajaActiva)
                {{-- Botón Registrar Gasto (coral, alineado a la marca) --}}
                <button type="button" @click="openGastoModal = true" 
                    class="py-2 px-3.5 bg-[#FF6B4A] hover:bg-[#F0552F] active:scale-95 text-white font-bold rounded-xl transition duration-200 shadow-md shadow-[#FF6B4A]/20 flex items-center gap-1.5 text-[11px] uppercase tracking-wider cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    Registrar Gasto
                </button>

                <span class="badge-emerald flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    Caja Abierta (#{{ $cajaActiva->id }})
                </span>
            @else
                <span class="badge-rose flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                    Caja Cerrada
                </span>
            @endif
        </div>
    </div>

    @if(!$cajaActiva)
        {{-- FORMULARIO DE APERTURA --}}
        <div class="max-w-md mx-auto card-base p-0 overflow-hidden shadow-lg">
            <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/40">
                <h2 class="text-xs sm:text-sm font-black text-slate-800 dark:text-white uppercase tracking-wider">Abrir Nuevo Turno</h2>
                <p class="text-[11px] text-slate-400 font-bold mt-0.5">Ingresa el monto inicial para comenzar las operaciones.</p>
            </div>

            <form action="{{ route('caja.abrir') }}" method="POST" class="p-6 space-y-4">
                @csrf
                <div>
                    <label for="monto_apertura" class="form-label">Monto Inicial en Efectivo ($)</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400 font-bold">$</span>
                        <input type="number" step="0.01" min="0" name="monto_apertura" id="monto_apertura" required placeholder="0.00"
                            class="form-input pl-8 font-black text-base no-spinner">
                    </div>
                </div>

                <div>
                    <label for="observaciones" class="form-label">Observaciones / Notas</label>
                    <textarea name="observaciones" id="observaciones" rows="3" placeholder="Opcional..."
                        class="form-input"></textarea>
                </div>

                <button type="submit" class="w-full py-3 px-4 bg-[#FF4500] hover:bg-[#E63E00] active:scale-95 text-white font-bold rounded-2xl transition duration-200 shadow-lg shadow-[#FF4500]/25 uppercase tracking-wider cursor-pointer flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    Abrir Caja e Iniciar Turno
                </button>
            </form>
        </div>
    @else
        {{-- LAYOUT PRINCIPAL DE 2 COLUMNAS --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 sm:gap-6 items-start">
            
            {{-- COLUMNA IZQUIERDA: RESUMEN Y CIERRE --}}
            <div class="lg:col-span-4 space-y-5 sm:space-y-6">
                
                {{-- Tarjeta de Resumen Financiero --}}
                <div class="card-base p-0 overflow-hidden shadow-sm border border-slate-200/70 dark:border-slate-800/70">
                    <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800/80 bg-slate-50 dark:bg-slate-900/40 flex items-center justify-between">
                        <h2 class="text-xs sm:text-sm font-black text-slate-800 dark:text-white uppercase tracking-wider">Resumen de Turno</h2>
                        <span class="text-xs font-black text-[#FF6B4A] dark:text-[#FF8A65]">#{{ $cajaActiva->id }}</span>
                    </div>

                    <div class="p-6 space-y-3 text-xs">
                        <div class="flex justify-between text-slate-500 dark:text-slate-400 font-semibold">
                            <span>Cajero:</span>
                            <span class="font-bold text-slate-800 dark:text-white truncate max-w-[150px] text-right">{{ auth()->user()->name }}</span>
                        </div>
                        <div class="flex justify-between text-slate-500 dark:text-slate-400 font-semibold">
                            <span>Apertura:</span>
                            <span class="font-bold text-slate-800 dark:text-white">{{ optional($cajaActiva->fecha_apertura)->format('d/m/Y ') }}</span>
                        </div>
                        <div class="pt-2 border-t border-slate-100 dark:border-slate-800/80 flex justify-between text-slate-600 dark:text-slate-300 font-bold">
                            <span>Saldo Inicial:</span>
                            <span class="text-slate-900 dark:text-white">${{ number_format($cajaActiva->monto_apertura, 2) }}</span>
                        </div>

                        {{-- Ventas por método de pago --}}
                        <div class="flex justify-between text-emerald-600 dark:text-emerald-400 font-bold">
                            <span>+ Ventas (Efectivo):</span>
                            <span>+${{ number_format($totalVentasEfectivo ?? 0, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-emerald-600 dark:text-emerald-400 font-bold">
                            <span>+ Ventas (Tarjeta):</span>
                            <span>+${{ number_format($totalVentasTarjeta ?? 0, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-emerald-600 dark:text-emerald-400 font-bold">
                            <span>+ Ventas (Transferencia):</span>
                            <span>+${{ number_format($totalVentasTransferencia ?? 0, 2) }}</span>
                        </div>

                        <div class="flex justify-between text-rose-600 dark:text-rose-400 pb-2 border-b border-slate-100 dark:border-slate-800/80 font-bold">
                            <span>- Salidas / Gastos:</span>
                            <span>-${{ number_format($totalGastos ?? 0, 2) }}</span>
                        </div>

                        <div class="pt-2">
                            <span class="kpi-label">Saldo Actual Estimado (Efectivo)</span>
                            <span class="kpi-value text-[#FF4500] dark:text-[#FF8A65] mt-1 block font-black text-xl">
                                ${{ number_format($cajaActiva->monto_apertura + ($totalVentasEfectivo ?? 0) - ($totalGastos ?? 0), 2) }}
                            </span>
                        </div>
                    </div>

                    {{-- Formulario de Cierre Integrado --}}
                    <form id="formCorteCaja" action="{{ route('caja.cerrar') }}" method="POST" class="p-6 border-t border-slate-100 dark:border-slate-800/80 space-y-4 bg-slate-50/60 dark:bg-slate-900/30">
                        @csrf
                        <div>
                            <label for="monto_cierre" class="form-label">Monto Final en Caja ($) - Físico</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400 font-bold">$</span>
                                <input type="number" step="0.01" min="0" name="monto_cierre" id="monto_cierre" required placeholder="0.00"
                                    x-model="montoCierreInput"
                                    class="form-input pl-8 font-black text-sm focus:border-rose-500 focus:ring-rose-500/40 no-spinner">
                            </div>
                        </div>

                        <div class="p-3 rounded-xl bg-slate-100 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 transition" x-show="montoCierreInput !== ''" x-cloak>
                            <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-500">Diferencia / Descuadre:</span>
                            <span class="text-sm font-black" :class="diferenciaCierre >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'">
                                $<span x-text="diferenciaCierre"></span> <span class="text-[10px] font-semibold" x-text="diferenciaCierre >= 0 ? '(Sobrante / Cuadrado)' : '(Faltante)'"></span>
                            </span>
                        </div>

                        <div>
                            <label for="observaciones_cierre" class="form-label">Notas del Corte</label>
                            <textarea name="observaciones" id="observaciones_cierre" rows="2" placeholder="Sin novedades..."
                                class="form-input focus:border-rose-500 focus:ring-rose-500/40"></textarea>
                        </div>

                        <button type="button" onclick="confirmarCorteCaja()"
                            class="w-full py-3 px-4 bg-rose-600 hover:bg-rose-700 active:scale-95 text-white font-bold rounded-2xl transition duration-200 shadow-lg shadow-rose-500/20 flex items-center justify-center gap-2 text-xs uppercase tracking-wider cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
                            Cerrar Caja
                        </button>
                    </form>
                </div>
            </div>

            {{-- COLUMNA DERECHA: TABLAS DE MOVIMIENTOS --}}
            <div class="lg:col-span-8 space-y-5 sm:space-y-6">
                
                {{-- VENTAS DEL TURNO --}}
                <div class="table-container shadow-sm border border-slate-200/70 dark:border-slate-800/70">
                    <div class="px-6 py-4 border-b border-slate-200/80 dark:border-slate-800/80 flex items-center justify-between bg-slate-50 dark:bg-slate-900/40">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                            <h2 class="text-xs sm:text-sm font-black text-slate-800 dark:text-white uppercase tracking-wider">Ventas del Turno</h2>
                        </div>
                        <span class="badge-emerald">
                            Total: ${{ number_format(($totalVentasEfectivo ?? 0) + ($totalVentasTarjeta ?? 0) + ($totalVentasTransferencia ?? 0), 2) }}
                        </span>
                    </div>

                    {{-- Vista Escritorio (Tabla) --}}
                    <div class="hidden md:block overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr>
                                    <th class="table-th">Fecha</th>
                                    <th class="table-th">Total</th>
                                    <th class="table-th">Recibido</th>
                                    <th class="table-th">Cambio/Ref</th>
                                    <th class="table-th">Método</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($ventasTurno ?? [] as $venta)
                                    @php
                                        $metodo = $venta->metodo_pago;

                                        // Extrae el número de referencia (voucher/autorización o rastreo
                                        // bancario) guardado dentro del texto de "reason", con formato
                                        // "... | Ref: XXXXX" (ver CajaController@registrarVenta).
                                        $referenciaVenta = null;
                                        if (preg_match('/\|\s*Ref:\s*(.+)$/i', $venta->reason ?? '', $matchesRefVenta)) {
                                            $referenciaVenta = trim($matchesRefVenta[1]);
                                        }

                                        $claseBadge = match($metodo) {
                                            'Tarjeta' => 'bg-blue-500/10 text-blue-400 border border-blue-500/20',
                                            'Transferencia' => 'bg-purple-500/10 text-purple-400 border border-purple-500/20',
                                            default => 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20',
                                        };
                                    @endphp
                                    <tr class="table-tr">
                                        <td class="table-td text-slate-400 font-mono text-[11px] font-bold">{{ $venta->created_at ? $venta->created_at->format('d/m/Y') : '' }}</td>
                                        <td class="table-td font-black text-emerald-600 dark:text-emerald-400">${{ number_format($venta->total ?? ($venta->quantity * $venta->unit_price), 2) }}</td>
                                        <td class="table-td font-bold text-slate-800 dark:text-slate-100">${{ number_format($venta->monto_recibido ?? 0, 2) }}</td>
                                        <td class="table-td font-bold {{ $metodo === 'Efectivo' ? (($venta->cambio ?? 0) > 0 ? 'text-[#FF6B4A] dark:text-[#FF8A65]' : 'text-slate-400') : 'text-slate-500 dark:text-slate-300 font-mono text-[11px]' }}">
                                            @if($metodo === 'Efectivo')
                                                ${{ number_format($venta->cambio ?? 0, 2) }}
                                            @else
                                                {{ $referenciaVenta ?? '-' }}
                                            @endif
                                        </td>
                                        <td class="table-td">
                                            <span class="px-2.5 py-1 rounded-lg text-xs font-extrabold {{ $claseBadge }}">
                                                {{ strtoupper($metodo) }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="p-10 text-center text-xs font-bold text-slate-400">
                                            No hay ventas registradas en este turno.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Vista Móvil (Cards) --}}
                    <div class="block md:hidden divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($ventasTurno ?? [] as $venta)
                            @php
                                $metodoMovil = $venta->metodo_pago;

                                $referenciaVentaMovil = null;
                                if (preg_match('/\|\s*Ref:\s*(.+)$/i', $venta->reason ?? '', $matchesRefMovil)) {
                                    $referenciaVentaMovil = trim($matchesRefMovil[1]);
                                }
                            @endphp
                            <div class="p-4 space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-mono font-bold text-slate-400">{{ $venta->created_at ? $venta->created_at->format('d/m/Y') : '' }}</span>
                                    <span class="font-black text-emerald-600 dark:text-emerald-400">${{ number_format($venta->total ?? ($venta->quantity * $venta->unit_price), 2) }}</span>
                                </div>
                                <div class="flex items-center justify-between text-xs font-semibold text-slate-500 dark:text-slate-400">
                                    <span>Recibido: ${{ number_format($venta->monto_recibido ?? 0, 2) }}</span>
                                    @if($metodoMovil === 'Efectivo')
                                        <span>Cambio: ${{ number_format($venta->cambio ?? 0, 2) }}</span>
                                    @else
                                        <span>Ref: {{ $referenciaVentaMovil ?? '-' }}</span>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="p-6 text-center text-xs font-bold text-slate-400">
                                No hay ventas registradas en este turno.
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- GASTOS Y SALIDAS --}}
                <div class="table-container shadow-sm border border-slate-200/70 dark:border-slate-800/70">
                    <div class="px-6 py-4 border-b border-slate-200/80 dark:border-slate-800/80 flex items-center justify-between bg-slate-50 dark:bg-slate-900/40">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                            <h2 class="text-xs sm:text-sm font-black text-slate-800 dark:text-white uppercase tracking-wider">Gastos y Salidas</h2>
                        </div>
                        <span class="badge-rose">
                            Total: ${{ number_format($totalGastos ?? 0, 2) }}
                        </span>
                    </div>

                    {{-- Vista Escritorio (Tabla) --}}
                    <div class="hidden md:block overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr>
                                    <th class="table-th">Fecha</th>
                                    <th class="table-th">Concepto / Motivo</th>
                                    <th class="table-th">Monto</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($gastosTurno ?? [] as $gasto)
                                    <tr class="table-tr">
                                        <td class="table-td text-slate-400 font-mono text-[11px] font-bold">{{ $gasto->created_at ? $gasto->created_at->format('d/m/Y') : '' }}</td>
                                        <td class="table-td font-bold text-slate-800 dark:text-white">{{ $gasto->reason ?? $gasto->concepto ?? $gasto->observaciones }}</td>
                                        <td class="table-td font-black text-rose-600 dark:text-rose-400">-${{ number_format($gasto->total ?? $gasto->monto ?? 0, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="p-10 text-center text-xs font-bold text-slate-400">
                                            No hay gastos o salidas registrados en este turno.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Vista Móvil (Cards) --}}
                    <div class="block md:hidden divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($gastosTurno ?? [] as $gasto)
                            <div class="p-4 flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-xs font-bold text-slate-800 dark:text-white">{{ $gasto->reason ?? $gasto->concepto ?? $gasto->observaciones }}</p>
                                    <span class="text-[10px] font-mono font-bold text-slate-400">{{ $gasto->created_at ? $gasto->created_at->format('d/m/Y') : '' }}</span>
                                </div>
                                <span class="font-black text-rose-600 dark:text-rose-400 text-sm">-${{ number_format($gasto->total ?? $gasto->monto ?? 0, 2) }}</span>
                            </div>
                        @empty
                            <div class="p-6 text-center text-xs font-bold text-slate-400">
                                No hay gastos o salidas registrados en este turno.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- MODAL PARA REGISTRAR GASTO / SALIDA (alineado a la paleta coral) --}}
        <div x-show="openGastoModal" 
             x-cloak
             class="fixed inset-0 z-50 bg-slate-950/70 dark:bg-slate-950/85 backdrop-blur-md flex items-center justify-center p-3 sm:p-5 overflow-hidden"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">

            <div class="relative w-full max-w-md max-h-[90vh] bg-white dark:bg-[#090d18] text-slate-900 dark:text-slate-100 rounded-[28px] border border-slate-200/80 dark:border-[#FF6B4A]/20 shadow-2xl flex flex-col overflow-hidden my-auto"
                 @click.outside="openGastoModal = false">

                {{-- Resplandor Neón Coral (uniforme con el resto de modales) --}}
                <div class="hidden dark:block pointer-events-none absolute -top-20 -left-20 w-72 h-72 bg-[#FF6B4A]/15 rounded-full blur-3xl"></div>

                {{-- Header --}}
                <div class="relative z-10 flex items-center justify-between p-5 sm:px-7 border-b border-slate-100 dark:border-[#FF6B4A]/10 bg-transparent shrink-0">
                    <div class="flex items-center gap-3">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#FF6B4A] shadow-[0_0_10px_rgba(255,107,74,0.8)]"></span>
                        <h3 class="text-xs sm:text-sm font-black text-slate-900 dark:text-white uppercase tracking-wider">
                            Registrar Salida / Gasto de Caja
                        </h3>
                    </div>
                    <button type="button" @click="openGastoModal = false"
                            class="w-8 h-8 rounded-full bg-[#FF6B4A]/10 dark:bg-[#FF6B4A]/15 hover:bg-[#F0552F] dark:hover:bg-[#F0552F] text-[#F0552F] dark:text-[#FF8A65] hover:text-white dark:hover:text-white flex items-center justify-center font-bold transition-all duration-200 hover:rotate-90 hover:shadow-md hover:shadow-[#F0552F]/30 cursor-pointer border border-[#FF6B4A]/30 dark:border-[#FF6B4A]/25">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Body --}}
                <form action="{{ route('caja.gastos.store') }}" method="POST" class="relative z-10 p-6 space-y-4">
                    @csrf
                    <div>
                        <label for="concepto" class="form-label">Concepto / Motivo</label>
                        <input type="text" name="concepto" id="concepto" required placeholder="Ej. Compra de suministros, pago de servicio..."
                            class="form-input text-xs font-semibold">
                    </div>

                    <div>
                        <label for="monto_gasto" class="form-label">Monto ($)</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400 font-bold">$</span>
                            <input type="number" step="0.01" min="0.01" name="monto" id="monto_gasto" required placeholder="0.00"
                                class="form-input pl-8 font-black text-sm no-spinner">
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" @click="openGastoModal = false"
                            class="px-4 py-2 !bg-slate-200 dark:!bg-slate-800 hover:!bg-slate-300 dark:hover:!bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs rounded-xl transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md cursor-pointer">
                            Cancelar
                        </button>
                        <button type="submit"
                            class="px-4 py-2 bg-[#FF6B4A] hover:bg-[#F0552F] text-white font-bold text-xs rounded-xl shadow-md shadow-[#FF6B4A]/25 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg cursor-pointer">
                            Guardar Salida
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
</x-app-container>
@endsection

@push('scripts')
    <script src="{{ asset('js/components/caja-management.js') }}"></script>
@endpush