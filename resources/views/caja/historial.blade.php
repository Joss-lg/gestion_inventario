@extends('layouts.app')

@section('title', 'SCGI - Historial de Caja')
@section('header_title', 'Historial de Turnos y Caja')

@section('content')
<x-app-container>
<div class="space-y-8" x-data="{ 
    openModal: false,
    showVentaModal: false,
    ventaActual: null,
    turnoSeleccionado: null,
    cargando: false,
    error: false,

    verDetalleVenta(venta) {
        this.ventaActual = venta;
        this.showVentaModal = true;
    },

    async verMovimientos(id) {
        this.openModal = true;
        this.showVentaModal = false;
        this.ventaActual = null;
        this.cargando = true;
        this.error = false;
        this.turnoSeleccionado = null;

        try {
            const res = await fetch(`/caja/historial/${id}`, {
                headers: { 'Accept': 'application/json' }
            });

            if (!res.ok) throw new Error('Error al obtener el turno');

            const data = await res.json();

            this.turnoSeleccionado = {
                id: data.id,
                user: typeof data.user === 'object' ? (data.user?.name || 'N/A') : (data.user || 'N/A'),
                monto_apertura: parseFloat(data.monto_inicial || 0).toFixed(2),
                monto_esperado: parseFloat(data.monto_esperado || 0).toFixed(2),
                monto_cierre: data.monto_cierre !== null && data.monto_cierre !== undefined ? parseFloat(data.monto_cierre).toFixed(2) : null,
                diferencia: (data.monto_cierre !== null && data.monto_cierre !== undefined)
                    ? (parseFloat(data.monto_cierre) - parseFloat(data.monto_esperado || 0)).toFixed(2)
                    : null,
                monto_tarjeta: parseFloat(data.monto_tarjeta || 0).toFixed(2),
                monto_transferencia: parseFloat(data.monto_transferencia || 0).toFixed(2),
                ventas: data.ventas || [],
                gastos: data.gastos || [],
            };
        } catch (e) {
            console.error('Error al cargar el detalle del turno:', e);
            this.error = true;
        } finally {
            this.cargando = false;
        }
    }
}">

    <!-- CABECERA -->
    <div class="flex flex-col gap-1">
        <nav class="text-xs font-bold text-[#FF6B4A] tracking-wide uppercase">
            SCGI <span class="mx-1 text-slate-400 dark:text-slate-600">/</span> <span class="text-slate-600 dark:text-slate-300">Caja</span>
        </nav>
        <div>
            <h1 class="page-title">Historial de Caja</h1>
            <div class="h-1 w-14 rounded-full bg-gradient-to-r from-[#FF4500] to-[#FF8A65] my-2"></div>
            <p class="page-subtitle">Bitácora general de aperturas, cierres y flujos de efectivo de todos los usuarios</p>
        </div>
    </div>

    <!-- CONTENEDOR PRINCIPAL -->
    <div class="table-container ring-1 ring-slate-200 dark:ring-slate-800">
        
        <!-- Header de la tabla -->
        <div class="p-6 bg-slate-50 dark:bg-slate-900/40 border-b border-slate-200 dark:border-slate-800/80 flex items-center justify-between">
            <div>
                <h2 class="text-sm font-black text-slate-900 dark:text-white">Registros del Sistema</h2>
                <p class="page-subtitle">Historial maestro en tiempo real</p>
            </div>
            <div class="w-8 h-8 rounded-xl bg-[#FF4500] text-white flex items-center justify-center shadow-md shadow-[#FF4500]/25">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>

        <!-- 1. VISTA DE TARJETAS (MÓVIL < 768px) -->
        <div class="block md:hidden divide-y divide-slate-100 dark:divide-slate-800/60">
            @forelse($historial as $movimiento)
                <div class="p-5 space-y-4 hover:bg-[#FFF1EC]/30 dark:hover:bg-slate-800/40 transition-colors">
                    
                    <!-- Usuario, Botón de Historial y Estado -->
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-[#FF4500] to-[#FF6B4A] text-white flex items-center justify-center font-black text-xs shrink-0 shadow-sm shadow-[#FF6B4A]/30">
                                {{ strtoupper(substr($movimiento->user->name ?? 'U', 0, 2)) }}
                            </div>
                            <span class="font-bold text-xs text-slate-900 dark:text-white truncate">
                                {{ $movimiento->user->name ?? 'N/A' }}
                            </span>
                        </div>

                        <div class="flex items-center gap-2">
                            {{-- Botón de movimientos en móvil --}}
                            <button type="button" @click="verMovimientos({{ $movimiento->id }})" 
                            class="w-9 h-9 rounded-xl bg-[#FF4500] hover:bg-[#D9431F] text-white flex items-center justify-center transition cursor-pointer shadow-md shadow-[#FF4500]/40 ring-1 ring-[#FF4500]/20" title="Ver movimientos">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
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
                    <div class="grid grid-cols-3 gap-3 bg-white dark:bg-slate-900/40 p-3.5 rounded-2xl border border-slate-200 dark:border-slate-800">
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
                        <div>
                            <span class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-0.5">Diferencia</span>
                            @if(is_null($movimiento->monto_cierre) || is_null($movimiento->monto_esperado))
                                <span class="text-xs font-black text-slate-300 dark:text-slate-600">-</span>
                            @else
                                @php $diferencia = $movimiento->monto_cierre - $movimiento->monto_esperado; @endphp
                                @if(abs($diferencia) < 0.01)
                                    <span class="text-xs font-black text-slate-400">Cuadrada</span>
                                @else
                                    <span class="text-xs font-black {{ $diferencia > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                        {{ $diferencia > 0 ? '+' : '-' }}${{ number_format(abs($diferencia), 2) }}
                                    </span>
                                @endif
                            @endif
                        </div>
                    </div>

                    <!-- Fechas -->
                    <div class="space-y-1.5 text-[11px] text-slate-500 dark:text-slate-400 pt-0.5">
                        <div class="flex items-center justify-between">
                            <span class="font-medium text-slate-400">Apertura:</span>
                            <span class="font-mono font-semibold text-slate-700 dark:text-slate-300">
                                {{ $movimiento->fecha_apertura ? \Carbon\Carbon::parse($movimiento->fecha_apertura)->format('d/m/Y ') : '-' }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="font-medium text-slate-400">Cierre:</span>
                            <span class="font-mono font-semibold text-slate-700 dark:text-slate-300">
                                {{ $movimiento->fecha_cierre ? \Carbon\Carbon::parse($movimiento->fecha_cierre)->format('d/m/Y ') : '-' }}
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
                <thead class="bg-slate-50 dark:bg-slate-900/40 border-b border-slate-200 dark:border-slate-800/80">
                    <tr>
                        <th class="table-th text-slate-600 dark:text-slate-300">Usuario</th>
                        <th class="table-th text-slate-600 dark:text-slate-300">Apertura</th>
                        <th class="table-th text-slate-600 dark:text-slate-300">Cierre</th>
                        <th class="table-th text-slate-600 dark:text-slate-300">Monto Inicial</th>
                        <th class="table-th text-slate-600 dark:text-slate-300">Monto Cierre</th>
                        <th class="table-th text-slate-600 dark:text-slate-300 text-center">Diferencia</th>
                        <th class="table-th text-slate-600 dark:text-slate-300 text-center">Movimientos</th>
                        <th class="table-th text-slate-600 dark:text-slate-300 text-center">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($historial as $movimiento)
                        <tr class="table-tr">
                            <td class="table-td">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-[#FF4500] to-[#FF6B4A] text-white flex items-center justify-center font-black text-xs shrink-0 shadow-sm shadow-[#FF6B4A]/30">
                                        {{ strtoupper(substr($movimiento->user->name ?? 'U', 0, 2)) }}
                                    </div>
                                    <span class="font-bold text-slate-900 dark:text-white">
                                        {{ $movimiento->user->name ?? 'N/A' }}
                                    </span>
                                </div>
                            </td>
                            <td class="table-td font-mono text-[11px] text-slate-500 dark:text-slate-400 whitespace-nowrap">
                                {{ $movimiento->fecha_apertura ? \Carbon\Carbon::parse($movimiento->fecha_apertura)->format('d/m/Y ') : '-' }}
                            </td>
                            <td class="table-td font-mono text-[11px] text-slate-500 dark:text-slate-400 whitespace-nowrap">
                                {{ $movimiento->fecha_cierre ? \Carbon\Carbon::parse($movimiento->fecha_cierre)->format('d/m/Y ') : '-' }}
                            </td>
                            <td class="table-td font-mono font-bold text-emerald-500 dark:text-emerald-400">
                                ${{ number_format($movimiento->monto_apertura, 2) }}
                            </td>
                            <td class="table-td font-mono font-bold text-slate-800 dark:text-slate-200">
                                {{ $movimiento->monto_cierre ? '$' . number_format($movimiento->monto_cierre, 2) : '-' }}
                            </td>

                            {{-- Diferencia: Monto Cierre (real, contado) vs Monto Esperado (calculado por el sistema) --}}
                            <td class="table-td text-center whitespace-nowrap">
                                @if(is_null($movimiento->monto_cierre) || is_null($movimiento->monto_esperado))
                                    <span class="text-slate-300 dark:text-slate-600">-</span>
                                @else
                                    @php
                                        $diferencia = $movimiento->monto_cierre - $movimiento->monto_esperado;
                                    @endphp
                                    @if(abs($diferencia) < 0.01)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider bg-slate-500/10 text-slate-500 dark:text-slate-400 border border-slate-500/20">
                                            Cuadrada
                                        </span>
                                    @elseif($diferencia > 0)
                                        <span class="inline-flex flex-col items-center gap-0.5">
                                            <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                                Sobra
                                            </span>
                                            <span class="font-mono font-bold text-emerald-600 dark:text-emerald-400 text-[11px]">
                                                +${{ number_format($diferencia, 2) }}
                                            </span>
                                        </span>
                                    @else
                                        <span class="inline-flex flex-col items-center gap-0.5">
                                            <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20">
                                                Falta
                                            </span>
                                            <span class="font-mono font-bold text-rose-600 dark:text-rose-400 text-[11px]">
                                                -${{ number_format(abs($diferencia), 2) }}
                                            </span>
                                        </span>
                                    @endif
                                @endif
                            </td>

                            {{-- Botón de Movimientos ubicado entre Diferencia y Estado --}}
                            <td class="table-td text-center whitespace-nowrap">
                                <button type="button" @click="verMovimientos({{ $movimiento->id }})" 
                                class="w-9 h-9 rounded-xl bg-[#FF4500] hover:bg-[#D9431F] text-white inline-flex items-center justify-center transition cursor-pointer shadow-md shadow-[#FF4500]/40 ring-1 ring-[#FF4500]/20" title="Ver historial de movimientos">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
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
                            <td colspan="8" class="py-16 text-center text-slate-400 dark:text-slate-500 font-medium">
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
        </div>

        
        
    <!-- MODAL DETALLE DE MOVIMIENTOS DEL TURNO -->
    <div x-show="openModal" 
         x-cloak
         class="fixed inset-0 z-50 bg-slate-950/70 dark:bg-slate-950/85 backdrop-blur-md flex items-center justify-center p-3 sm:p-5"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95">
        <div class="relative w-full max-w-3xl max-h-[90vh] bg-white dark:bg-[#090d18] text-slate-900 dark:text-slate-100 rounded-[28px] border border-slate-200/80 dark:border-[#FF6B4A]/20 shadow-2xl flex flex-col overflow-hidden my-auto" @click.away="openModal = false">

            {{-- Resplandor Neón Coral --}}
            <div class="hidden dark:block pointer-events-none absolute -top-20 -left-20 w-72 h-72 bg-[#F0552F]/15 rounded-full blur-3xl"></div>
            <div class="hidden dark:block pointer-events-none absolute -bottom-24 -right-16 w-64 h-64 bg-[#FF4500]/10 rounded-full blur-3xl"></div>

            {{-- Encabezado del Modal --}}
            <div class="relative z-10 flex items-center justify-between p-5 sm:px-7 border-b border-slate-100 dark:border-[#FF6B4A]/10 bg-gradient-to-r from-[#FFF1EC]/60 to-transparent dark:from-[#FF6B4A]/[0.04] dark:to-transparent shrink-0">
                <div class="flex items-center gap-3">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#FF6B4A] shadow-[0_0_10px_rgba(255,107,74,0.8)]"></span>
                    <div>
                        <h3 class="text-xs sm:text-sm font-black text-slate-900 dark:text-white uppercase tracking-wider">Detalle del Turno #<span x-text="turnoSeleccionado?.id"></span></h3>
                        <p class="text-[11px] text-slate-400 font-bold mt-0.5">Operado por: <span class="text-[#FF6B4A] dark:text-[#FF8A65]" x-text="turnoSeleccionado?.user"></span></p>
                    </div>
                </div>
                <button type="button" @click="openModal = false" 
                        class="w-8 h-8 rounded-full bg-[#FFF1EC] dark:bg-[#3A120A]/40 hover:bg-[#FFE1D6] dark:hover:bg-[#5C1B0E]/60 text-[#FF6B4A] dark:text-[#FF8A65] hover:text-[#D9431F] dark:hover:text-[#FFB399] flex items-center justify-center font-bold transition-all cursor-pointer border border-[#FFCCB8]/60 dark:border-[#FF6B4A]/20">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Cuerpo con scroll --}}
            <div class="relative z-10 p-6 space-y-6 overflow-y-auto flex-1 text-xs">

                {{-- Estado de carga --}}
                <template x-if="cargando">
                    <div class="text-center py-10 text-slate-400 text-xs font-bold">
                        Cargando datos del turno...
                    </div>
                </template>

                {{-- Estado de error --}}
                <template x-if="error && !cargando">
                    <div class="text-center py-10 text-rose-500 text-xs font-bold">
                        Ocurrió un error al cargar el detalle de este turno.
                    </div>
                </template>

                {{-- Contenido normal --}}
                <template x-if="!cargando && !error && turnoSeleccionado">
                    <div class="space-y-6">

                        {{-- Resumen de montos --}}
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 p-4 rounded-xl bg-gradient-to-br from-[#FFF1EC]/70 to-[#FAEBD7]/40 dark:from-[#3A120A]/20 dark:to-transparent border-l-4 border-[#FF4500] border-y border-r border-[#FFCCB8]/60 dark:border-[#FF6B4A]/20 text-center">
                            <div>
                                <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Monto Inicial</span>
                                <p class="font-black text-emerald-600 dark:text-emerald-400 text-sm" x-text="`$${turnoSeleccionado?.monto_apertura}`"></p>
                            </div>
                            <div>
                                <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Monto Esperado</span>
                                <p class="font-black text-[#F0552F] dark:text-[#FF8A65] text-sm" x-text="`$${turnoSeleccionado?.monto_esperado}`"></p>
                            </div>
                            <div>
                                <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Monto Cierre</span>
                                <p class="font-black text-slate-700 dark:text-slate-200 text-sm" x-text="turnoSeleccionado?.monto_cierre !== null ? `$${turnoSeleccionado?.monto_cierre}` : '-'"></p>
                            </div>
                            <div>
                                <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Monto Tarjeta</span>
                                <p class="font-black text-blue-600 dark:text-blue-400 text-sm" x-text="`$${turnoSeleccionado?.monto_tarjeta}`"></p>
                            </div>
                            <div>
                                <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Monto Transferencia</span>
                                <p class="font-black text-purple-600 dark:text-purple-400 text-sm" x-text="`$${turnoSeleccionado?.monto_transferencia}`"></p>
                            </div>
                            <div>
                                <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Diferencia</span>
                                <template x-if="turnoSeleccionado?.diferencia === null">
                                    <p class="font-black text-slate-400 text-sm">-</p>
                                </template>
                                <template x-if="turnoSeleccionado?.diferencia !== null">
                                    <p class="font-black text-sm"
                                       :class="{
                                            'text-slate-400': Math.abs(parseFloat(turnoSeleccionado?.diferencia)) < 0.01,
                                            'text-emerald-600 dark:text-emerald-400': parseFloat(turnoSeleccionado?.diferencia) >= 0.01,
                                            'text-rose-600 dark:text-rose-400': parseFloat(turnoSeleccionado?.diferencia) <= -0.01
                                       }"
                                       x-text="Math.abs(parseFloat(turnoSeleccionado?.diferencia)) < 0.01 ? 'Cuadrada' : `${parseFloat(turnoSeleccionado?.diferencia) > 0 ? '+' : '-'}$${Math.abs(parseFloat(turnoSeleccionado?.diferencia)).toFixed(2)}`"></p>
                                </template>
                            </div>
                        </div>

                        {{-- Sección de Ventas --}}
                        <div>
                            <h4 class="font-black text-slate-800 dark:text-white uppercase tracking-wider mb-2 flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Ventas Realizadas
                            </h4>
                            <div class="border border-[#FFE1D6] dark:border-[#FF6B4A]/10 rounded-xl overflow-hidden">
                                <table class="w-full text-left">
                                    <thead class="bg-[#FFF1EC]/60 dark:bg-[#3A120A]/30 text-[10px] uppercase text-slate-400">
                                        <tr>
                                            <th class="p-2.5">Productos</th>
                                            <th class="p-2.5">Total</th>
                                            <th class="p-2.5">Recibido</th>
                                            <th class="p-2.5">Cambio/Ref</th>
                                            <th class="p-2.5">Método</th>
                                                            <th class="p-2.5 text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-[#FFE1D6] dark:divide-[#FF6B4A]/10">
                                    <template x-for="venta in (turnoSeleccionado?.ventas || [])" :key="venta.id">
                                     <tr>
                                      {{-- Lista de productos: cada uno en su propia línea con su cantidad exacta --}}
                                                  <td class="p-2.5 align-top">

                                                    <div class="flex flex-col gap-1.5">
                                                        <template x-for="prod in venta.productos" :key="prod.nombre">
                                                            <div class="flex items-center gap-2">
                                                                <span class="inline-flex items-center justify-center min-w-[28px] h-[20px] px-1.5 rounded-lg bg-[#FF6B4A] text-white text-[10px] font-black shrink-0"
                                                                      x-text="`${prod.cantidad}x`"></span>
                                                                <span class="text-[11px] font-semibold text-slate-700 dark:text-slate-300" x-text="prod.nombre"></span>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </td>
                                                <td class="p-2.5 font-bold text-emerald-600" x-text="`$${parseFloat(venta.total || 0).toFixed(2)}`"></td>
                                                <td class="p-2.5 text-slate-600 dark:text-slate-300" x-text="`$${parseFloat(venta.monto_recibido || 0).toFixed(2)}`"></td>
                                                <td class="p-2.5 text-[#FF6B4A]" x-text="venta.metodo === 'EFECTIVO' ? `$${parseFloat(venta.cambio || 0).toFixed(2)}` : (venta.referencia || '-')"></td>
                                                <td class="p-2.5">
                                                    <span class="px-2 py-0.5 rounded-lg text-[10px] font-extrabold"
                                                        :class="{
                                                            'bg-blue-500/10 text-blue-400 border border-blue-500/20': venta.metodo === 'TARJETA',
                                                            'bg-purple-500/10 text-purple-400 border border-purple-500/20': venta.metodo === 'TRANSFERENCIA',
                                                            'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20': venta.metodo === 'EFECTIVO'
                                                        }"
                                                        x-text="venta.metodo"></span>
                                                </td>
                                                <td class="p-2.5 text-center">
                                                    <button type="button"
                                                            @click="verDetalleVenta(venta)"
                                                            title="Ver productos de la venta"
                                                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-emerald-400 bg-emerald-500/10 border border-emerald-500/20 hover:bg-emerald-500/20 hover:text-emerald-300 transition-colors cursor-pointer">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                        </svg>
                                                    </button>
                                                </td>
                                            </tr>
                                        </template>
                                        <template x-if="!turnoSeleccionado?.ventas || turnoSeleccionado.ventas.length === 0">
                                            <tr>
                                                <td colspan="6" class="p-4 text-center text-slate-400 font-semibold">No hay ventas registradas en este turno.</td>
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
                            <div class="border border-[#FFE1D6] dark:border-[#FF6B4A]/10 rounded-xl overflow-hidden">
                                <table class="w-full text-left">
                                    <thead class="bg-[#FFF1EC]/60 dark:bg-[#3A120A]/30 text-[10px] uppercase text-slate-400">
                                        <tr>
                                            <th class="p-2.5">Concepto</th>
                                            <th class="p-2.5">Monto</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-[#FFE1D6] dark:divide-[#FF6B4A]/10">
                                        <template x-for="(gasto, index) in (turnoSeleccionado?.gastos || [])" :key="index">
                                            <tr>
                                                <td class="p-2.5 font-bold text-slate-800 dark:text-white" x-text="gasto.concepto || 'Gasto general'"></td>
                                                <td class="p-2.5 font-black text-rose-600" x-text="`-$${parseFloat(gasto.monto || 0).toFixed(2)}`"></td>
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
                </template>

            </div>

            {{-- Pie del Modal --}}
            <div class="relative z-10 px-6 py-3 border-t border-slate-100 dark:border-[#FF6B4A]/10 bg-transparent flex justify-end shrink-0">
                <button type="button" @click="openModal = false" 
                        class="px-5 py-2.5 bg-gradient-to-r from-[#FF4500] to-[#FF6B4A] hover:brightness-110 text-white font-bold text-xs rounded-2xl transition cursor-pointer shadow-md shadow-[#FF4500]/25">
                    Cerrar Ventana
                </button>
            </div>

        </div>
    </div>

    {{-- SUBMODAL DETALLE DE PRODUCTOS DE LA VENTA --}}
    <div x-show="showVentaModal"
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         @keydown.escape.window="showVentaModal = false"
         class="fixed inset-0 z-[60] bg-slate-950/75 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.away="showVentaModal = false"
             class="w-full max-w-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-2xl overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/60">
                <div>
                    <h3 class="text-sm font-black uppercase tracking-wider text-slate-900 dark:text-white">Detalle de Venta</h3>
                    <p class="text-[11px] text-slate-400 mt-1" x-text="ventaActual ? `Venta #${ventaActual.id}` : ''"></p>
                </div>
                <button type="button"
                        @click="showVentaModal = false"
                        title="Cerrar detalle"
                        class="w-8 h-8 inline-flex items-center justify-center rounded-lg text-slate-400 hover:text-slate-200 hover:bg-slate-700 transition-colors cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="p-5 space-y-3">
                <div class="flex items-center justify-between text-[10px] font-black uppercase tracking-wider text-slate-400">
                    <span>Producto</span>
                    <span>Subtotal</span>
                </div>
                <div class="divide-y divide-slate-200 dark:divide-slate-800 border-y border-slate-200 dark:border-slate-800">
                    <template x-for="(producto, index) in (ventaActual?.productos || [])" :key="`${producto.nombre}-${index}`">
                        <div class="flex items-center justify-between gap-4 py-3">
                            <div class="min-w-0">
                                <p class="text-xs font-bold text-slate-800 dark:text-slate-100 truncate" x-text="producto.nombre"></p>
                                <p class="text-[11px] text-slate-400 mt-0.5" x-text="`${producto.cantidad} unidad${producto.cantidad === 1 ? '' : 'es'}`"></p>
                            </div>
                            <span class="text-xs font-black text-emerald-600 dark:text-emerald-400 whitespace-nowrap" x-text="`$${parseFloat(producto.subtotal || 0).toFixed(2)}`"></span>
                        </div>
                    </template>
                </div>
                <div class="flex items-center justify-between pt-1">
                    <span class="text-xs font-black uppercase text-slate-500 dark:text-slate-400">Total venta</span>
                    <span class="text-base font-black text-emerald-600 dark:text-emerald-400" x-text="ventaActual ? `$${parseFloat(ventaActual.total || 0).toFixed(2)}` : '$0.00'"></span>
                </div>
            </div>
        </div>
    </div>

</div>
</x-app-container>
@endsection