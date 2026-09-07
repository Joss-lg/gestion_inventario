@extends('layouts.app')

@section('title', 'Punto de Venta - SCGI')
@section('header_title', 'Punto de Venta (POS)')

@section('content')
<x-app-container>
    {{-- CONTENEDOR PRINCIPAL --}}
    <div x-data="posApp()" x-init="init()" class="flex flex-col lg:grid lg:grid-cols-12 lg:items-start gap-4 lg:gap-6 w-full pb-16 lg:pb-0 select-none">

        {{-- COLUMNA IZQUIERDA: CATÁLOGO DE PRODUCTOS --}}
        <div class="lg:col-span-7 xl:col-span-8 flex flex-col h-[65vh] lg:h-[calc(100vh-8.5rem)] bg-white dark:bg-[#0e1322] rounded-2xl border border-slate-200 dark:border-slate-800/80 shadow-xs overflow-hidden">

            {{-- Buscador y Categorías --}}
            <div class="p-3 sm:p-4 border-b border-slate-200 dark:border-slate-800/80 space-y-3 bg-slate-50/50 dark:bg-[#0b0f19]/30 shrink-0">
                <div class="relative">
                    <input type="text"
                           x-model="search"
                           x-ref="searchInput"
                           @keydown.window.slash.prevent="$refs.searchInput.focus()"
                           placeholder="Buscar producto por nombre o código..."
                           class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#0b0f19] text-xs sm:text-sm text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#FF6B4A]/20 focus:border-[#F0552F] transition-colors">
                    <svg class="w-5 h-5 text-slate-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button @click="selectedCategory = 'all'"
                            :class="selectedCategory === 'all' ? 'bg-[#F0552F] text-white' : 'bg-slate-100 dark:bg-slate-800/60 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-800'"
                            class="px-3.5 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap transition-colors cursor-pointer">
                        Todos
                    </button>
                    @foreach($categories as $category)
                        <button @click="selectedCategory = {{ $category->id }}"
                                :class="selectedCategory === {{ $category->id }} ? 'bg-[#F0552F] text-white' : 'bg-slate-100 dark:bg-slate-800/60 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-800'"
                                class="px-3.5 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap transition-colors cursor-pointer">
                            {{ $category->name }}
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Grid de Productos --}}
            <div class="flex-1 overflow-y-auto custom-scroll p-4">
                <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-4 items-start">
                    <template x-for="product in filteredProducts" :key="product.id">
                        <div @click="addToCart(product)"
                             :class="{
                                 'opacity-60 cursor-not-allowed bg-slate-100 dark:bg-slate-800/50 border-dashed': product.stock <= 0,
                                 'cursor-pointer hover:border-[#FF6B4A] dark:hover:border-[#FF6B4A]/50 hover:shadow-md bg-white dark:bg-[#0b0f19]/40': product.stock > 0
                             }"
                             class="group relative flex flex-col justify-between p-3 rounded-2xl border border-slate-200 dark:border-slate-800/80 shadow-sm hover:-translate-y-0.5 transition-colors duration-200 overflow-hidden">

                            <div class="absolute top-2 right-2 z-20 flex items-center gap-1 px-1.5 py-0.5 sm:px-2 sm:py-1 rounded-md text-[9px] sm:text-[10px] font-extrabold shadow-xs"
                                 :class="{
                                     'bg-rose-600 text-white animate-pulse': product.stock <= 0 || product.stock <= 5,
                                     'bg-amber-400 text-slate-950': product.stock >= 6 && product.stock <= 9,
                                     'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-900': product.stock >= 10
                                 }">
                                <span x-text="product.stock <= 0 ? '¡AGOTADO!' : (product.stock <= 5 ? '¡ÚLTIMAS ' + product.stock + '!' : (product.stock <= 9 ? '¡SE ACABA: ' + product.stock + '!' : 'Stock: ' + product.stock))"></span>
                            </div>

                            <div>
                                <div class="w-full h-24 sm:h-28 mb-2 rounded-lg bg-slate-100 dark:bg-slate-800/50 flex items-center justify-center overflow-hidden relative">
                                    <template x-if="product.image_url">
                                        <img :src="product.image_url" :alt="product.name" class="w-full h-full object-cover">
                                    </template>
                                    <template x-if="!product.image_url">
                                        <svg class="w-8 h-8 text-slate-400 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    </template>
                                </div>

                                <span class="text-[9px] sm:text-[10px] font-bold px-2 py-0.5 rounded-md bg-[#FFF1EC] dark:bg-[#3A120A]/40 text-[#F0552F] dark:text-[#FF8A65] truncate block w-max" x-text="product.category ? product.category.name : 'General'"></span>
                                <h4 class="font-bold text-xs sm:text-sm text-slate-800 dark:text-slate-100 group-hover:text-[#F0552F] dark:group-hover:text-[#FF8A65] line-clamp-2 leading-tight mt-1" x-text="product.name"></h4>
                            </div>

                            <div class="mt-3 pt-2 border-t border-slate-100 dark:border-slate-800/50 flex items-center justify-between">
                                <span class="font-black text-slate-900 dark:text-white text-sm sm:text-base" x-text="'$' + formatNumber(product.price)"></span>
                                <div class="w-7 h-7 rounded-lg bg-[#FFF1EC] dark:bg-[#3A120A]/40 text-[#F0552F] dark:text-[#FF8A65] flex items-center justify-center group-hover:bg-[#F0552F] group-hover:text-white transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        {{-- COLUMNA DERECHA: CARRITO Y COBRO --}}
        <div id="cart-section" class="lg:col-span-5 xl:col-span-4 flex flex-col h-[65vh] lg:h-[calc(100vh-8.5rem)] lg:sticky lg:top-4 bg-white dark:bg-[#0e1322] rounded-2xl border border-slate-200 dark:border-slate-800/80 shadow-xs overflow-hidden">

            {{-- Header Carrito --}}
            <div class="p-4 border-b border-slate-200 dark:border-slate-800/80 flex items-center justify-between bg-slate-50/50 dark:bg-[#0b0f19]/30 shrink-0">
                <h3 class="font-extrabold text-slate-800 dark:text-white text-base flex items-center gap-2">
                    <svg class="w-5 h-5 text-[#FF6B4A]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    Carrito de Compra
                </h3>
                <button @click="clearCart()" x-show="cart.length > 0" class="text-xs text-rose-500 hover:text-rose-600 font-bold hover:underline cursor-pointer">
                    Vaciar
                </button>
            </div>

            {{-- Items Carrito --}}
            <div class="flex-1 overflow-y-auto custom-scroll p-4 space-y-3">
                <template x-if="cart.length === 0">
                    <div class="h-full flex flex-col items-center justify-center text-slate-400 py-8">
                        <svg class="w-12 h-12 mb-2 stroke-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        <p class="text-sm font-semibold">El carrito está vacío</p>
                    </div>
                </template>

                <template x-for="(item, index) in cart" :key="item.id">
                    <div class="p-3 rounded-xl border border-slate-100 dark:border-slate-800/60 bg-slate-50/50 dark:bg-[#0b0f19]/20 flex items-center justify-between gap-2 shrink-0">
                        <div class="flex-1 min-w-0">
                            <h5 class="font-bold text-xs text-slate-800 dark:text-slate-200 truncate" x-text="item.name"></h5>
                            <p class="text-[11px] text-slate-500" x-text="'$' + formatNumber(item.price) + ' c/u'"></p>
                        </div>

                        <div class="flex items-center gap-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg p-1 shrink-0">
                            <button @click="updateQuantity(index, -1)" class="w-5 h-5 flex items-center justify-center rounded text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 font-extrabold cursor-pointer">-</button>
                            <span class="w-6 text-center text-xs font-bold text-slate-800 dark:text-white" x-text="item.quantity"></span>
                            <button @click="updateQuantity(index, 1)" class="w-5 h-5 flex items-center justify-center rounded text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 font-extrabold cursor-pointer">+</button>
                        </div>

                        <div class="text-right min-w-[60px] shrink-0">
                            <p class="font-extrabold text-xs text-slate-900 dark:text-white" x-text="'$' + formatNumber(item.price * item.quantity)"></p>
                        </div>

                        <button @click="removeFromCart(index)" class="text-slate-400 hover:text-rose-500 p-1 shrink-0 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </template>
            </div>

            {{-- Footer Cobro --}}
            <div class="shrink-0 p-4 border-t border-slate-200 dark:border-slate-800/80 bg-slate-50/50 dark:bg-[#0b0f19]/30 space-y-3">
                <div class="flex justify-between text-base font-black text-slate-900 dark:text-white">
                    <span>Total a Pagar</span>
                    <span class="text-[#F0552F] dark:text-[#FF8A65] text-xl" x-text="'$' + formatNumber(total)"></span>
                </div>

                {{-- Fila con 4 Botones de Pago --}}
                <div class="grid grid-cols-4 gap-1.5">
                    <button type="button" 
                            @click="selectDirectPayment('efectivo')" 
                            :class="paymentMethod === 'efectivo' && !isMultiPayMode 
                                ? 'border-[#F0552F] bg-[#FFF1EC] dark:bg-[#3A120A]/40 text-[#F0552F] dark:text-[#FF8A65] font-extrabold ring-1 ring-[#F0552F]' 
                                : 'border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:border-slate-300 dark:hover:border-slate-700'" 
                            class="py-2 px-1 text-[11px] rounded-xl border text-center transition-all cursor-pointer truncate">
                        Efectivo
                    </button>
                    
                    <button type="button" 
                            @click="selectDirectPayment('tarjeta')" 
                            :class="paymentMethod === 'tarjeta' && !isMultiPayMode 
                                ? 'border-[#F0552F] bg-[#FFF1EC] dark:bg-[#3A120A]/40 text-[#F0552F] dark:text-[#FF8A65] font-extrabold ring-1 ring-[#F0552F]' 
                                : 'border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:border-slate-300 dark:hover:border-slate-700'" 
                            class="py-2 px-1 text-[11px] rounded-xl border text-center transition-all cursor-pointer truncate">
                        Tarjeta
                    </button>
                    
                    <button type="button" 
                            @click="selectDirectPayment('transferencia')" 
                            :class="paymentMethod === 'transferencia' && !isMultiPayMode 
                                ? 'border-[#F0552F] bg-[#FFF1EC] dark:bg-[#3A120A]/40 text-[#F0552F] dark:text-[#FF8A65] font-extrabold ring-1 ring-[#F0552F]' 
                                : 'border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:border-slate-300 dark:hover:border-slate-700'" 
                            class="py-2 px-1 text-[11px] rounded-xl border text-center transition-all cursor-pointer truncate">
                        Transf.
                    </button>

                    <button type="button" 
                            @click="abrirModalCobro()" 
                            :class="isMultiPayMode 
                                ? 'border-[#F0552F] bg-[#FFF1EC] dark:bg-[#3A120A]/40 text-[#F0552F] dark:text-[#FF8A65] font-extrabold ring-1 ring-[#F0552F]' 
                                : 'border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:border-slate-300 dark:hover:border-slate-700'" 
                            class="py-2 px-1 text-[11px] rounded-xl border text-center transition-all cursor-pointer truncate">
                        Multi Pago
                    </button>
                </div>

                {{-- Inputs para Pago Directo --}}
                <div x-show="!isMultiPayMode && paymentMethod === 'efectivo'" class="space-y-1">
                    <label class="text-[11px] font-bold text-slate-500 dark:text-slate-400">Efectivo Recibido</label>
                    <input type="number" step="0.01" min="0" x-model.number="receivedAmount" @wheel.prevent placeholder="0.00" 
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#0b0f19] text-sm text-slate-800 dark:text-slate-100 font-bold focus:outline-none focus:ring-2 focus:ring-[#FF6B4A]/20 focus:border-[#F0552F] transition-colors">
                    <div x-show="receivedAmount > 0" class="flex justify-between text-xs pt-1 font-bold" :class="singleChange >= 0 ? 'text-emerald-500' : 'text-rose-500'">
                        <span>Cambio:</span>
                        <span x-text="'$' + formatNumber(singleChange >= 0 ? singleChange : 0)"></span>
                    </div>
                </div>

                <div x-show="!isMultiPayMode && (paymentMethod === 'tarjeta' || paymentMethod === 'transferencia')" class="space-y-1" x-cloak>
                    <label class="text-[11px] font-bold text-slate-500 dark:text-slate-400" x-text="paymentMethod === 'tarjeta' ? 'Nº Voucher / Autorización' : 'Nº Referencia / Rastreo'"></label>
                    <input type="text" x-model="referenceNumber" placeholder="Ej: 12345678" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#0b0f19] text-sm text-slate-800 dark:text-slate-100 font-bold focus:outline-none focus:ring-2 focus:ring-[#FF6B4A]/20 focus:border-[#F0552F] transition-colors">
                </div>

                {{-- Botón Completar Venta --}}
                <button @click="isMultiPayMode ? abrirModalCobro() : processSale(false)"
                        :disabled="cart.length === 0 || loading || (!isMultiPayMode && paymentMethod === 'efectivo' && receivedAmount < total) || (!isMultiPayMode && (paymentMethod === 'tarjeta' || paymentMethod === 'transferencia') && (!referenceNumber || referenceNumber.trim() === ''))"
                        class="w-full py-3.5 rounded-xl bg-[#F0552F] hover:bg-[#D9431F] disabled:bg-slate-100 dark:disabled:bg-slate-900 disabled:text-slate-400 dark:disabled:text-slate-600 disabled:border disabled:border-slate-300 dark:disabled:border-slate-700 text-white font-extrabold text-sm shadow-md shadow-[#F0552F]/20 transition-all flex items-center justify-center gap-2 cursor-pointer disabled:cursor-not-allowed">
                    <span x-show="!loading" x-text="isMultiPayMode ? 'Abrir Desglose Multi Pago' : 'Completar Venta'"></span>
                    <span x-show="loading" class="inline-block animate-spin rounded-full h-4 w-4 border-2 border-white border-t-transparent" x-cloak></span>
                </button>
            </div>
        </div>

        {{-- MODAL MULTI PAGO FIJO --}}
