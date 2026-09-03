<style>
    /* ============================================================
       1. ESTILOS PARA PANTALLA (MODAL POS)
       ============================================================ */
    .ticket-container {
        width: 100%;
        max-width: 300px;
        margin: 0 auto;
        padding: 16px;
        background-color: #ffffff !important;
        color: #000000 !important;
        font-family: 'Courier New', Courier, monospace;
        border-radius: 16px;
    }

    .ticket-container * {
        color: #000000 !important;
    }

    .ticket-divider {
        border-top: 1px dashed #000000;
        margin: 6px 0;
        width: 100%;
    }

    /* ============================================================
       2. REGLAS EXCLUSIVAS DE IMPRESIÓN (Ajuste Térmico 58mm)
       ============================================================ */
    @media print {
        @page {
            margin: 0;
            size: 58mm auto;
        }

        html, body, div, main, section {
            overflow: visible !important;
            height: auto !important;
            min-height: 0 !important;
            background: #ffffff !important;
            filter: none !important;
            backdrop-filter: none !important;
            transform: none !important;
            box-shadow: none !important;
        }

        body * {
            visibility: hidden !important;
        }

        #ticket-print, #ticket-print * {
            visibility: visible !important;
            color: #000000 !important;
            background: #ffffff !important;
        }

        #ticket-print {
            position: fixed !important;
            left: 0 !important;
            top: 0 !important;
            width: 100% !important;
            max-width: 58mm !important;
            padding: 3mm 1mm !important;
            margin: 0 !important;
            box-sizing: border-box !important;
            font-size: 10px !important;
            line-height: 1.2 !important;
            z-index: 9999999 !important;
            border-radius: 0 !important;
            border: none !important;
            box-shadow: none !important;
        }

        .no-print {
            display: none !important;
        }
    }
</style>

{{-- Estructura del Ticket --}}
<template x-if="lastTicket">
    <div id="ticket-print" class="ticket-container shadow-sm">
        
        {{-- Encabezado --}}
        <div class="text-center">
            <p class="font-bold text-xs uppercase tracking-tight">SCGI NEGOCIOS</p>
            <p class="text-[9px]">Control de Inventarios</p>
            
            <div class="ticket-divider"></div>
            
            {{-- Folio real dinámico recibido desde la respuesta de la venta --}}
            <p class="font-bold text-[11px]">FOLIO: <span x-text="lastTicket.folio"></span></p>
            <p class="text-[9px]" x-text="lastTicket.fecha"></p>
            <p class="text-[9px] truncate">Cajero: <span x-text="lastTicket.cajero"></span></p>
        </div>

        <div class="ticket-divider"></div>

        {{-- Detalle de Productos --}}
        <div class="space-y-1.5">
            <template x-for="(item, index) in lastTicket.items" :key="index">
                <div class="text-[10px]">
                    <div class="font-bold break-words leading-tight" x-text="item.name"></div>
                    <div class="flex justify-between items-center text-[9px] mt-0.5">
                        <span x-text="item.quantity + ' x $' + formatNumber(item.precio || item.subtotal / item.quantity)"></span>
                        <span class="font-bold" x-text="'$' + formatNumber(item.subtotal)"></span>
                    </div>
                </div>
            </template>
        </div>

        <div class="ticket-divider"></div>

        {{-- Totales y Métodos de Pago --}}
        <div class="space-y-1">
            <div class="flex justify-between font-bold text-xs">
                <span>TOTAL:</span>
                <span x-text="'$' + formatNumber(lastTicket.total)"></span>
            </div>

            <div class="text-[9px] space-y-0.5 pt-0.5">
                <div class="flex justify-between">
                    <span>Método:</span>
                    <span class="font-bold uppercase" x-text="formatMetodo(lastTicket.metodo_pago)"></span>
                </div>

                <template x-if="lastTicket.metodo_pago === 'efectivo'">
                    <div class="space-y-0.5">
                        <div class="flex justify-between">
                            <span>Recibido:</span>
                            <span x-text="'$' + formatNumber(lastTicket.monto_recibido)"></span>
                        </div>
                        <div class="flex justify-between font-bold">
                            <span>Cambio:</span>
                            <span x-text="'$' + formatNumber(lastTicket.cambio)"></span>
                        </div>
                    </div>
                </template>

                <template x-if="lastTicket.metodo_pago !== 'efectivo'">
                    <div class="flex justify-between">
                        <span>Ref / Aut:</span>
                        <span class="font-bold" x-text="lastTicket.num_referencia || 'N/A'"></span>
                    </div>
                </template>
            </div>
        </div>

        <div class="ticket-divider"></div>
        
        {{-- Pie de Página --}}
        <p class="text-center mt-2 font-bold text-[9px] uppercase tracking-tight">
            ¡Gracias por su compra!
        </p>
    </div>
</template>