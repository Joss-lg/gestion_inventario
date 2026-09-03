<!-- resources/views/pos/partials/ticket-modal.blade.php -->
<div x-show="showTicket"
     x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm overflow-y-auto"
     @keydown.escape.window="showTicket = false">

    <!-- CONTENEDOR DEL MODAL EN PANTALLA -->
    <div class="relative w-full max-w-sm bg-slate-900 border border-slate-800 rounded-2xl shadow-2xl overflow-hidden text-slate-200 my-8">

        <!-- CABECERA DEL MODAL (NO IMPRIMIBLE) -->
        <div class="no-print flex items-center justify-between p-4 border-b border-slate-800 bg-slate-900/50">
            <div class="flex items-center gap-2">
                <div class="p-2 bg-emerald-500/10 text-emerald-400 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                </div>
                <h3 class="font-semibold text-white">Comprobante de Venta</h3>
            </div>
            <button @click="showTicket = false" class="text-slate-400 hover:text-white transition-colors p-1 rounded-lg hover:bg-slate-800">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <!-- CUERPO / VISTA PREVIA DEL TICKET -->
        <div class="p-4 overflow-y-auto max-h-[70vh] bg-slate-950/50">
            <template x-if="lastTicket">
                <!-- ESTRUCTURA DEL TICKET EN PAPEL -->
                <div id="ticket-print" class="bg-white text-black p-4 rounded-xl shadow-inner font-mono text-xs mx-auto max-w-[280px]">

                    <!-- Datos del Negocio -->
                    <div class="text-center mb-2">
                        <h2 class="font-bold text-sm tracking-wider uppercase" x-text="lastTicket.tienda_nombre || 'MI TIENDA'"></h2>
                        <p class="text-[10px]" x-text="lastTicket.tienda_direccion || 'Dirección de la tienda'"></p>
                        <p class="text-[10px]" x-text="'Tel: ' + (lastTicket.tienda_telefono || '0000000000')"></p>
                    </div>

                    <div class="border-b-dashed my-2"></div>

                    <!-- Datos del Folio y Fecha -->
                    <div class="text-[10px] space-y-0.5">
                        <div class="flex-print flex justify-between">
                            <span>Folio:</span>
                            <span class="font-bold" x-text="'#' + String(lastTicket.id || lastTicket.folio || '0').padStart(6, '0')"></span>
                        </div>
                        <div class="flex-print flex justify-between">
                            <span>Fecha:</span>
                            <span x-text="lastTicket.fecha"></span>
                        </div>
                        <div class="flex-print flex justify-between">
                            <span>Atendido por:</span>
                            <span x-text="lastTicket.cajero || 'Cajero'"></span>
                        </div>
                    </div>

                    <div class="border-b-dashed my-2"></div>

                    <!-- Encabezados -->
                    <div class="flex-print flex justify-between font-bold text-[10px] mb-1">
                        <span class="w-1/2 text-left">CANT/PROD</span>
                        <span class="w-1/4 text-right">P.U.</span>
                        <span class="w-1/4 text-right">TOTAL</span>
                    </div>

                    <!-- Lista de Productos -->
                    <div class="space-y-1">
                        <template x-for="item in lastTicket.items" :key="item.id">
                            <div>
                                <div class="font-bold uppercase text-[10px]" x-text="item.name"></div>
                                <div class="flex-print flex justify-between text-[10px]">
                                    <span class="w-1/2 text-left" x-text="item.quantity + ' x $' + formatNumber(item.price)"></span>
                                    <span class="w-1/2 text-right font-bold" x-text="'$' + formatNumber(item.quantity * item.price)"></span>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="border-b-dashed my-2"></div>

                    <!-- Totales y Pago -->
                    <div class="space-y-1 text-[10px]">
                        <div class="flex-print flex justify-between font-bold text-xs">
                            <span>TOTAL:</span>
                            <span x-text="'$' + formatNumber(lastTicket.total)"></span>
                        </div>

                        <div class="border-b-dashed my-1"></div>

                        <div class="flex-print flex justify-between">
                            <span>Método Pago:</span>
                            <span class="font-bold uppercase" x-text="formatMetodo(lastTicket.metodo_pago)"></span>
                        </div>

                        <template x-if="lastTicket.metodo_pago === 'efectivo'">
                            <div>
                                <div class="flex-print flex justify-between">
                                    <span>Efectivo Recibido:</span>
                                    <span x-text="'$' + formatNumber(lastTicket.monto_recibido)"></span>
                                </div>
                                <div class="flex-print flex justify-between font-bold">
                                    <span>Cambio:</span>
                                    <span x-text="'$' + formatNumber((lastTicket.monto_recibido || 0) - lastTicket.total)"></span>
                                </div>
                            </div>
                        </template>

                        <template x-if="lastTicket.metodo_pago !== 'efectivo'">
                            <div class="flex-print flex justify-between">
                                <span>Ref / Voucher:</span>
                                <span class="font-bold" x-text="lastTicket.num_referencia"></span>
                            </div>
                        </template>
                    </div>

                    <div class="border-b-dashed my-2"></div>

                    <!-- Pie del Ticket -->
                    <div class="text-center text-[9px] mt-2 space-y-0.5">
                        <p class="font-bold">¡GRACIAS POR SU COMPRA!</p>
                        <p>Conserve este ticket para cualquier aclaración.</p>
                    </div>

                </div>
            </template>
        </div>

        <!-- BOTONES DE ACCIÓN (NO IMPRIMIBLES) -->
        <div class="no-print p-4 border-t border-slate-800 bg-slate-900 flex gap-3">
            <button @click="showTicket = false"
                    class="flex-1 py-2.5 px-4 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl font-medium text-sm transition-colors border border-slate-700/50">
                Cerrar
            </button>
            <button @click="printTicket()"
                    class="flex-1 py-2.5 px-4 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl font-medium text-sm transition-colors shadow-lg shadow-indigo-600/25 flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Imprimir
            </button>
        </div>

    </div>
</div>