<div x-show="showPayModal" x-cloak
     role="dialog" aria-modal="true" aria-labelledby="modal-multi-pago-title"
     class="fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-md flex items-center justify-center p-3 sm:p-5"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 scale-95"
     x-transition:enter-end="opacity-100 scale-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100 scale-100"
     x-transition:leave-end="opacity-0 scale-95">

    <div @click.away="showPayModal = false"
         class="relative w-full max-w-lg max-h-[90vh] bg-white dark:bg-[#090d18] text-slate-900 dark:text-slate-100 rounded-[28px] border border-slate-200/80 dark:border-[#FF6B4A]/20 shadow-2xl flex flex-col overflow-hidden">

        {{-- Header Modal --}}
        <div class="relative z-10 flex items-center justify-between p-4 border-b border-slate-100 dark:border-[#FF6B4A]/10 shrink-0">
            <div class="flex items-center gap-3">
                <span class="w-2.5 h-2.5 rounded-full bg-[#FF6B4A] shadow-[0_0_10px_rgba(255,107,74,0.8)]"></span>
                <h3 id="modal-multi-pago-title" class="text-xs sm:text-sm font-black uppercase tracking-wider text-slate-900 dark:text-white">Desglose Multi Pago</h3>
            </div>
            <button type="button" @click="showPayModal = false"
                    class="w-8 h-8 rounded-full bg-[#FFF1EC] dark:bg-[#3A120A]/40 hover:bg-[#FFE1D6] dark:hover:bg-[#5C1B0E]/60 text-[#FF6B4A] dark:text-[#FF8A65] font-bold transition flex items-center justify-center cursor-pointer focus:outline-none">
                ✕
            </button>
        </div>

        {{-- Body Modal --}}
        <div class="p-4 sm:p-6 overflow-y-auto custom-scroll space-y-4">
            <div class="grid grid-cols-3 gap-2 p-3.5 bg-slate-50 dark:bg-[#0b0f19]/60 rounded-2xl border border-slate-100 dark:border-slate-800/60 text-center">
                <div>
                    <span class="text-[10px] font-extrabold uppercase text-slate-400 block">Total Venta</span>
                    <p class="text-sm sm:text-base font-black text-slate-900 dark:text-white mt-0.5" x-text="'$' + formatNumber(total)"></p>
                </div>
                <div>
                    <span class="text-[10px] font-extrabold uppercase text-slate-400 block">Monto Cubierto</span>
                    <p class="text-sm sm:text-base font-black text-emerald-500 mt-0.5" x-text="'$' + formatNumber(totalPagado)"></p>
                </div>
                <div>
                    <span class="text-[10px] font-extrabold uppercase text-slate-400 block" x-text="faltante > 0 ? 'Faltante' : 'Cambio'"></span>
                    <p class="text-sm sm:text-base font-black mt-0.5"
                       :class="faltante > 0 ? 'text-rose-500' : 'text-amber-500'" 
                       x-text="'$' + formatNumber(faltante > 0 ? faltante : cambio)"></p>
                </div>
            </div>

            <div class="space-y-2.5">
                <label class="text-xs font-bold text-slate-500 dark:text-slate-400">Métodos de Pago Aplicados</label>
                
                {{-- Efectivo --}}
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 bg-slate-50/50 dark:bg-[#0b0f19]/30 p-2.5 rounded-xl border border-slate-200/80 dark:border-slate-800/80">
                    <div class="sm:w-1/3 text-xs font-bold px-3 py-2 rounded-lg bg-white dark:bg-[#0b0f19] text-slate-800 dark:text-slate-100 flex items-center">
                        Efectivo
                    </div>
                    <div class="relative sm:w-1/3">
                        <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-xs font-extrabold text-slate-400">$</span>
                        <input type="number" step="0.01" min="0" x-model.number="pagoEfectivo" 
                               class="w-full pl-6 pr-2 py-2 text-xs font-extrabold rounded-lg border-slate-200 dark:border-slate-800 bg-white dark:bg-[#0b0f19] text-slate-800 dark:text-slate-100 focus:ring-[#FF6B4A]/20 focus:border-[#F0552F]" placeholder="0.00">
                    </div>
                    <div class="sm:w-1/3 text-center text-xs text-slate-400 font-medium">N/A</div>
                </div>

                {{-- Tarjeta --}}
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 bg-slate-50/50 dark:bg-[#0b0f19]/30 p-2.5 rounded-xl border border-slate-200/80 dark:border-slate-800/80">
                    <div class="sm:w-1/3 text-xs font-bold px-3 py-2 rounded-lg bg-white dark:bg-[#0b0f19] text-slate-800 dark:text-slate-100 flex items-center">
                        Tarjeta
                    </div>
                    <div class="relative sm:w-1/3">
                        <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-xs font-extrabold text-slate-400">$</span>
                        <input type="number" step="0.01" min="0" x-model.number="pagoTarjeta" 
                               class="w-full pl-6 pr-2 py-2 text-xs font-extrabold rounded-lg border-slate-200 dark:border-slate-800 bg-white dark:bg-[#0b0f19] text-slate-800 dark:text-slate-100 focus:ring-[#FF6B4A]/20 focus:border-[#F0552F]" placeholder="0.00">
                    </div>
                    <div class="sm:w-1/3">
                        <input type="text" x-model="refTarjeta" placeholder="Ref / Voucher" 
                               class="w-full text-xs font-medium rounded-lg border-slate-200 dark:border-slate-800 bg-white dark:bg-[#0b0f19] text-slate-800 dark:text-slate-100 focus:ring-[#FF6B4A]/20 focus:border-[#F0552F]">
                    </div>
                </div>

                {{-- Transferencia --}}
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 bg-slate-50/50 dark:bg-[#0b0f19]/30 p-2.5 rounded-xl border border-slate-200/80 dark:border-slate-800/80">
                    <div class="sm:w-1/3 text-xs font-bold px-3 py-2 rounded-lg bg-white dark:bg-[#0b0f19] text-slate-800 dark:text-slate-100 flex items-center">
                        Transferencia
                    </div>
                    <div class="relative sm:w-1/3">
                        <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-xs font-extrabold text-slate-400">$</span>
                        <input type="number" step="0.01" min="0" x-model.number="pagoTransferencia" 
                               class="w-full pl-6 pr-2 py-2 text-xs font-extrabold rounded-lg border-slate-200 dark:border-slate-800 bg-white dark:bg-[#0b0f19] text-slate-800 dark:text-slate-100 focus:ring-[#FF6B4A]/20 focus:border-[#F0552F]" placeholder="0.00">
                    </div>
                    <div class="sm:w-1/3">
                        <input type="text" x-model="refTransferencia" placeholder="Ref / Voucher" 
                               class="w-full text-xs font-medium rounded-lg border-slate-200 dark:border-slate-800 bg-white dark:bg-[#0b0f19] text-slate-800 dark:text-slate-100 focus:ring-[#FF6B4A]/20 focus:border-[#F0552F]">
                    </div>
                </div>
            </div>
        </div>

        {{-- Footer Modal --}}
        <div class="p-4 border-t border-slate-100 dark:border-[#FF6B4A]/10 shrink-0 bg-slate-50/50 dark:bg-[#0b0f19]/30 flex items-center justify-end gap-3">
            <button type="button" 
                    @click="showPayModal = false" 
                    class="px-4 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-600 dark:text-slate-300 text-xs font-extrabold transition cursor-pointer focus:outline-none">
                Cancelar
            </button>
            <button type="button" 
                    @click="processSale(true)" 
                    :disabled="loading || totalPagado < total" 
                    class="px-6 py-2.5 rounded-xl bg-[#F0552F] hover:bg-[#D9431F] disabled:bg-slate-300 dark:disabled:bg-slate-800 text-white text-xs font-extrabold shadow-md shadow-[#F0552F]/20 transition cursor-pointer flex items-center gap-2 disabled:cursor-not-allowed focus:outline-none">
                <span x-show="!loading">Confirmar y Registrar Venta</span>
                <span x-show="loading" class="inline-block animate-spin rounded-full h-4 w-4 border-2 border-white border-t-transparent" x-cloak aria-hidden="true"></span>
            </button>
        </div>
    </div>
</div>

        {{-- MODAL TICKET --}}
        <div x-show="showTicket" x-cloak
             class="fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-md flex items-center justify-center p-3 sm:p-5"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">

            <div @click.away="showTicket = false"
                 class="relative w-full max-w-sm max-h-[90vh] bg-white dark:bg-[#090d18] text-slate-900 dark:text-slate-100 rounded-[28px] border border-slate-200/80 dark:border-[#FF6B4A]/20 shadow-2xl flex flex-col overflow-hidden">

                <div class="no-print relative z-10 flex items-center justify-between p-4 border-b border-slate-100 dark:border-[#FF6B4A]/10 shrink-0">
                    <div class="flex items-center gap-3">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#FF6B4A] shadow-[0_0_10px_rgba(255,107,74,0.8)]"></span>
                        <h3 class="text-xs sm:text-sm font-black text-slate-900 dark:text-white uppercase tracking-wider">Ticket de Venta</h3>
                    </div>
                    <button type="button" @click="showTicket = false"
                            class="w-8 h-8 rounded-full bg-[#FFF1EC] dark:bg-[#3A120A]/40 hover:bg-[#FFE1D6] dark:hover:bg-[#5C1B0E]/60 text-[#FF6B4A] dark:text-[#FF8A65] font-bold transition flex items-center justify-center cursor-pointer">
                        ✕
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto custom-scroll">
                    @include('caja.ticket')
                </div>

                <div class="no-print relative z-10 flex items-center gap-3 p-4 border-t border-slate-100 dark:border-[#FF6B4A]/10 shrink-0 bg-slate-50/50 dark:bg-[#0b0f19]/30">
                    <button type="button" @click="showTicket = false"
                            class="flex-1 py-2.5 rounded-2xl bg-slate-100 dark:bg-slate-900/80 hover:bg-slate-200 dark:hover:bg-slate-800 text-slate-700 dark:text-white text-xs font-bold transition cursor-pointer">
                        Cerrar
                    </button>
                    <button type="button" @click="printTicket()"
                            class="flex-1 py-2.5 rounded-2xl bg-[#F0552F] hover:bg-[#D9431F] text-white text-xs font-bold shadow-lg shadow-[#FF6B4A]/25 transition cursor-pointer">
                        Imprimir
                    </button>
                </div>
            </div>
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