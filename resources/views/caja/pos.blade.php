@extends('layouts.app')

@section('title', 'Punto de Venta - SCGI')
@section('header_title', 'Punto de Venta (POS)')

@section('content')
<x-app-container>
    <div x-data="posApp()" x-init="init()" class="flex flex-col lg:grid lg:grid-cols-12 gap-4 lg:gap-6 lg:h-[calc(100vh-8.5rem)] pb-16 lg:pb-0">

        {{-- COLUMNA IZQUIERDA: CATÁLOGO DE PRODUCTOS --}}
        <div class="lg:col-span-7 xl:col-span-8 flex flex-col h-full bg-white dark:bg-[#0e1322] rounded-2xl border border-slate-200 dark:border-slate-800/80 shadow-xs overflow-hidden backdrop-blur-md">
            
            {{-- BUSCADOR Y FILTROS --}}
            <div class="p-3 sm:p-4 border-b border-slate-200 dark:border-slate-800/80 space-y-3 bg-slate-50/50 dark:bg-[#0b0f19]/30">
                <div class="relative">
                    <input type="text"
                           x-model="search"
                           x-ref="searchInput"
                           @keydown.window.slash.prevent="$refs.searchInput.focus()"
                           placeholder="Buscar producto por nombre o código (Presiona '/' para buscar)..."
                           class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#0b0f19] text-xs sm:text-sm text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-colors">
                    <svg class="w-5 h-5 text-slate-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>

                {{-- FILTRO CATEGORÍAS --}}
                <div class="flex items-center gap-2 overflow-x-auto pb-1 no-scrollbar">
                    <button @click="selectedCategory = 'all'"
                            :class="selectedCategory === 'all' ? 'bg-indigo-600 text-white' : 'bg-slate-100 dark:bg-slate-800/60 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-800'"
                            class="px-3.5 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap transition-colors">
                        Todos
                    </button>
                    @foreach($categories as $category)
                        <button @click="selectedCategory = {{ $category->id }}"
                                :class="selectedCategory === {{ $category->id }} ? 'bg-indigo-600 text-white' : 'bg-slate-100 dark:bg-slate-800/60 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-800'"
                                class="px-3.5 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap transition-colors">
                            {{ $category->name }}
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- GRID DE PRODUCTOS --}}
            <div class="flex-1 p-3 sm:p-4 overflow-y-auto grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-3 content-start">
                <template x-for="product in filteredProducts" :key="product.id">
                    <div @click="addToCart(product)"
                         :class="{
                             'opacity-60 cursor-not-allowed bg-slate-100 dark:bg-slate-800/50 border-dashed': product.stock <= 0,
                             'cursor-pointer hover:border-indigo-500 dark:hover:border-indigo-500/50 hover:shadow-md bg-white dark:bg-[#0b0f19]/40': product.stock > 0
                         }"
                         class="group relative flex flex-col justify-between p-3 rounded-xl border border-slate-200 dark:border-slate-800/80 transition-all duration-200 overflow-hidden">
                        
                        {{-- ETIQUETA DE STOCK EN LA ESQUINA SUPERIOR DERECHA --}}
                        <div class="absolute top-2 right-2 z-20 flex items-center gap-1 px-1.5 py-0.5 sm:px-2 sm:py-1 rounded-md text-[9px] sm:text-[10px] font-extrabold shadow-xs"
                             :class="{
                                 'bg-rose-600 text-white animate-pulse': product.stock <= 0 || product.stock <= 5,
                                 'bg-amber-400 text-slate-950': product.stock >= 6 && product.stock <= 9,
                                 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-900': product.stock >= 10
                             }">
                            
                            <svg x-show="product.stock > 0 && product.stock <= 9" class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>

                            <span x-text="product.stock <= 0 ? '¡AGOTADO!' : (product.stock <= 5 ? '¡ÚLTIMAS ' + product.stock + '!' : (product.stock <= 9 ? '¡SE ACABA: ' + product.stock + '!' : 'Stock: ' + product.stock))"></span>
                        </div>

                        <div>
                            {{-- PREVIEW DE IMAGEN --}}
                            <div class="w-full h-24 sm:h-28 mb-2 rounded-lg bg-slate-100 dark:bg-slate-800/50 flex items-center justify-center overflow-hidden relative">
                                <template x-if="product.image_url">
                                    <img :src="product.image_url" 
                                         :alt="product.name" 
                                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200">
                                </template>
                                <template x-if="!product.image_url">
                                    <svg class="w-8 h-8 text-slate-400 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </template>
                            </div>

                            <div class="flex items-start justify-between gap-1 mb-1">
                                <span class="text-[9px] sm:text-[10px] font-bold px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 truncate" x-text="product.category ? product.category.name : 'General'"></span>
                            </div>
                            <h4 class="font-bold text-xs sm:text-sm text-slate-800 dark:text-slate-100 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 line-clamp-2 leading-tight" x-text="product.name"></h4>
                            <p class="text-[10px] sm:text-[11px] text-slate-400 dark:text-slate-500 mt-0.5" x-text="product.sku || 'Sin SKU'"></p>
                        </div>

                        <div class="mt-3 pt-2 border-t border-slate-100 dark:border-slate-800/50 flex items-center justify-between">
                            <span class="font-black text-slate-900 dark:text-white text-sm sm:text-base" x-text="'$' + formatNumber(product.price)"></span>
                            <div class="w-7 h-7 rounded-lg bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center group-hover:bg-indigo-600 group-hover:text-white transition-colors"
                                 :class="{ 'opacity-40 group-hover:bg-indigo-50 group-hover:text-indigo-600': product.stock <= 0 }">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- COLUMNA DERECHA: CARRITO DE COMPRA / COBRO --}}
        <div id="cart-section" class="lg:col-span-5 xl:col-span-4 flex flex-col h-full bg-white dark:bg-[#0e1322] rounded-2xl border border-slate-200 dark:border-slate-800/80 shadow-xs overflow-hidden backdrop-blur-md">
            
            {{-- CABECERA DEL CARRITO --}}
            <div class="p-4 border-b border-slate-200 dark:border-slate-800/80 flex items-center justify-between bg-slate-50/50 dark:bg-[#0b0f19]/30">
                <h3 class="font-extrabold text-slate-800 dark:text-white text-base flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    Carrito de Compra
                </h3>
                <button @click="clearCart()" x-show="cart.length > 0" class="text-xs text-rose-500 hover:text-rose-600 font-bold hover:underline">
                    Vaciar
                </button>
            </div>

            {{-- LISTADO DE PRODUCTOS EN EL CARRITO --}}
            <div class="flex-1 p-4 overflow-y-auto space-y-3 min-h-[180px]">
                <template x-if="cart.length === 0">
                    <div class="h-full flex flex-col items-center justify-center text-slate-400 py-8">
                        <svg class="w-12 h-12 mb-2 stroke-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        <p class="text-sm font-semibold">El carrito está vacío</p>
                        <p class="text-xs text-slate-500">Selecciona productos para agregar</p>
                    </div>
                </template>

                <template x-for="(item, index) in cart" :key="item.id">
                    <div class="p-3 rounded-xl border border-slate-100 dark:border-slate-800/60 bg-slate-50/50 dark:bg-[#0b0f19]/20 flex items-center justify-between gap-2">
                        <div class="flex-1 min-w-0">
                            <h5 class="font-bold text-xs text-slate-800 dark:text-slate-200 truncate" x-text="item.name"></h5>
                            <p class="text-[11px] text-slate-500" x-text="'$' + formatNumber(item.price) + ' c/u'"></p>
                        </div>

                        {{-- CONTROLES DE CANTIDAD --}}
                        <div class="flex items-center gap-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg p-1">
                            <button @click="updateQuantity(index, -1)" class="w-5 h-5 flex items-center justify-center rounded text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 font-extrabold">-</button>
                            <span class="w-6 text-center text-xs font-bold text-slate-800 dark:text-white" x-text="item.quantity"></span>
                            <button @click="updateQuantity(index, 1)" class="w-5 h-5 flex items-center justify-center rounded text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 font-extrabold">+</button>
                        </div>

                        <div class="text-right min-w-[60px]">
                            <p class="font-extrabold text-xs text-slate-900 dark:text-white" x-text="'$' + formatNumber(item.price * item.quantity)"></p>
                        </div>

                        <button @click="removeFromCart(index)" class="text-slate-400 hover:text-rose-500 p-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </template>
            </div>

            {{-- RESUMEN Y MÉTODOS DE PAGO --}}
            <div class="p-4 border-t border-slate-200 dark:border-slate-800/80 bg-slate-50/50 dark:bg-[#0b0f19]/30 space-y-3">
                <div class="space-y-1.5">
                    <div class="flex justify-between text-xs text-slate-500 dark:text-slate-400">
                        <span>Subtotal</span>
                        <span class="font-bold text-slate-800 dark:text-slate-200" x-text="'$' + formatNumber(total)"></span>
                    </div>
                    <div class="flex justify-between text-base font-black text-slate-900 dark:text-white pt-1 border-t border-slate-200 dark:border-slate-800">
                        <span>Total a Pagar</span>
                        <span class="text-indigo-600 dark:text-indigo-400 text-lg" x-text="'$' + formatNumber(total)"></span>
                    </div>
                </div>

                {{-- MÉTODO DE PAGO --}}
                <div class="grid grid-cols-3 gap-2 pt-1">
                    <button @click="paymentMethod = 'efectivo'"
                            :class="paymentMethod === 'efectivo' ? 'border-indigo-600 bg-indigo-50 dark:bg-indigo-950/30 text-indigo-600 dark:text-indigo-400 font-extrabold' : 'border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400'"
                            class="py-2 text-xs rounded-xl border text-center transition-all">Efectivo</button>
                    <button @click="paymentMethod = 'tarjeta'"
                            :class="paymentMethod === 'tarjeta' ? 'border-indigo-600 bg-indigo-50 dark:bg-indigo-950/30 text-indigo-600 dark:text-indigo-400 font-extrabold' : 'border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400'"
                            class="py-2 text-xs rounded-xl border text-center transition-all">Tarjeta</button>
                    <button @click="paymentMethod = 'transferencia'"
                            :class="paymentMethod === 'transferencia' ? 'border-indigo-600 bg-indigo-50 dark:bg-indigo-950/30 text-indigo-600 dark:text-indigo-400 font-extrabold' : 'border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400'"
                            class="py-2 text-xs rounded-xl border text-center transition-all">Transf.</button>
                </div>

                {{-- MONTO RECIBIDO --}}
                <div x-show="paymentMethod === 'efectivo'" class="space-y-1">
                    <label class="text-[11px] font-bold text-slate-500">Efectivo Recibido</label>
                    <input type="number" step="0.01" x-model="receivedAmount" placeholder="0.00" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#0b0f19] text-sm text-slate-800 dark:text-slate-100 font-bold">
                    <div x-show="receivedAmount > 0" class="flex justify-between text-xs pt-1 font-bold" :class="change >= 0 ? 'text-emerald-500' : 'text-rose-500'">
                        <span>Cambio:</span>
                        <span x-text="'$' + formatNumber(change >= 0 ? change : 0)"></span>
                    </div>
                </div>

                {{-- BOTÓN REGISTRAR VENTA --}}
                <button @click="processSale()"
                        :disabled="cart.length === 0 || loading || (paymentMethod === 'efectivo' && receivedAmount < total)"
                        class="w-full py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 disabled:bg-slate-300 dark:disabled:bg-slate-800 text-white font-extrabold text-sm shadow-md shadow-indigo-600/20 transition-all flex items-center justify-center gap-2 cursor-pointer disabled:cursor-not-allowed">
                    <span x-show="!loading">Completar Venta</span>
                    <span x-show="loading" class="inline-block animate-spin rounded-full h-4 w-4 border-2 border-white border-t-transparent"></span>
                </button>
            </div>
        </div>

        {{-- BOTÓN FLOTANTE EN MÓVIL PARA VER EL CARRITO RÁPIDAMENTE --}}
        <div class="lg:hidden fixed bottom-4 right-4 z-40" x-show="cart.length > 0">
            <a href="#cart-section" class="flex items-center gap-2 px-4 py-3 bg-indigo-600 text-white rounded-full shadow-lg font-bold text-xs">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                <span>Ver Carrito (<span x-text="cart.reduce((sum, item) => sum + item.quantity, 0)"></span>)</span>
            </a>
        </div>

    </div>
</x-app-container>
@endsection

@push('scripts')
    <script>
        window.posData = {
            products: @json($products),
            storeUrl: "{{ route('caja.venta') }}",
            csrfToken: "{{ csrf_token() }}"
        };
    </script>
    <script src="{{ asset('js/components/pos.js') }}"></script>
@endpush