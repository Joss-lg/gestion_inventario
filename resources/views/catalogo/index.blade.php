@extends('layouts.app')

@section('title', 'Catálogo de Productos - SCGI')
@section('header_title', 'Catálogo de Productos')

@section('content')

<div x-data="{ 
        search: '',
        selectedCategory: 'all',
        showModal: false,
        activeProduct: {},
        openDetails(product) {
            this.activeProduct = product;
            this.showModal = true;
        }
    }" 
    class="relative space-y-6">

    <!-- ORBES DE LUZ NEÓN DE FONDO -->
    <div class="pointer-events-none absolute -top-12 left-6 h-72 w-72 rounded-full bg-[#FF6B4A]/25 blur-3xl dark:bg-[#FF6B4A]/20"></div>
    <div class="pointer-events-none absolute top-36 right-6 h-80 w-80 rounded-full bg-emerald-500/20 blur-3xl dark:bg-emerald-500/15"></div>

    {{-- Buscador y Píldoras de Categorías --}}
    <div class="relative flex flex-col gap-4 rounded-3xl border border-white/80 bg-white/60 p-5 shadow-lg shadow-slate-500/5 backdrop-blur-md dark:border-slate-800/80 dark:bg-slate-900/60 transition-colors">
        
        <div class="relative w-full lg:w-96">
            <input type="text" 
                   x-model.debounce.250ms="search"
                   placeholder="Buscar producto..." 
                   class="w-full rounded-2xl border border-slate-200/80 bg-white/70 px-4 py-2.5 pl-10 text-xs font-bold text-slate-700 shadow-2xs focus:border-[#FF6B4A]/50 focus:outline-hidden focus:ring-2 focus:ring-[#FF6B4A]/50 dark:border-slate-700/60 dark:bg-slate-800/70 dark:text-slate-200">
            <svg class="absolute left-3.5 top-3 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        </div>

        <div class="flex flex-wrap items-center gap-2.5 w-full pt-1 border-t border-slate-200/60 dark:border-slate-800/60">
            <button type="button" 
                    @click="selectedCategory = 'all'"
                    :class="selectedCategory === 'all' ? 'bg-[#F0552F] text-white shadow-md shadow-[#F0552F]/20' : 'border border-slate-200/80 bg-white/70 text-slate-600 backdrop-blur-xs hover:bg-white dark:border-slate-700/60 dark:bg-slate-800/70 dark:text-slate-300 dark:hover:bg-slate-800/90'"
                    class="whitespace-nowrap rounded-xl px-4 py-2 text-xs font-bold transition-all duration-200 cursor-pointer">
                Todos
            </button>
            
            @if(isset($categories) && $categories->isNotEmpty())
                @foreach($categories as $category)
                    <button type="button" 
                            @click="selectedCategory = '{{ $category->id }}'"
                            :class="selectedCategory == '{{ $category->id }}' ? 'bg-[#F0552F] text-white shadow-md shadow-[#F0552F]/20' : 'border border-slate-200/80 bg-white/70 text-slate-600 backdrop-blur-xs hover:bg-white dark:border-slate-700/60 dark:bg-slate-800/70 dark:text-slate-300 dark:hover:bg-slate-800/90'"
                            class="whitespace-nowrap rounded-xl px-4 py-2 text-xs font-bold transition-all duration-200 cursor-pointer">
                        {{ $category->name }} ({{ $category->products_count ?? 0 }})
                    </button>
                @endforeach
            @endif

            <template x-if="search !== '' || selectedCategory !== 'all'">
                <button type="button" @click="search = ''; selectedCategory = 'all'" class="cursor-pointer rounded-xl border border-rose-500/30 bg-rose-500/10 px-3 py-2 text-xs font-bold text-rose-600 backdrop-blur-xs transition hover:bg-rose-500/20 dark:text-rose-400">
                    Limpiar
                </button>
            </template>
        </div>

    </div>

    {{-- Listado de Productos --}}
    @if($products->isEmpty())
        <div class="rounded-3xl border border-white/80 bg-white/60 p-12 text-center text-slate-500 backdrop-blur-md dark:border-slate-800/80 dark:bg-slate-900/60 dark:text-slate-400">
            <svg class="w-16 h-16 mx-auto mb-3 text-slate-300 dark:text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            <p class="text-sm font-bold">No se encontraron productos en el catálogo.</p>
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
                        'name' => $product->name,
                        'sku' => $product->sku ?? 'Sin SKU',
                        'price' => number_format($product->price ?? 0, 2),
                        'stock' => $product->stock ?? 0,
                        'category' => $product->category ? $product->category->name : 'General',
                        'image' => $imageUrl,
                    ];

                    $catId = $product->category_id ?? ($product->category->id ?? '');
                    $pName = strtolower($product->name);
                    $pSku = strtolower($product->sku ?? '');
                @endphp

                <div x-show="(selectedCategory === 'all' || String(selectedCategory) === String('{{ $catId }}')) && 
                             (search === '' || 
                              '{{ $pName }}'.includes(search.toLowerCase()) || 
                              '{{ $pSku }}'.includes(search.toLowerCase()))"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     class="group flex flex-col justify-between overflow-hidden rounded-3xl border border-white/80 bg-white/60 shadow-lg shadow-slate-500/5 backdrop-blur-md transition-all duration-300 hover:-translate-y-1 hover:border-[#FF6B4A]/30 hover:bg-white/80 hover:shadow-xl hover:shadow-[#FF6B4A]/10 dark:border-slate-800/80 dark:bg-slate-900/60 dark:hover:border-[#FF6B4A]/20 dark:hover:bg-slate-900/80">
                    
                    <div @click="openDetails({{ \Illuminate\Support\Js::from($productData) }})" class="cursor-pointer">
                        <div class="relative flex h-44 items-center justify-center overflow-hidden border-b border-slate-100 bg-gradient-to-br from-slate-50 to-slate-100 dark:border-slate-800/60 dark:from-slate-900 dark:to-slate-950">
                            @if($imageUrl)
                                <img src="{{ $imageUrl }}" alt="{{ $product->name }}" class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105">
                            @else
                                <svg class="w-12 h-12 text-slate-300 transition-transform duration-300 group-hover:scale-110 dark:text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            @endif

                            @if($product->category)
                                <span class="absolute top-3 left-3 flex items-center gap-1.5 rounded-xl border border-[#FF6B4A]/40 bg-white/90 px-2.5 py-1 text-[10px] font-black text-[#5C1B0E] shadow-md backdrop-blur-md dark:bg-slate-900/90 dark:text-white">
                                    <span class="h-1.5 w-1.5 rounded-full bg-[#FF6B4A] shadow-[0_0_6px_rgba(255,107,74,0.8)]"></span>
                                    {{ $product->category->name }}
                                </span>
                            @endif
                        </div>
                        
                        <div class="p-5 pb-3 space-y-1.5">
                            <p class="font-mono text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 truncate">
                                SKU: {{ $product->sku ?? 'N/A' }}
                            </p>
                            <h3 class="text-sm font-black tracking-tight text-slate-900 dark:text-white group-hover:text-[#F0552F] dark:group-hover:text-[#FF8A65] transition truncate" title="{{ $product->name }}">
                                {{ $product->name }}
                            </h3>
                            <div class="flex items-baseline justify-between pt-1.5">
                                <span class="inline-flex items-baseline gap-1 rounded-lg bg-emerald-50 px-2 py-1 dark:bg-emerald-500/10">
                                    <span class="text-base font-black text-emerald-600 dark:text-emerald-400">${{ number_format($product->price ?? 0, 2) }}</span>
                                    <span class="text-[10px] font-bold text-emerald-600/70 dark:text-emerald-400/70">MXN</span>
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- FOOTER --}}
                    <div class="flex items-center justify-between px-5 pb-5 pt-1">
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
                                class="cursor-pointer rounded-full bg-[#FF6B4A]/10 p-2 text-[#F0552F] transition-all duration-200 hover:bg-[#F0552F] hover:text-white hover:shadow-md hover:shadow-[#F0552F]/30 dark:bg-[#FF6B4A]/15 dark:text-[#FF8A65] dark:hover:bg-[#F0552F] dark:hover:text-white" 
                                title="Ver detalles rápidos">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </button>
                    </div>

                </div>
            @endforeach
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
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95 translate-y-2"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             class="relative flex max-h-[90vh] w-full max-w-lg flex-col overflow-hidden rounded-3xl border border-white/80 bg-white/90 shadow-2xl shadow-[#5C1B0E]/10 backdrop-blur-xl dark:border-slate-800 dark:bg-slate-900/90 dark:shadow-black/40">

            <!-- Resplandor neón de fondo (mismo recurso que el header de la página) -->
            <div class="pointer-events-none absolute -top-16 -left-10 h-56 w-56 rounded-full bg-[#FF6B4A]/20 blur-3xl dark:bg-[#FF6B4A]/15"></div>

            <div class="relative flex items-center justify-between border-b border-slate-200/60 px-6 py-4 dark:border-slate-800/60">
                <span class="flex items-center gap-1.5 rounded-xl border border-[#FF6B4A]/40 bg-[#FF6B4A]/10 px-2.5 py-1 text-[10px] font-black uppercase tracking-wide text-[#D9431F] dark:text-[#FFB399]">
                    <span class="h-1.5 w-1.5 rounded-full bg-[#FF6B4A] shadow-[0_0_6px_rgba(255,107,74,0.8)]"></span>
                    <span x-text="activeProduct.category"></span>
                </span>
                <button type="button" 
                        @click="showModal = false" 
                        aria-label="Cerrar"
                        class="flex h-9 w-9 shrink-0 cursor-pointer items-center justify-center rounded-full bg-[#F0552F] text-white shadow-md shadow-[#F0552F]/40 transition-all duration-200 hover:bg-[#D9431F] hover:scale-105 hover:rotate-90 dark:shadow-[#F0552F]/30">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="relative space-y-4 overflow-y-auto p-6">
                <div class="relative flex h-56 items-center justify-center overflow-hidden rounded-2xl border border-slate-200/80 bg-gradient-to-br from-slate-50 to-slate-100 dark:border-slate-800 dark:from-slate-900 dark:to-slate-950">
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
                    <div class="rounded-2xl border border-slate-200/80 bg-white/60 p-3.5 backdrop-blur-xs transition-colors hover:border-emerald-500/30 dark:border-slate-800 dark:bg-slate-900/60">
                        <span class="text-[11px] font-bold text-slate-400 block">Precio de Venta</span>
                        <span class="text-lg font-black text-emerald-600 dark:text-emerald-400" x-text="'$' + activeProduct.price + ' MXN'"></span>
                    </div>
                    <div class="rounded-2xl border border-slate-200/80 bg-white/60 p-3.5 backdrop-blur-xs transition-colors hover:border-[#FF6B4A]/30 dark:border-slate-800 dark:bg-slate-900/60">
                        <span class="text-[11px] font-bold text-slate-400 block">Stock Disponible</span>
                        <span class="text-lg font-black text-[#F0552F] dark:text-[#FF8A65]" x-text="activeProduct.stock + ' pzas'"></span>
                    </div>
                </div>
            </div>

            <div class="relative flex items-center justify-end gap-3 border-t border-slate-200/60 bg-white/40 p-4 backdrop-blur-md dark:border-slate-800/60 dark:bg-slate-900/40">
                <button type="button" @click="showModal = false" class="inline-flex cursor-pointer items-center gap-1.5 rounded-full bg-[#F0552F] px-6 py-2.5 text-xs font-bold tracking-wide text-white shadow-lg shadow-slate-500/20 transition-all duration-200 hover:bg-[#D9431F] hover:-translate-y-0.5 active:translate-y-0">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                    Cerrar
                </button>
            </div>

        </div>
    </div>

</div>
@endsection