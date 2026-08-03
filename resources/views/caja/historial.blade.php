@extends('layouts.app')

@section('title', 'SCGI - Historial de Caja')
@section('header_title', 'Historial de Turnos y Caja')

@section('content')
<x-app-container>
<div class="space-y-8" x-data="{ 
    openModal: false,
    turnoSeleccionado: null,
    sidebarWidth: 256,
    init() {
        const findSidebar = () => {
            const sb = document.querySelector('aside') || document.querySelector('.sidebar') || document.querySelector('nav');
            if (sb) {
                this.sidebarWidth = sb.offsetWidth;
                const observer = new ResizeObserver(entries => {
                    for (let entry of entries) {
                        this.sidebarWidth = entry.contentRect.width;
                    }
                });
                observer.observe(sb);
            }
        };
        setTimeout(findSidebar, 100);
    },
    verMovimientos(turno) {
        this.turnoSeleccionado = turno;
        this.openModal = true;
    },
    get montoEsperado() {
        if (!this.turnoSeleccionado) return '0.00';
        let inicial = parseFloat(this.turnoSeleccionado.raw_monto_apertura || 0);
        let ventasEfectivo = (this.turnoSeleccionado.ventas || []).reduce((acc, v) => acc + parseFloat(v.total || 0), 0);
        let totalGastos = (this.turnoSeleccionado.gastos || []).reduce((acc, g) => acc + parseFloat(g.total || g.monto || 0), 0);
        return (inicial + ventasEfectivo - totalGastos).toFixed(2);
    },
    get montoTarjeta() {
        if (!this.turnoSeleccionado) return '0.00';
        let total = (this.turnoSeleccionado.ventas || []).filter(v => 
            v.metodo_pago === 'tarjeta' || v.payment_method === 'tarjeta' || v.type === 'tarjeta'
        ).reduce((acc, v) => acc + parseFloat(v.total || 0), 0);
        return total.toFixed(2);
    },
    get montoTransferencia() {
        if (!this.turnoSeleccionado) return '0.00';
        let total = (this.turnoSeleccionado.ventas || []).filter(v => 
            v.metodo_pago === 'transferencia' || v.payment_method === 'transferencia' || v.type === 'transferencia'
        ).reduce((acc, v) => acc + parseFloat(v.total || 0), 0);
        return total.toFixed(2);
    }
}">

    <!-- CABECERA -->
    <div class="flex flex-col gap-1">
        <nav class="text-xs font-bold text-indigo-500 tracking-wide uppercase">
            SCGI <span class="mx-1 text-slate-400 dark:text-slate-600">/</span> <span class="text-slate-600 dark:text-slate-300">Caja</span>
        </nav>
        <div>
            <h1 class="page-title">Historial de Caja</h1>
            <p class="page-subtitle">Bitácora general de aperturas, cierres y flujos de efectivo de todos los usuarios</p>
        </div>
    </div>

    <!-- CONTENEDOR PRINCIPAL -->
    <div class="table-container">
        
        <!-- Header de la tabla -->
        <div class="p-6 bg-slate-50/50 dark:bg-[#070a11] border-b border-slate-200/80 dark:border-slate-800/80 flex items-center justify-between">
            <div>
                <h2 class="text-sm font-black text-slate-900 dark:text-white">Registros del Sistema</h2>
                <p class="page-subtitle">Historial maestro en tiempo real</p>
            </div>
            <div class="w-8 h-8 rounded-xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-500 flex items-center justify-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>

        <!-- 1. VISTA DE TARJETAS (MÓVIL < 768px) -->
        <div class="block md:hidden divide-y divide-slate-100 dark:divide-slate-800/60">
            @forelse($historial as $movimiento)
                <div class="p-5 space-y-4 hover:bg-indigo-50/30 dark:hover:bg-indigo-500/5 transition-colors">
                    
                    <!-- Usuario, Botón de Historial y Estado -->
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-500/20 to-purple-500/20 border border-indigo-500/30 flex items-center justify-center font-black text-indigo-600 dark:text-indigo-400 text-xs shrink-0 shadow-sm">
                                {{ strtoupper(substr($movimiento->user->name ?? 'U', 0, 2)) }}
                            </div>
                            <span class="font-bold text-xs text-slate-900 dark:text-white truncate">
                                {{ $movimiento->user->name ?? 'N/A' }}
                            </span>
                        </div>

                        <div class="flex items-center gap-2">
                            <button type="button" @click="verMovimientos({{ json_encode([
                                'id' => $movimiento->id,
                                'user' => $movimiento->user->name ?? 'N/A',
                                'raw_monto_apertura' => $movimiento->monto_apertura,
                                'monto_apertura' => number_format($movimiento->monto_apertura, 2),
                                'monto_cierre' => $movimiento->monto_cierre ? number_format($movimiento->monto_cierre, 2) : 'No cerrado',
                                'fecha_apertura' => $movimiento->fecha_apertura ? \Carbon\Carbon::parse($movimiento->fecha_apertura)->format('d/m/Y H:i') : '-',
                                'fecha_cierre' => $movimiento->fecha_cierre ? \Carbon\Carbon::parse($movimiento->fecha_cierre)->format('d/m/Y H:i' ) : '-',
                                'ventas' => $movimiento->ventas ?? [],
                                'gastos' => $movimiento->gastos ?? []
                            ]) }})" 
                            class="w-8 h-8 rounded-xl bg-indigo-500/10 hover:bg-indigo-500/20 border border-indigo-500/30 text-indigo-500 flex items-center justify-center transition cursor-pointer shadow-xs" title="Ver movimientos">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </button>

                            @if($movimiento->estado === 'abierta')
                                <span class="badge-emerald">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shadow-sm shadow-emerald-500"></span>
                                    Abierta
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-[10px] font-black uppercase tracking-wider bg-slate-500/10 text-slate-500 dark:text-slate-400 border border-slate-500/20">
                                    Cerrada
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Detalles de Montos -->
                    <div class="grid grid-cols-2 gap-3 bg-slate-100/60 dark:bg-[#070a11] p-3.5 rounded-2xl border border-slate-200/60 dark:border-slate-800">
                        <div>
                            <span class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-0.5">Monto Inicial</span>
                            <span class="text-xs font-black text-emerald-600 dark:text-emerald-400">
                                ${{ number_format($movimiento->monto_apertura, 2) }}
                            </span>
                        </div>
                        <div>
                            <span class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-0.5">Monto Cierre</span>
                            <span class="text-xs font-black text-slate-700 dark:text-slate-200">
                                {{ $movimiento->monto_cierre ? '$' . number_format($movimiento->monto_cierre, 2) : '-' }}
                            </span>
                        </div>
                    </div>

                    <!-- Fechas -->
                    <div class="space-y-1.5 text-[11px] text-slate-500 dark:text-slate-400 pt-0.5">
                        <div class="flex items-center justify-between">
                            <span class="font-medium text-slate-400">Apertura:</span>
                            <span class="font-mono font-semibold text-slate-700 dark:text-slate-300">
                                {{ $movimiento->fecha_apertura ? \Carbon\Carbon::parse($movimiento->fecha_apertura)->format('d/m/Y H:i') : '-' }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="font-medium text-slate-400">Cierre:</span>
                            <span class="font-mono font-semibold text-slate-700 dark:text-slate-300">
                                {{ $movimiento->fecha_cierre ? \Carbon\Carbon::parse($movimiento->fecha_cierre)->format('d/m/Y H:i') : '-' }}
                            </span>
                        </div>
                    </div>

                </div>
            @empty
                <div class="p-12 text-center text-xs text-slate-400">
                    No hay registros de movimientos en la base de datos.
                </div>
            @endforelse
        </div>

        <!-- 2. VISTA DE TABLA (ESCRITORIO >= 768px) -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr>
                        <th class="table-th">Usuario</th>
                        <th class="table-th">Apertura</th>
                        <th class="table-th">Cierre</th>
                        <th class="table-th">Monto Inicial</th>
                        <th class="table-th">Monto Cierre</th>
                        <th class="table-th text-center">Movimientos</th>
                        <th class="table-th text-center">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($historial as $movimiento)
                        <tr class="table-tr">
                            <td class="table-td">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-indigo-500/20 to-purple-500/20 border border-indigo-500/30 flex items-center justify-center font-black text-indigo-600 dark:text-indigo-400 text-xs shrink-0">
                                        {{ strtoupper(substr($movimiento->user->name ?? 'U', 0, 2)) }}
                                    </div>
                                    <span class="font-bold text-slate-900 dark:text-white">
                                        {{ $movimiento->user->name ?? 'N/A' }}
                                    </span>
                                </div>
                            </td>
                            <td class="table-td font-mono text-[11px] text-slate-500 dark:text-slate-400 whitespace-nowrap">
                                {{ $movimiento->fecha_apertura ? \Carbon\Carbon::parse($movimiento->fecha_apertura)->format('d/m/Y H:i') : '-' }}
                            </td>
                            <td class="table-td font-mono text-[11px] text-slate-500 dark:text-slate-400 whitespace-nowrap">
                                {{ $movimiento->fecha_cierre ? \Carbon\Carbon::parse($movimiento->fecha_cierre)->format('d/m/Y H:i') : '-' }}
                            </td>
                            <td class="table-td font-mono font-bold text-emerald-500 dark:text-emerald-400">
                                ${{ number_format($movimiento->monto_apertura, 2) }}
                            </td>
                            <td class="table-td font-mono font-bold text-slate-800 dark:text-slate-200">
                                {{ $movimiento->monto_cierre ? '$' . number_format($movimiento->monto_cierre, 2) : '-' }}
                            </td>
                            
                            <td class="table-td text-center whitespace-nowrap">
                                <button type="button" @click="verMovimientos({{ json_encode([
                                    'id' => $movimiento->id,
                                    'user' => $movimiento->user->name ?? 'N/A',
                                    'raw_monto_apertura' => $movimiento->monto_apertura,
                                    'monto_apertura' => number_format($movimiento->monto_apertura, 2),
                                    'monto_cierre' => $movimiento->monto_cierre ? number_format($movimiento->monto_cierre, 2) : 'No cerrado',
                                    'fecha_apertura' => $movimiento->fecha_apertura ? \Carbon\Carbon::parse($movimiento->fecha_apertura)->format('d/m/Y H:i') : '-',
                                    'fecha_cierre' => $movimiento->fecha_cierre ? \Carbon\Carbon::parse($movimiento->fecha_cierre)->format('d/m/Y H:i') : '-',
                                    'ventas' => $movimiento->ventas ?? [],
                                    'gastos' => $movimiento->gastos ?? []
                                ]) }})" 
                                class="w-8 h-8 rounded-xl bg-indigo-500/10 hover:bg-indigo-500/20 border border-indigo-500/30 text-indigo-500 inline-flex items-center justify-center transition cursor-pointer shadow-xs" title="Ver historial de movimientos">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </button>
                            </td>

                            <td class="table-td text-center whitespace-nowrap">
                                @if($movimiento->estado === 'abierta')
                                    <span class="badge-emerald">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shadow-sm shadow-emerald-500"></span>
                                        Abierta
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-wider bg-slate-500/10 text-slate-500 dark:text-slate-400 border border-slate-500/20">
                                        Cerrada
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-16 text-center text-slate-400 dark:text-slate-500 font-medium">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <svg class="w-8 h-8 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <p>No hay registros de movimientos en la base de datos.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- PAGINACIÓN -->
        @if(isset($historial) && method_exists($historial, 'hasPages') && $historial->hasPages())
            <div class="p-4 border-t border-slate-200/80 dark:border-slate-800/80 bg-slate-50/50 dark:bg-[#070a11]">
                {{ $historial->links() }}
            </div>
        @endif
    </div>

    <!-- MODAL DETALLE DE MOVIMIENTOS DEL TURNO (Centrado dinámico adaptativo al menú lateral) -->
    <div x-show="openModal" 
         class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4 transition-all duration-300"
         :style="window.innerWidth >= 768 ? `padding-left: ${sidebarWidth}px` : 'padding-left: 1rem'"
         x-cloak>
        <div class="card-base w-full max-w-3xl p-0 overflow-hidden shadow-2xl animate-in fade-in zoom-in duration-200 max-h-[90vh] flex flex-col" @click.away="openModal = false">
            
            {{-- Encabezado del Modal --}}
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-[#070a11]/80 flex items-center justify-between shrink-0">
                <div>
                    <h3 class="text-xs sm:text-sm font-black text-slate-800 dark:text-white uppercase tracking-wider">Detalle del Turno #<span x-text="turnoSeleccionado?.id"></span></h3>
                    <p class="text-[11px] text-slate-400 font-bold mt-0.5">Operado por: <span class="text-indigo-500" x-text="turnoSeleccionado?.user"></span></p>
                </div>
                <button type="button" @click="openModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-white cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Cuerpo con scroll --}}
            <div class="p-6 space-y-6 overflow-y-auto flex-1 text-xs">
                
                {{-- Resumen de montos centrados (Monto Inicial, Monto Esperado, Tarjeta, Transferencia) --}}
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 p-4 rounded-xl bg-slate-100/60 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700">
                    <div class="flex flex-col items-center justify-center text-center">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Monto Inicial</span>
                        <p class="font-black text-emerald-600 dark:text-emerald-400 text-sm" x-text="`$${turnoSeleccionado?.monto_apertura}`"></p>
                    </div>
                    <div class="flex flex-col items-center justify-center text-center">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Monto Esperado</span>
                        <p class="font-black text-indigo-600 dark:text-indigo-400 text-sm" x-text="`$${montoEsperado}`"></p>
                    </div>
                    <div class="flex flex-col items-center justify-center text-center">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Monto Tarjeta</span>
                        <p class="font-black text-blue-600 dark:text-blue-400 text-sm" x-text="`$${montoTarjeta}`"></p>
                    </div>
                    <div class="flex flex-col items-center justify-center text-center">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Monto Transferencia</span>
                        <p class="font-black text-purple-600 dark:text-purple-400 text-sm" x-text="`$${montoTransferencia}`"></p>
                    </div>
                </div>

                {{-- Sección de Ventas --}}
                <div>
                    <h4 class="font-black text-slate-800 dark:text-white uppercase tracking-wider mb-2 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Ventas Realizadas
                    </h4>
                    <div class="border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden">
                        <table class="w-full text-left">
                            <thead class="bg-slate-50 dark:bg-slate-800/50 text-[10px] uppercase text-slate-400">
                                <tr>
                                    <th class="p-2.5">Total</th>
                                    <th class="p-2.5">Recibido</th>
                                    <th class="p-2.5">Cambio</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                <template x-for="venta in (turnoSeleccionado?.ventas || [])">
                                    <tr>
                                        <td class="p-2.5 font-bold text-emerald-600" x-text="`$${parseFloat(venta.total || 0).toFixed(2)}`"></td>
                                        <td class="p-2.5 text-slate-600 dark:text-slate-300" x-text="`$${parseFloat(venta.monto_recibido || 0).toFixed(2)}`"></td>
                                        <td class="p-2.5 text-indigo-500" x-text="`$${parseFloat(venta.cambio || 0).toFixed(2)}`"></td>
                                    </tr>
                                </template>
                                <template x-if="!turnoSeleccionado?.ventas || turnoSeleccionado.ventas.length === 0">
                                    <tr>
                                        <td colspan="3" class="p-4 text-center text-slate-400 font-semibold">No hay ventas registradas en este turno.</td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Sección de Gastos --}}
                <div>
                    <h4 class="font-black text-slate-800 dark:text-white uppercase tracking-wider mb-2 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-rose-500"></span> Gastos y Salidas
                    </h4>
                    <div class="border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden">
                        <table class="w-full text-left">
                            <thead class="bg-slate-50 dark:bg-slate-800/50 text-[10px] uppercase text-slate-400">
                                <tr>
                                    <th class="p-2.5">Concepto</th>
                                    <th class="p-2.5">Monto</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                <template x-for="gasto in (turnoSeleccionado?.gastos || [])">
                                    <tr>
                                        <td class="p-2.5 font-bold text-slate-800 dark:text-white" x-text="gasto.reason || gasto.concepto || 'Gasto general'"></td>
                                        <td class="p-2.5 font-black text-rose-600" x-text="`-$${parseFloat(gasto.total || gasto.monto || 0).toFixed(2)}`"></td>
                                    </tr>
                                </template>
                                <template x-if="!turnoSeleccionado?.gastos || turnoSeleccionado.gastos.length === 0">
                                    <tr>
                                        <td colspan="2" class="p-4 text-center text-slate-400 font-semibold">No hay gastos registrados en este turno.</td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

            {{-- Pie del Modal --}}
            <div class="px-6 py-3 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-[#070a11]/80 flex justify-end shrink-0">
                <button type="button" @click="openModal = false" class="px-4 py-2 bg-slate-200 dark:bg-slate-800 hover:bg-slate-300 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs rounded-xl transition cursor-pointer">
                    Cerrar Ventana
                </button>
            </div>

        </div>
    </div>

</div>
</x-app-container>
@endsection