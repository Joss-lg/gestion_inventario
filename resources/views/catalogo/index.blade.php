@extends('layouts.app')

@section('title', 'Catálogo de Productos - SCGI')
@section('header_title', 'Catálogo de Productos')

@section('content')

{{-- Contenedor principal con Alpine.js para la búsqueda y modal --}}
<div x-data="{ 
        showModal: false,
        activeProduct: {},
        openDetails(product) {
            this.activeProduct = product;
            this.showModal = true;
        }
    }" 
    class="relative space-y-6">

    <!-- ORBES DE LUZ NEÓN DE FONDO -->
    <div class="pointer-events-none absolute -top-12 left-6 h-72 w-72 rounded-full bg-indigo-500/25 blur-3xl dark:bg-indigo-500/20"></div>
    <div class="pointer-events-none absolute top-36 right-6 h-80 w-80 rounded-full bg-emerald-500/20 blur-3xl dark:bg-emerald-500/15"></div>

    {{-- Buscador y Filtros --}}
    <form method="GET" action="{{ route('catalogo.index') }}" class="relative flex flex-col md:flex-row justify-between items-center gap-4 rounded-3xl border border-white/80 bg-white/60 p-4 shadow-lg shadow-slate-500/5 backdrop-blur-md dark:border-slate-800/80 dark:bg-slate-900/60 transition-colors">
        <div class="relative w-full md:w-96">
            <input type="text" 
                   name="search" 
                   value="{{ request('search') }}"
                   placeholder="Buscar por nombre, SKU o código..." 
                   class="w-full rounded-2xl border border-slate-200/80 bg-white/70 px-4 py-2.5 pl-10 text-xs font-bold text-slate-700 shadow-2xs focus:border-indigo-500/50 focus:outline-hidden focus:ring-2 focus:ring-indigo-500/50 dark:border-slate-700/60 dark:bg-slate-800/70 dark:text-slate-200">
            <svg class="absolute left-3.5 top-3 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            @if(request('category'))
                <input type="hidden" name="category" value="{{ request('category') }}">
            @endif
        </div>
        
        <div class="flex items-center gap-2 w-full md:w-auto justify-end">
            <button type="submit" class="cursor-pointer rounded-xl bg-indigo-600 px-4 py-2.5 text-xs font-bold text-white shadow-md shadow-indigo-600/20 transition hover:bg-indigo-700">
                Buscar
            </button>
            @if(request('search') || (request('category') && request('category') !== 'all'))
                <a href="{{ route('catalogo.index') }}" class="rounded-xl border border-slate-200/80 bg-white/70 px-4 py-2.5 text-xs font-bold text-slate-600 backdrop-blur-xs transition hover:bg-slate-100 dark:border-slate-700/60 dark:bg-slate-800/70 dark:text-slate-300">
                    Limpiar Filtros
                </a>
            @endif
            <span class="text-xs font-bold text-slate-400 dark:text-slate-500 ml-2 uppercase tracking-wider">Total:</span>
            <span class="rounded-xl border border-indigo-500/30 bg-indigo-500/10 px-3 py-1 text-xs font-black text-indigo-700 dark:text-indigo-300">
                {{ $products->total() }}
            </span>
        </div>
    </form>

    {{-- Pills de Categorías --}}
    <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-none">
        <a href="{{ route('catalogo.index', array_merge(request()->except('category', 'page'), ['category' => 'all'])) }}" 
           class="whitespace-nowrap rounded-2xl px-4 py-2 text-xs font-bold transition-all duration-200 {{ !request('category') || request('category') === 'all' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/20' : 'border border-white/80 bg-white/60 text-slate-600 backdrop-blur-md hover:bg-white/80 dark:border-slate-800/80 dark:bg-slate-900/60 dark:text-slate-300 dark:hover:bg-slate-900/85' }}">
            Todos
        </a>
        
        @if(isset($categories) && $categories->isNotEmpty())
            @foreach($categories as $category)
                <a href="{{ route('catalogo.index', array_merge(request()->except('category', 'page'), ['category' => $category->id])) }}" 
                   class="whitespace-nowrap rounded-2xl px-4 py-2 text-xs font-bold transition-all duration-200 {{ request('category') == $category->id ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/20' : 'border border-white/80 bg-white/60 text-slate-600 backdrop-blur-md hover:bg-white/80 dark:border-slate-800/80 dark:bg-slate-900/60 dark:text-slate-300 dark:hover:bg-slate-900/85' }}">
                    {{ $category->name }} ({{ $category->products_count ?? 0 }})
                </a>
            @endforeach
        @endif
    </div>

    {{-- Listado de Productos --}}
    @if($products->isEmpty())
        <div class="rounded-3xl border border-white/80 bg-white/60 p-12 text-center text-slate-500 backdrop-blur-md dark:border-slate-800/80 dark:bg-slate-900/60 dark:text-slate-400">
            <svg class="w-16 h-16 mx-auto mb-3 text-slate-300 dark:text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            <p class="text-sm font-bold">No se encontraron productos en el catálogo.</p>
            <p class="text-xs mt-1 text-slate-400">Intenta cambiando el término de búsqueda o la categoría seleccionada.</p>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($products as $product)
                @php
                    $rawImg = $product->image_path ?? $product->image ?? $product->photo ?? $product->imagen ?? null;
                    
                    $imageUrl = null;
                    if ($rawImg) {
                        if (filter_var($rawImg, FILTER_VALIDATE_URL)) {
                            $imageUrl = $rawImg;
                        } elseif (\Route::has('products.image')) {
                            $imageUrl = route('products.image', ['path' => $rawImg]);
                        } else {
                            $imageUrl = asset('storage/' . ltrim($rawImg, '/'));
                        }
                    }

                    $productData = [
                        'id' => $product->id,
                        'name' => $product->name,
                        'sku' => $product->sku ?? 'Sin SKU',
                        'price' => number_format($product->price ?? 0, 2),
                        'stock' => $product->stock ?? 0,
                        'category' => $product->category ? $product->category->name : 'General',
                        'image' => $imageUrl,
                    ];
                @endphp

                <div class="group flex flex-col justify-between overflow-hidden rounded-3xl border border-white/80 bg-white/60 shadow-lg shadow-slate-500/5 backdrop-blur-md transition-all duration-300 hover:-translate-y-0.5 hover:bg-white/80 hover:shadow-indigo-500/10 dark:border-slate-800/80 dark:bg-slate-900/60 dark:hover:bg-slate-900/80">
                    
                    <div @click="openDetails({{ \Illuminate\Support\Js::from($productData) }})" class="cursor-pointer">
                        <div class="relative flex h-44 items-center justify-center overflow-hidden bg-slate-100/80 dark:bg-slate-950/40">
                            @if($imageUrl)
                                <img src="{{ $imageUrl }}" alt="{{ $product->name }}" class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105">
                            @else
                                <svg class="w-12 h-12 text-slate-300 dark:text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            @endif

                            @if($product->category)
                                {{-- Etiqueta de categoría corregida para modo oscuro y claro --}}
                                <span class="absolute top-3 left-3 rounded-xl border border-indigo-500/40 bg-white/90 px-2.5 py-1 text-[10px] font-black text-indigo-900 shadow-md backdrop-blur-md dark:bg-slate-900/90 dark:text-white">
                                    {{ $product->category->name }}
                                </span>
                            @endif
                        </div>
                        
                        <div class="p-5 space-y-1.5">
                            <p class="font-mono text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 truncate">
                                SKU: {{ $product->sku ?? 'N/A' }}
                            </p>
                            <h3 class="text-sm font-black tracking-tight text-slate-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition truncate" title="{{ $product->name }}">
                                {{ $product->name }}
                            </h3>
                            <div class="flex items-baseline justify-between pt-1">
                                <span class="text-base font-black text-emerald-600 dark:text-emerald-400">
                                    ${{ number_format($product->price ?? 0, 2) }} <span class="text-[10px] font-bold text-slate-400">MXN</span>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between border-t border-slate-200/60 p-5 pt-0 mt-2 dark:border-slate-800/60">
                        @php
                            $stock = $product->stock ?? 0;
                            $minStock = $product->min_stock ?? 5;
                            $isLowStock = $stock <= $minStock;
                        @endphp

                        <div class="flex items-center gap-1.5 rounded-xl border px-2.5 py-1 text-[11px] font-bold {{ $isLowStock ? 'border-rose-500/30 bg-rose-500/10 text-rose-700 dark:text-rose-400' : 'border-emerald-500/30 bg-emerald-500/10 text-emerald-700 dark:text-emerald-400' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $isLowStock ? 'bg-rose-500 animate-pulse' : 'bg-emerald-500' }}"></span>
                            Stock: {{ $stock }} pzas
                        </div>

                        <button type="button" 
                                @click="openDetails({{ \Illuminate\Support\Js::from($productData) }})"
                                class="cursor-pointer rounded-xl border border-slate-200/80 bg-white/70 p-2 text-slate-400 backdrop-blur-xs transition hover:border-indigo-500/40 hover:bg-indigo-500/10 hover:text-indigo-600 dark:border-slate-700/60 dark:bg-slate-800/70 dark:text-slate-300 dark:hover:text-indigo-400" 
                                title="Ver detalles rápidos">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </button>
                    </div>

                </div>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $products->links() }}
        </div>
    @endif

    {{-- Modal de Detalles --}}
    <div x-show="showModal" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         x-cloak
         @keydown.escape.window="showModal = false"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-md">
         
        <div @click.away="showModal = false" 
             class="flex max-h-[90vh] w-full max-w-lg flex-col overflow-hidden rounded-3xl border border-white/80 bg-white/90 shadow-2xl backdrop-blur-xl dark:border-slate-800 dark:bg-slate-900/90">
            
            <div class="flex items-center justify-between border-b border-slate-200/60 px-6 py-4 dark:border-slate-800/60">
                <span class="rounded-xl border border-indigo-500/30 bg-indigo-500/10 px-2.5 py-1 text-[10px] font-black text-indigo-700 dark:text-indigo-300" x-text="activeProduct.category"></span>
                <button type="button" @click="showModal = false" class="cursor-pointer rounded-xl p-1 text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 dark:hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="space-y-4 overflow-y-auto p-6">
                <div class="relative flex h-56 items-center justify-center overflow-hidden rounded-2xl border border-slate-200/80 bg-slate-100/80 dark:border-slate-800 dark:bg-slate-950/40">
                    <template x-if="activeProduct.image">
                        <img :src="activeProduct.image" :alt="activeProduct.name" class="h-full w-full object-cover">
                    </template>
                    <template x-if="!activeProduct.image">
                        <svg class="w-16 h-16 text-slate-300 dark:text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </template>
                </div>

                <div>
                    <p class="font-mono text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500" x-text="'SKU: ' + activeProduct.sku"></p>
                    <h2 class="mt-1 text-lg font-black tracking-tight text-slate-900 dark:text-white" x-text="activeProduct.name"></h2>
                </div>

                <div class="grid grid-cols-2 gap-4 border-t border-slate-200/60 pt-4 dark:border-slate-800/60">
                    <div class="rounded-2xl border border-slate-200/80 bg-white/60 p-3.5 backdrop-blur-xs dark:border-slate-800 dark:bg-slate-900/60">
                        <span class="text-[11px] font-bold text-slate-400 block">Precio de Venta</span>
                        <span class="text-lg font-black text-emerald-600 dark:text-emerald-400" x-text="'$' + activeProduct.price + ' MXN'"></span>
                    </div>
                    <div class="rounded-2xl border border-slate-200/80 bg-white/60 p-3.5 backdrop-blur-xs dark:border-slate-800 dark:bg-slate-900/60">
                        <span class="text-[11px] font-bold text-slate-400 block">Stock Disponible</span>
                        <span class="text-lg font-black text-indigo-600 dark:text-indigo-400" x-text="activeProduct.stock + ' pzas'"></span>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-slate-200/60 bg-white/40 p-4 backdrop-blur-md dark:border-slate-800/60 dark:bg-slate-900/40">
                <button type="button" @click="showModal = false" class="cursor-pointer rounded-xl border border-slate-200/80 bg-white/70 px-4 py-2 text-xs font-bold text-slate-700 backdrop-blur-xs transition hover:bg-slate-100 dark:border-slate-700/60 dark:bg-slate-800/70 dark:text-slate-300">
                    Cerrar
                </button>
            </div>

        </div>
    </div>

</div>
@endsection