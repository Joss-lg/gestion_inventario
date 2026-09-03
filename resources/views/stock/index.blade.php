@extends('layouts.app')

@section('title', 'SCGI - Movimientos de Stock')
@section('header_title', 'Movimientos')

@section('content')
<x-app-container>
    <div class="space-y-8" x-data="stockManagement()">
        <div class="flex flex-col gap-1">
            <nav class="text-xs font-bold text-[#FF6B4A] tracking-wide uppercase">SCGI <span class="mx-1 text-slate-400">/</span> Inventarios</nav>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mt-1">
                <div>
                    <h1 class="page-title">Movimientos de Stock</h1>
                    <p class="page-subtitle">Registra entradas y salidas operativas para el control del taller</p>
                </div>
                <button type="button" @click="openCreateModal()" class="btn-primary w-full sm:w-auto uppercase tracking-wider cursor-pointer">
                    <span>Registrar Movimiento</span>
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            <div class="kpi-card"><span class="kpi-value">{{ $totalMovements ?? 0 }}</span><span class="kpi-label">Total movimientos</span></div>
            <div class="kpi-card"><span class="kpi-value text-emerald-500 dark:text-emerald-400">{{ $totalEntradas ?? 0 }}</span><span class="kpi-label">Entradas registradas</span></div>
            <div class="kpi-card"><span class="kpi-value text-rose-500 dark:text-rose-400">{{ $totalSalidas ?? 0 }}</span><span class="kpi-label">Salidas registradas</span></div>
        </div>

        <div class="card-base !p-4 flex flex-col sm:flex-row items-center justify-between gap-4">
            <input type="text" x-model="searchQuery" placeholder="Buscar por producto o motivo..." class="form-input w-full sm:max-w-md">
            <select x-model="selectedTypeFilter" class="form-input !w-auto cursor-pointer w-full sm:w-auto">
                <option value="">Todos los tipos</option>
                <option value="entrada">Entradas</option>
                <option value="salida">Salidas</option>
            </select>
        </div>

        <div class="table-container">
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead><tr><th class="table-th">ID</th><th class="table-th">Producto</th><th class="table-th">Tipo</th><th class="table-th">Cantidad</th><th class="table-th">Motivo</th><th class="table-th">Registrado por</th><th class="table-th">Fecha</th></tr></thead>
                    <tbody>
                        @forelse($movements as $movement)
                            <tr class="table-tr" x-show="(!searchQuery || '{{ strtolower(addslashes($movement->product->name ?? '')) }}'.includes(searchQuery.toLowerCase()) || '{{ strtolower(addslashes($movement->reason ?? '')) }}'.includes(searchQuery.toLowerCase())) && (!selectedTypeFilter || '{{ $movement->type }}' === selectedTypeFilter)">
                                <td class="table-td">#{{ $movement->id }}</td>
                                <td class="table-td font-bold text-slate-900 dark:text-white">{{ $movement->product->name ?? 'N/A' }}</td>
                                <td class="table-td"><span class="{{ $movement->type === 'entrada' ? 'badge-emerald' : 'badge-rose' }}">{{ ucfirst($movement->type) }}</span></td>
                                <td class="table-td font-mono font-bold">{{ $movement->type === 'entrada' ? '+' : '-' }}{{ $movement->quantity }} pzas</td>
                                <td class="table-td text-slate-500 dark:text-slate-400">{{ $movement->reason }}</td>
                                <td class="table-td">{{ $movement->user->name ?? 'Sistema' }}</td>
                                <td class="table-td text-slate-400 whitespace-nowrap">{{ $movement->created_at?->format('d/m/Y') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="py-16 text-center text-slate-400">No se encontraron movimientos de stock registrados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="block md:hidden divide-y divide-slate-100 dark:divide-slate-800/60">
                @forelse($movements as $movement)
                    <div class="p-4 space-y-2" x-show="(!searchQuery || '{{ strtolower(addslashes($movement->product->name ?? '')) }}'.includes(searchQuery.toLowerCase()) || '{{ strtolower(addslashes($movement->reason ?? '')) }}'.includes(searchQuery.toLowerCase())) && (!selectedTypeFilter || '{{ $movement->type }}' === selectedTypeFilter)">
                        <div class="flex justify-between gap-3"><strong>#{{ $movement->id }} {{ $movement->product->name ?? 'N/A' }}</strong><span>{{ ucfirst($movement->type) }}</span></div>
                        <div class="text-xs text-slate-500">{{ $movement->reason }} · {{ $movement->quantity }} pzas</div>
                    </div>
                @empty
                    <div class="py-16 text-center text-slate-400">No se encontraron movimientos de stock registrados.</div>
                @endforelse
            </div>
        </div>

        <x-modal name="create" title="Registrar Movimiento" maxWidth="max-w-lg">
            <form action="{{ route('stock.store') }}" method="POST" class="relative z-10 flex flex-col flex-1 min-h-0 bg-transparent">
                @csrf
                <div class="p-5 sm:p-7 space-y-4 overflow-y-auto flex-1 min-h-0">
                    <div class="space-y-1.5"><label class="form-label">Producto</label><select name="product_id" x-model="currentMovement.product_id" required class="form-input cursor-pointer"><option value="" disabled>Selecciona un artículo...</option>@foreach($products as $product)<option value="{{ $product->id }}">{{ $product->name }} (Stock actual: {{ $product->stock }})</option>@endforeach</select></div>
                    <div class="space-y-1.5"><label class="form-label">Tipo de Movimiento</label><div class="grid grid-cols-2 gap-3"><label><input type="radio" name="type" value="entrada" x-model="currentMovement.type"> Entrada</label><label><input type="radio" name="type" value="salida" x-model="currentMovement.type"> Salida</label></div></div>
                    <div class="space-y-1.5"><label class="form-label">Cantidad</label><input type="number" name="quantity" x-model.number="currentMovement.quantity" min="1" required class="form-input"></div>
                    <div class="space-y-1.5"><label class="form-label">Motivo del Movimiento</label><input type="text" name="reason" x-model="currentMovement.reason_custom" required class="form-input"></div>
                    <div class="space-y-1.5"><label class="form-label">Notas u Observaciones (Opcional)</label><textarea name="notes" x-model="currentMovement.notes" rows="2" class="form-input resize-none"></textarea></div>
                </div>
                <div class="flex items-center justify-end gap-3 p-5 sm:px-7 border-t border-slate-100 dark:border-white/5"><button type="button" @click="closeModal('create')" class="btn-secondary">Cancelar</button><button type="submit" class="btn-primary">Registrar</button></div>
            </form>
        </x-modal>
    </div>
</x-app-container>
@endsection

@push('scripts')
<script>window.sessionSuccess = @json(session('success')); window.sessionError = @json(session('error'));</script>
<script src="{{ asset('js/components/stock-management.js') }}"></script>
@endpush
