@extends('layouts.app')

@section('title', 'SCGI - Movimientos de Stock')
@section('header_title', 'Movimientos')

@section('content')
<x-app-container>
<div class="space-y-8" x-data="stockManagement()">

    {{-- HEADER --}}
    <div class="flex flex-col gap-1">
        <nav class="text-xs font-bold text-indigo-500 tracking-wide uppercase">
            SCGI <span class="mx-1 text-slate-400 dark:text-slate-600">/</span> <span class="text-slate-600 dark:text-slate-300">Inventarios</span>
        </nav>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mt-1">
            <div>
                <h1 class="page-title">Movimientos de Stock</h1>
                <p class="page-subtitle">Registra entradas y salidas operativas para el control del taller</p>
            </div>
            <button type="button" @click="openCreateModal()" class="btn-primary w-full sm:w-auto uppercase tracking-wider cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                <span>Registrar Movimiento</span>
            </button>
        </div>
    </div>

    {{-- TARJETAS DE MÉTRICAS (KPIS) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        <div class="kpi-card">
            <div class="flex items-center justify-between">
                <div>
                    <span class="kpi-value">{{ $totalMovements ?? 0 }}</span>
                    <span class="kpi-label">Total movimientos</span>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-500 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                </div>
            </div>
        </div>

        <div class="kpi-card">
            <div class="flex items-center justify-between">
                <div>
                    <span class="kpi-value text-emerald-500 dark:text-emerald-400">{{ $totalEntradas ?? 0 }}</span>
                    <span class="kpi-label">Entradas registradas</span>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-500 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/></svg>
                </div>
            </div>
        </div>

        <div class="kpi-card sm:col-span-2 lg:col-span-1">
            <div class="flex items-center justify-between">
                <div>
                    <span class="kpi-value text-rose-500 dark:text-rose-400">{{ $totalSalidas ?? 0 }}</span>
                    <span class="kpi-label">Salidas registradas</span>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-500 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"/></svg>
                </div>
            </div>
        </div>
    </div>

    {{-- BÚSQUEDA Y FILTROS --}}
    <div class="card-base !p-4 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="relative w-full sm:max-w-md">
            <span class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </span>
            <input type="text" x-model="searchQuery" placeholder="Buscar por producto o motivo..." class="form-input !pl-11">
        </div>
        <div class="flex items-center gap-3 w-full sm:w-auto justify-end">
            <select x-model="selectedTypeFilter" class="form-input !w-auto cursor-pointer w-full sm:w-auto">
                <option value="">Todos los tipos</option>
                <option value="entrada">Entradas</option>
                <option value="salida">Salidas</option>
            </select>
        </div>
    </div>

    {{-- TABLA MAESTRA Y VISTA MÓVIL --}}
    <div class="table-container">
        
        {{-- VISTA ESCRITORIO ( >= 768px ) --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr>
                        <th class="table-th w-24">ID</th>
                        <th class="table-th">Producto</th>
                        <th class="table-th w-32">Tipo</th>
                        <th class="table-th w-32">Cantidad</th>
                        <th class="table-th">Motivo</th>
                        <th class="table-th w-44">Registrado por</th>
                        <th class="table-th w-32">Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movements as $movement)
                        <tr class="table-tr"
                            x-show="(!searchQuery || '{{ strtolower(addslashes($movement->product->name ?? '')) }}'.includes(searchQuery.toLowerCase()) || '{{ strtolower(addslashes($movement->reason ?? '')) }}'.includes(searchQuery.toLowerCase())) && (!selectedTypeFilter || '{{ $movement->type }}' === selectedTypeFilter)">
                            
                            <td class="table-td">
                                <span class="px-2.5 py-1 bg-slate-100 dark:bg-slate-800/80 rounded-lg font-mono text-[11px] font-bold text-slate-500 dark:text-slate-400">
                                    #{{ $movement->id }}
                                </span>
                            </td>
                            <td class="table-td font-bold text-slate-900 dark:text-white">{{ $movement->product->name ?? 'N/A' }}</td>
                            <td class="table-td">
                                <span class="{{ $movement->type === 'entrada' ? 'badge-emerald' : 'badge-rose' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $movement->type === 'entrada' ? 'bg-emerald-500 shadow-sm shadow-emerald-500' : 'bg-rose-500 shadow-sm shadow-rose-500' }}"></span>
                                    {{ ucfirst($movement->type) }}
                                </span>
                            </td>
                            <td class="table-td font-mono font-bold text-indigo-600 dark:text-indigo-400">
                                {{ $movement->type === 'entrada' ? '+' : '-' }}{{ $movement->quantity }} pzas
                            </td>
                            <td class="table-td text-slate-500 dark:text-slate-400 font-medium">{{ $movement->reason }}</td>
                            <td class="table-td font-semibold text-slate-700 dark:text-slate-300">{{ $movement->user->name ?? 'Sistema' }}</td>
                            <td class="table-td text-slate-400 dark:text-slate-500 text-[11px] whitespace-nowrap font-mono">
                                {{ $movement->created_at ? $movement->created_at->format('d/m/Y') : '' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-16 text-center text-slate-400 dark:text-slate-500 font-medium">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <svg class="w-8 h-8 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                                    <p>No se encontraron movimientos de stock registrados.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- VISTA MÓVIL ( < 768px ) --}}
        <div class="block md:hidden divide-y divide-slate-100 dark:divide-slate-800/60">
            @forelse($movements as $movement)
                <div class="p-4 space-y-3 hover:bg-indigo-50/40 dark:hover:bg-indigo-500/5 transition-colors"
                     x-show="(!searchQuery || '{{ strtolower(addslashes($movement->product->name ?? '')) }}'.includes(searchQuery.toLowerCase()) || '{{ strtolower(addslashes($movement->reason ?? '')) }}'.includes(searchQuery.toLowerCase())) && (!selectedTypeFilter || '{{ $movement->type }}' === selectedTypeFilter)">
                    
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2.5">
                            <span class="px-2.5 py-1 bg-slate-100 dark:bg-slate-800/80 rounded-lg font-mono text-[11px] font-bold text-slate-500 dark:text-slate-400 shrink-0">
                                #{{ $movement->id }}
                            </span>
                            <span class="{{ $movement->type === 'entrada' ? 'badge-emerald' : 'badge-rose' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $movement->type === 'entrada' ? 'bg-emerald-500 shadow-sm shadow-emerald-500' : 'bg-rose-500 shadow-sm shadow-rose-500' }}"></span>
                                {{ ucfirst($movement->type) }}
                            </span>
                        </div>

                        <span class="font-mono font-bold text-xs text-indigo-600 dark:text-indigo-400 shrink-0">
                            {{ $movement->type === 'entrada' ? '+' : '-' }}{{ $movement->quantity }} pzas
                        </span>
                    </div>

                    <div>
                        <div class="font-bold text-slate-900 dark:text-white text-xs">{{ $movement->product->name ?? 'N/A' }}</div>
                        <div class="text-[11px] text-slate-500 dark:text-slate-400 font-medium mt-0.5">{{ $movement->reason }}</div>
                    </div>

                    <div class="flex items-center justify-between pt-1 text-[11px] border-t border-slate-100 dark:border-slate-800/40">
                        <span class="font-semibold text-slate-600 dark:text-slate-300">
                            Reg: <span class="text-slate-400 dark:text-slate-500 font-normal">{{ $movement->user->name ?? 'Sistema' }}</span>
                        </span>
                        <span class="text-slate-400 dark:text-slate-500 font-mono">
                            {{ $movement->created_at ? $movement->created_at->format('d/m/Y') : '' }}
                        </span>
                    </div>
                </div>
            @empty
                <div class="py-16 text-center text-slate-400 dark:text-slate-500 font-medium px-4">
                    <div class="flex flex-col items-center justify-center gap-2">
                        <svg class="w-8 h-8 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                        <p>No se encontraron movimientos de stock registrados.</p>
                    </div>
                </div>
            @endforelse
        </div>

    </div>

    {{-- ========================================================= --}}
    {{-- MODAL REGISTRAR MOVIMIENTO --}}
    {{-- ========================================================= --}}
    <x-modal name="create" title="Registrar Movimiento" maxWidth="max-w-lg">
        <form action="{{ route('stock.store') }}" method="POST" class="relative z-10 flex flex-col flex-1 min-h-0 bg-transparent">
            @csrf
            <div class="p-5 sm:p-7 space-y-4 overflow-y-auto flex-1 min-h-0 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">

                {{-- PRODUCTO --}}
                <div class="space-y-1.5">
                    <label class="form-label">Producto</label>
                    <select name="product_id" 
                            x-model="currentMovement.product_id"
                            required
                            class="form-input cursor-pointer">
                        <option value="" disabled selected>Selecciona un artículo...</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}">
                                {{ $product->name }} (Stock actual: {{ $product->stock }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- TIPO DE MOVIMIENTO --}}
                <div class="space-y-1.5">
                    <label class="form-label">Tipo de Movimiento</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="relative flex flex-col p-3.5 rounded-2xl border-2 cursor-pointer transition-all duration-200"
                               :class="currentMovement.type === 'entrada' ? 'border-emerald-500 bg-emerald-500/10 shadow-lg shadow-emerald-500/10' : 'border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-[#070a11] hover:border-slate-300 dark:hover:border-slate-700'">
                            <input type="radio" name="type" value="entrada" x-model="currentMovement.type" @change="currentMovement.reason_preset = ''" class="sr-only">
                            <span class="text-xs font-black text-slate-900 dark:text-white flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 shadow-sm shadow-emerald-500"></span>
                                Entrada
                            </span>
                            <span class="text-[10px] text-slate-400 mt-0.5">Sumar artículos</span>
                        </label>

                        <label class="relative flex flex-col p-3.5 rounded-2xl border-2 cursor-pointer transition-all duration-200"
                               :class="currentMovement.type === 'salida' ? 'border-rose-500 bg-rose-500/10 shadow-lg shadow-rose-500/10' : 'border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-[#070a11] hover:border-slate-300 dark:hover:border-slate-700'">
                            <input type="radio" name="type" value="salida" x-model="currentMovement.type" @change="currentMovement.reason_preset = ''" class="sr-only">
                            <span class="text-xs font-black text-slate-900 dark:text-white flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-rose-500 shadow-sm shadow-rose-500"></span>
                                Salida
                            </span>
                            <span class="text-[10px] text-slate-400 mt-0.5">Restar artículos</span>
                        </label>
                    </div>
                </div>

                {{-- CANTIDAD --}}
                <div class="space-y-1.5">
                    <label class="form-label">Cantidad</label>
                    <input type="number" 
                           name="quantity" 
                           x-model.number="currentMovement.quantity" 
                           min="1"
                           required
                           class="form-input">
                </div>

                {{-- MOTIVO --}}
                <div class="space-y-1.5">
                    <label class="form-label">Motivo del Movimiento</label>
                    <select x-model="currentMovement.reason_preset" required class="form-input cursor-pointer">
                        <option value="" disabled selected>Selecciona una opción...</option>
                        
                        <template x-if="currentMovement.type === 'entrada'">
                            <optgroup label="Motivos de Entrada">
                                <option value="Compra de insumos / piezas">Compra de insumos / piezas</option>
                                <option value="Devolución de cliente">Devolución de cliente</option>
                                <option value="Ajuste por inventario físico">Ajuste por inventario físico</option>
                            </optgroup>
                        </template>

                        <template x-if="currentMovement.type === 'salida'">
                            <optgroup label="Motivos de Salida">
                                <option value="Uso en servicio / taller">Uso en servicio / taller</option>
                                <option value="Pieza dañada o defectuosa">Pieza dañada o defectuosa</option>
                                <option value="Ajuste por inventario físico">Ajuste por inventario físico</option>
                            </optgroup>
                        </template>

                        <option value="Otro">Otro motivo (Especificar...)</option>
                    </select>

                    <div x-show="currentMovement.reason_preset === 'Otro'" class="mt-2">
                        <input type="text" 
                               x-model="currentMovement.reason_custom"
                               :required="currentMovement.reason_preset === 'Otro'"
                               placeholder="Escribe el motivo personalizado..." 
                               class="form-input !border-indigo-500">
                    </div>

                    <input type="hidden" name="reason" :value="finalReason">
                </div>

                {{-- NOTAS / OBSERVACIONES --}}
                <div class="space-y-1.5">
                    <label class="form-label">Notas u Observaciones (Opcional)</label>
                    <textarea name="notes" 
                              x-model="currentMovement.notes" 
                              rows="2" 
                              placeholder="Detalles adicionales del movimiento..."
                              class="form-input resize-none"></textarea>
                </div>

            </div>

            {{-- FOOTER --}}
            <div class="flex items-center justify-end gap-3 p-5 sm:px-7 border-t border-slate-100 dark:border-white/5 bg-transparent shrink-0">
                <button type="button" @click="closeModal('create')" class="btn-secondary">Cancelar</button>
                <button type="submit" class="btn-primary">Registrar</button>
            </div>
        </form>
    </x-modal>

</div>
</x-app-container>
@endsection

@push('scripts')
<script>
    window.sessionSuccess = @json(session('success'));
    window.sessionError = @json(session('error'));
</script>
<script src="{{ asset('js/components/stock-management.js') }}"></script>
@endpush