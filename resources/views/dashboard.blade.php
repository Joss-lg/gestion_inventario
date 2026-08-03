@extends('layouts.app')

@section('title', 'SCGI - Dashboard')
@section('header_title', 'Panel General')

@section('content')
<x-app-container>
<div class="space-y-6">

    <!-- BREADCRUMB & HEADER + SELECTOR DE MES -->
    <div class="flex flex-col gap-1">
        <nav class="text-xs font-semibold text-slate-400">
            SCGI <span class="mx-1.5 text-slate-300 dark:text-slate-700">/</span> <span class="font-bold text-slate-600 dark:text-slate-300">Dashboard</span>
        </nav>
        <div class="mt-1 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl font-black tracking-tight text-slate-900 dark:text-white">Bienvenido, {{ Auth::user()->name ?? 'Colaborador' }}</h1>
                <p class="mt-0.5 text-xs font-medium text-slate-500 capitalize dark:text-slate-400">
                    {{ \Carbon\Carbon::now()->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY') }}
                </p>
            </div>

            <!-- SELECTOR DE MES (CRISTAL) -->
            <form method="GET" action="{{ route('dashboard') }}" class="flex w-full items-center gap-2 rounded-2xl border border-white/80 bg-white/60 p-2 shadow-sm backdrop-blur-md dark:border-slate-800/80 dark:bg-slate-900/60 sm:w-auto">
                <label for="month" class="pl-2 text-xs font-bold text-slate-500 dark:text-slate-400">Mes:</label>
                <input type="month" name="month" id="month" value="{{ $selectedMonth }}" 
                    onchange="this.form.submit()"
                    class="w-full cursor-pointer rounded-xl border border-slate-200/80 bg-white/70 px-3 py-1.5 text-xs font-bold text-slate-700 shadow-2xs focus:ring-2 focus:ring-indigo-500/50 focus:outline-hidden dark:border-slate-700/60 dark:bg-slate-800/70 dark:text-slate-200 sm:w-auto">
            </form>
        </div>
    </div>

    <!-- ALERTA DE STOCK BAJO -->
    @if(isset($lowStockProducts) && $lowStockProducts->count() > 0)
    <div class="space-y-3 rounded-3xl border border-rose-500/30 bg-rose-500/10 p-6 shadow-lg shadow-rose-500/5 backdrop-blur-md dark:border-rose-500/20 dark:bg-rose-950/40">
        <div class="flex items-center gap-2 text-xs font-extrabold text-rose-700 dark:text-rose-400">
            <svg class="h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <span>{{ $lowStockProducts->count() }} productos requieren reposición urgente</span>
        </div>
        <div class="space-y-1.5 pl-6">
            @foreach($lowStockProducts as $product)
                <div class="flex flex-col gap-1 text-xs text-rose-950 dark:text-rose-200 sm:flex-row sm:items-center sm:gap-2">
                    <span class="w-fit rounded-lg border border-rose-200/80 bg-white/80 px-2 py-0.5 font-mono text-[10px] font-bold text-rose-700 backdrop-blur-xs dark:border-rose-700/50 dark:bg-rose-900/50 dark:text-rose-300">{{ $product->sku ?? 'N/D' }}</span>
                    <span class="font-bold">{{ $product->name ?? 'Producto' }}</span>
                    <span class="text-rose-600 dark:text-rose-400">— stock actual:</span>
                    <span class="font-extrabold">{{ $product->stock ?? 0 }} pza</span>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- TARJETAS DE MÉTRICAS -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
        
        <!-- Valor Total del Inventario -->
        <div class="group flex flex-col justify-between rounded-3xl border border-indigo-500/20 bg-white/60 p-6 shadow-lg shadow-indigo-500/5 backdrop-blur-md transition-all duration-300 hover:-translate-y-0.5 hover:bg-white/80 hover:shadow-indigo-500/10 dark:border-indigo-500/30 dark:bg-slate-900/60 dark:hover:bg-slate-900/80">
            <div class="mb-4 flex items-center justify-between">
                <div class="flex h-10 w-10 items-center justify-center rounded-2xl border border-indigo-500/30 bg-indigo-500/10 text-lg font-bold text-indigo-600 shadow-inner dark:text-indigo-400">$</div>
                <span class="rounded-xl border border-indigo-500/30 bg-indigo-500/10 px-2.5 py-1 text-[9px] font-black uppercase tracking-wider text-indigo-700 dark:text-indigo-300">Sistema</span>
            </div>
            <div>
                <span class="text-xl font-black tracking-tight text-slate-900 dark:text-white sm:text-2xl">${{ number_format($totalInventoryValue ?? 0, 2) }}</span>
                <p class="mt-1 text-xs font-bold text-slate-500 dark:text-slate-400">Valor total del inventario</p>
            </div>
        </div>

        <!-- Dinero Total en Ventas del Mes -->
        <div class="group flex flex-col justify-between rounded-3xl border border-emerald-500/20 bg-white/60 p-6 shadow-lg shadow-emerald-500/5 backdrop-blur-md transition-all duration-300 hover:-translate-y-0.5 hover:bg-white/80 hover:shadow-emerald-500/10 dark:border-emerald-500/30 dark:bg-slate-900/60 dark:hover:bg-slate-900/80">
            <div class="mb-4 flex items-center justify-between">
                <div class="flex h-10 w-10 items-center justify-center rounded-2xl border border-emerald-500/30 bg-emerald-500/10 font-bold text-emerald-600 dark:text-emerald-400">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <span class="rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-2.5 py-1 text-[9px] font-black uppercase tracking-wider text-emerald-700 dark:text-emerald-300">Ventas Mes</span>
            </div>
            <div>
                <span class="text-xl font-black tracking-tight text-emerald-600 dark:text-emerald-400 sm:text-2xl">${{ number_format($totalSalesMoney ?? 0, 2) }}</span>
                <p class="mt-1 text-xs font-bold text-slate-800 dark:text-slate-200">Dinero total en ventas</p>
            </div>
        </div>

        <!-- Productos con Stock Bajo -->
        <div class="group flex flex-col justify-between rounded-3xl border border-rose-500/20 bg-white/60 p-6 shadow-lg shadow-rose-500/5 backdrop-blur-md transition-all duration-300 hover:-translate-y-0.5 hover:bg-white/80 hover:shadow-rose-500/10 dark:border-rose-500/30 dark:bg-slate-900/60 dark:hover:bg-slate-900/80">
            <div class="mb-4 flex items-center justify-between">
                <div class="flex h-10 w-10 items-center justify-center rounded-2xl border border-rose-500/30 bg-rose-500/10 font-bold text-rose-600 dark:text-rose-400">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
            </div>
            <div>
                <span class="text-xl font-black tracking-tight text-rose-600 dark:text-rose-400 sm:text-2xl">{{ isset($lowStockProducts) ? $lowStockProducts->count() : 0 }}</span>
                <p class="mt-1 text-xs font-bold text-slate-800 dark:text-slate-200">Productos con stock bajo</p>
            </div>
        </div>

        <!-- Productos Registrados -->
        <div class="group flex flex-col justify-between rounded-3xl border border-slate-300/60 bg-white/60 p-6 shadow-lg shadow-slate-500/5 backdrop-blur-md transition-all duration-300 hover:-translate-y-0.5 hover:bg-white/80 dark:border-slate-800 dark:bg-slate-900/60 dark:hover:bg-slate-900/80">
            <div class="mb-4 flex items-center justify-between">
                <div class="flex h-10 w-10 items-center justify-center rounded-2xl border border-slate-400/20 bg-slate-500/10 font-bold text-slate-600 dark:text-slate-300">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
            </div>
            <div>
                <span class="text-xl font-black tracking-tight text-slate-900 dark:text-white sm:text-2xl">{{ $totalProductsCount ?? 0 }}</span>
                <p class="mt-1 text-xs font-bold text-slate-800 dark:text-slate-200">Productos registrados</p>
                <p class="mt-2 font-mono text-[11px] font-semibold text-slate-400 dark:text-slate-500">{{ $activeSkusCount ?? 0 }} SKUs activos</p>
            </div>
        </div>

        <!-- PRODUCTO MÁS VENDIDO DEL MES -->
        <div class="group flex flex-col justify-between rounded-3xl border border-amber-500/20 bg-white/60 p-6 shadow-lg shadow-amber-500/5 backdrop-blur-md transition-all duration-300 hover:-translate-y-0.5 hover:bg-white/80 hover:shadow-amber-500/10 dark:border-amber-500/30 dark:bg-slate-900/60 dark:hover:bg-slate-900/80 sm:col-span-2 lg:col-span-1">
            <div class="mb-2 flex items-center justify-between">
                <div class="flex h-10 w-10 items-center justify-center rounded-2xl border border-amber-500/30 bg-amber-500/10 text-amber-600 dark:text-amber-400">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.690h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.690l1.519-4.674z"/></svg>
                </div>
                <span class="rounded-xl border border-amber-500/30 bg-amber-500/10 px-2.5 py-1 text-[9px] font-black uppercase tracking-wider text-amber-700 dark:text-amber-300">Top Mes</span>
            </div>
            <div>
                <span class="block truncate text-sm font-black tracking-tight text-slate-900 dark:text-white">
                    {{ $mostSoldProduct->product->name ?? 'Sin ventas en el mes' }}
                </span>
                <p class="mt-0.5 text-xs font-bold text-amber-600 dark:text-amber-400">
                    {{ $mostSoldProduct->total_quantity ?? 0 }} unidades vendidas
                </p>
            </div>
        </div>

    </div>

    <!-- SECCIÓN GRÁFICA: PRODUCTOS MÁS VENDIDOS -->
    <div class="space-y-4 rounded-3xl border border-white/80 bg-white/60 p-6 shadow-lg shadow-slate-500/5 backdrop-blur-md dark:border-slate-800/80 dark:bg-slate-900/60">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-xs font-black uppercase tracking-wider text-slate-900 dark:text-white">Productos Más Vendidos ({{ \Carbon\Carbon::parse($selectedMonth)->locale('es')->isoFormat('MMMM YYYY') }})</h3>
                <p class="text-[11px] font-medium text-slate-400 dark:text-slate-500">Gráfica de salida de inventario correspondiente al periodo seleccionado</p>
            </div>
        </div>
        <div class="relative h-72 w-full">
            <canvas id="topProductsChart" 
                data-labels="{{ json_encode($chartLabels) }}" 
                data-values="{{ json_encode($chartData) }}">
            </canvas>
        </div>
    </div>

    <!-- TABLA DE ACTIVIDAD RECIENTE -->
    <div class="overflow-hidden rounded-3xl border border-white/80 bg-white/60 shadow-lg shadow-slate-500/5 backdrop-blur-md dark:border-slate-800/80 dark:bg-slate-900/60">
        <div class="flex items-center justify-between border-b border-slate-200/60 p-6 dark:border-slate-800/60">
            <h3 class="text-xs font-black uppercase tracking-wider text-slate-900 dark:text-white">Actividad reciente</h3>
            <span class="text-xs font-bold text-slate-400 dark:text-slate-500">
                {{ isset($recentActivities) ? $recentActivities->count() : 0 }} registros
            </span>
        </div>
        
        <!-- VISTA MÓVIL (TARJETAS) -->
        <div class="block divide-y divide-slate-100/80 dark:divide-slate-800/60 sm:hidden">
            @forelse($recentActivities ?? [] as $activity)
                <div class="space-y-2 p-4 transition-colors hover:bg-slate-50/50 dark:hover:bg-slate-800/30">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-900 dark:text-white">
                            {{ $activity->product->name ?? 'N/D' }}
                        </span>
                        @if(($activity->type ?? '') === 'entrada')
                            <span class="inline-flex items-center gap-1 rounded-md border border-emerald-500/30 bg-emerald-500/10 px-2 py-0.5 text-[9px] font-bold text-emerald-700 dark:text-emerald-400">ENTRADA</span>
                        @else
                            <span class="inline-flex items-center gap-1 rounded-md border border-amber-500/30 bg-amber-500/10 px-2 py-0.5 text-[9px] font-bold text-amber-700 dark:text-amber-400">SALIDA</span>
                        @endif
                    </div>
                    <div class="flex items-center justify-between font-mono text-[11px] text-slate-500 dark:text-slate-400">
                        <span>SKU: {{ $activity->product->sku ?? 'N/D' }}</span>
                        <span class="text-xs font-bold {{ ($activity->type ?? '') === 'entrada' ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400' }}">
                            {{ ($activity->type ?? '') === 'entrada' ? '+' : '-' }}{{ $activity->quantity ?? 0 }} pza(s)
                        </span>
                    </div>
                    <div class="flex items-center justify-between border-t border-slate-100/80 pt-1 text-[10px] text-slate-400 dark:border-slate-800/40">
                        <span>{{ $activity->created_at ? $activity->created_at->isoFormat('DD MMM YYYY, HH:mm') : '' }}</span>
                        <span class="font-medium text-slate-600 dark:text-slate-300">Por: {{ $activity->user->name ?? 'Sistema' }}</span>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-xs font-medium text-slate-400 dark:text-slate-500">
                    No hay registros de actividad recientes en la base de datos.
                </div>
            @endforelse
        </div>

        <!-- VISTA DE TABLA TRADICIONAL -->
        <div class="hidden w-full overflow-x-auto sm:block">
            <table class="w-full border-collapse text-left">
                <thead>
                    <tr class="border-b border-slate-200/80 bg-slate-50/50 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:border-slate-800/80 dark:bg-slate-950/40 dark:text-slate-500">
                        <th class="py-4 px-6">Fecha</th>
                        <th class="py-4 px-6">Producto</th>
                        <th class="py-4 px-6">SKU</th>
                        <th class="py-4 px-6">Tipo</th>
                        <th class="py-4 px-6">Cantidad</th>
                        <th class="py-4 px-6 text-right">Usuario</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100/80 text-xs font-medium text-slate-600 dark:divide-slate-800/60 dark:text-slate-300">
                    @forelse($recentActivities ?? [] as $activity)
                        <tr class="transition-colors hover:bg-slate-50/60 dark:hover:bg-slate-800/30">
                            <td class="whitespace-nowrap font-mono text-[11px] text-slate-400 dark:text-slate-500">
                                {{ $activity->created_at ? $activity->created_at->isoFormat('DD MMM YYYY') : '' }}
                            </td>
                            <td class="py-4 px-6 font-bold text-slate-900 dark:text-white">
                                {{ $activity->product->name ?? 'N/D' }}
                            </td>
                            <td class="whitespace-nowrap py-4 px-6 font-mono text-[11px] text-slate-500 dark:text-slate-400">
                                {{ $activity->product->sku ?? 'N/D' }}
                            </td>
                            <td class="whitespace-nowrap py-4 px-6">
                                @if(($activity->type ?? '') === 'entrada')
                                    <span class="inline-flex items-center gap-1 rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-2.5 py-1 text-[10px] font-bold text-emerald-700 dark:text-emerald-400">ENTRADA</span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-xl border border-amber-500/30 bg-amber-500/10 px-2.5 py-1 text-[10px] font-bold text-amber-700 dark:text-amber-400">SALIDA</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap py-4 px-6 font-bold {{ ($activity->type ?? '') === 'entrada' ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400' }}">
                                {{ ($activity->type ?? '') === 'entrada' ? '+' : '-' }}{{ $activity->quantity ?? 0 }}
                            </td>
                            <td class="whitespace-nowrap py-4 px-6 text-right font-bold text-slate-700 dark:text-slate-300">
                                {{ $activity->user->name ?? 'Sistema' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400 dark:text-slate-500">
                                No hay registros de actividad recientes en la base de datos.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
</x-app-container>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="{{ asset('js/components/dashboard-chart.js') }}"></script>
@endpush