@extends('layouts.app')

@section('header_title', 'Control de Caja y Turno')

@section('content')
<x-app-container>
<div class="max-w-7xl mx-auto space-y-4 sm:space-y-6" x-data="{ 
    montoCierreInput: '',
    saldoEstimado: {{ $cajaActiva ? ($cajaActiva->monto_apertura + ($totalVentasEfectivo ?? 0) - ($totalGastos ?? 0)) : 0 }},
    openGastoModal: false,
    gastoConcepto: '',
    gastoMonto: '',
    
    get diferenciaCierre() {
        let finalCaja = parseFloat(this.montoCierreInput) || 0;
        let diferencia = finalCaja - this.saldoEstimado;
        return diferencia.toFixed(2);
    }
}">

    {{-- Encabezado Principal --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-4 border-b border-slate-200/80 dark:border-slate-800/80">
        <div>
            <h1 class="page-title">Movimientos de Caja</h1>
            <p class="page-subtitle">Gestión de apertura de turno, corte de caja y registro de flujo de efectivo.</p>
        </div>
        <div class="self-start sm:self-auto flex items-center gap-3">
            @if($cajaActiva)
                {{-- Botón Registrar Gasto reubicado arriba junto a la caja abierta --}}
                <button type="button" @click="openGastoModal = true" 
                    class="py-1.5 px-3 bg-rose-600 hover:bg-rose-700 active:scale-95 text-white font-bold rounded-xl transition duration-200 shadow-md shadow-rose-500/20 flex items-center gap-1.5 text-[11px] uppercase tracking-wider cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    Registrar Gasto
                </button>

                <span class="badge-emerald">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    Caja Abierta (#{{ $cajaActiva->id }})
                </span>
            @else
                <span class="badge-rose">
                    <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                    Caja Cerrada
                </span>
            @endif
        </div>
    </div>

    @if(!$cajaActiva)
        {{-- FORMULARIO DE APERTURA --}}
        <div class="max-w-md mx-auto card-base p-0 overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-[#070a11]/80">
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
                            class="form-input pl-8 font-black text-base">
                    </div>
                </div>

                <div>
                    <label for="observaciones" class="form-label">Observaciones / Notas</label>
                    <textarea name="observaciones" id="observaciones" rows="3" placeholder="Opcional..."
                        class="form-input"></textarea>
                </div>

                <button type="submit" class="btn-primary w-full uppercase tracking-wider cursor-pointer">
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
                <div class="card-base p-0 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800/80 bg-slate-50/50 dark:bg-[#070a11]/80 flex items-center justify-between">
                        <h2 class="text-xs sm:text-sm font-black text-slate-800 dark:text-white uppercase tracking-wider">Resumen de Turno</h2>
                        <span class="text-xs font-black text-indigo-600 dark:text-indigo-400">#{{ $cajaActiva->id }}</span>
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
                        <div class="flex justify-between text-emerald-600 dark:text-emerald-400 font-bold">
                            <span>+ Ventas (Efectivo):</span>
                            <span>+${{ number_format($totalVentasEfectivo ?? 0, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-rose-600 dark:text-rose-400 pb-2 border-b border-slate-100 dark:border-slate-800/80 font-bold">
                            <span>- Salidas / Gastos:</span>
                            <span>-${{ number_format($totalGastos ?? 0, 2) }}</span>
                        </div>

                        <div class="pt-2">
                            <span class="kpi-label">Saldo Actual Estimado</span>
                            <span class="kpi-value text-indigo-600 dark:text-indigo-400 mt-1 block">
                                ${{ number_format($cajaActiva->monto_apertura + ($totalVentasEfectivo ?? 0) - ($totalGastos ?? 0), 2) }}
                            </span>
                        </div>
                    </div>

                    {{-- Formulario de Cierre Integrado --}}
                    <form id="formCorteCaja" action="{{ route('caja.cerrar') }}" method="POST" class="p-6 border-t border-slate-100 dark:border-slate-800/80 space-y-4 bg-slate-50/30 dark:bg-[#070a11]/40">
                        @csrf
                        <div>
                            <label for="monto_cierre" class="form-label">Monto Final en Caja ($) - Físico</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400 font-bold">$</span>
                                <input type="number" step="0.01" min="0" name="monto_cierre" id="monto_cierre" required placeholder="0.00"
                                    x-model="montoCierreInput"
                                    class="form-input pl-8 font-black text-sm focus:border-rose-500 focus:ring-rose-500/40">
                            </div>
                        </div>

                        <div class="p-3 rounded-xl bg-slate-100 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700" x-show="montoCierreInput !== ''" x-cloak>
                            <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-500">Diferencia / Descuadre:</span>
                            <span class="text-sm font-black" :class="diferenciaCierre >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'">
                                $<span x-text="diferenciaCierre"></span> <span class="text-[10px] font-semibold" x-text="diferenciaCierre >= 0 ? '(Sobrante)' : '(Faltante)'"></span>
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
                <div class="table-container">
                    <div class="px-6 py-4 border-b border-slate-200/80 dark:border-slate-800/80 flex items-center justify-between bg-slate-100/60 dark:bg-[#070a11]/80">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                            <h2 class="text-xs sm:text-sm font-black text-slate-800 dark:text-white uppercase tracking-wider">Ventas del Turno</h2>
                        </div>
                        <span class="badge-emerald">
                            Total: ${{ number_format($totalVentasEfectivo ?? 0, 2) }}
                        </span>
                    </div>

                    <div class="hidden md:block overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr>
                                    <th class="table-th">Fecha</th>
                                    <th class="table-th">Total</th>
                                    <th class="table-th">Recibido</th>
                                    <th class="table-th">Cambio</th>
                                    <th class="table-th">Método</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($ventasTurno ?? [] as $venta)
                                    <tr class="table-tr">
                                        <td class="table-td text-slate-400 font-mono text-[11px] font-bold">{{ $venta->created_at ? $venta->created_at->format('d/m/Y ') : '' }}</td>
                                        <td class="table-td font-black text-emerald-600 dark:text-emerald-400">${{ number_format($venta->total ?? ($venta->quantity * $venta->unit_price), 2) }}</td>
                                        <td class="table-td font-bold text-slate-800 dark:text-slate-100">${{ number_format($venta->monto_recibido ?? 0, 2) }}</td>
                                        <td class="table-td font-bold {{ ($venta->cambio ?? 0) > 0 ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400' }}">
                                            ${{ number_format($venta->cambio ?? 0, 2) }}
                                        </td>
                                        <td class="table-td">
                                            <span class="badge-emerald">Efectivo</span>
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
                </div>

                {{-- GASTOS Y SALIDAS --}}
                <div class="table-container">
                    <div class="px-6 py-4 border-b border-slate-200/80 dark:border-slate-800/80 flex items-center justify-between bg-slate-100/60 dark:bg-[#070a11]/80">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                            <h2 class="text-xs sm:text-sm font-black text-slate-800 dark:text-white uppercase tracking-wider">Gastos y Salidas</h2>
                        </div>
                        <span class="badge-rose">
                            Total: ${{ number_format($totalGastos ?? 0, 2) }}
                        </span>
                    </div>

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
                                        <td class="table-td text-slate-400 font-mono text-[11px] font-bold">{{ $gasto->created_at ? $gasto->created_at->format('d/m/Y ') : '' }}</td>
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
                </div>
            </div>
        </div>

        {{-- MODAL PARA REGISTRAR GASTO / SALIDA --}}
        <div x-show="openGastoModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4" x-cloak>
            <div class="card-base w-full max-w-md p-0 overflow-hidden shadow-2xl animate-in fade-in zoom-in duration-200" @click.away="openGastoModal = false">
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-[#070a11]/80 flex items-center justify-between">
                    <h3 class="text-xs sm:text-sm font-black text-slate-800 dark:text-white uppercase tracking-wider">Registrar Salida / Gasto de Caja</h3>
                    <button type="button" @click="openGastoModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form action="{{ route('caja.gastos.store') }}" method="POST" class="p-6 space-y-4">
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
                                class="form-input pl-8 font-black text-sm">
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" @click="openGastoModal = false" class="px-4 py-2 bg-slate-200 dark:bg-slate-800 hover:bg-slate-300 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs rounded-xl transition cursor-pointer">
                            Cancelar
                        </button>
                        <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs rounded-xl shadow-md shadow-rose-500/20 transition cursor-pointer">
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