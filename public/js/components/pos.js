document.addEventListener('alpine:init', () => {
    Alpine.data('posApp', () => ({
        // === ESTADO BASE ===
        products: window.posData ? window.posData.products : [],
        storeUrl: window.posData ? window.posData.storeUrl : '',
        csrfToken: window.posData ? window.posData.csrfToken : '',

        search: '',
        selectedCategory: 'all',
        cart: [],
        loading: false,

        // === ESTADO DE PAGO DIRECTO ===
        paymentMethod: 'efectivo',
        receivedAmount: '',
        referenceNumber: '',
        isMultiPayMode: false,

        // === ESTADO DE MULTI PAGO FIJO ===
        showPayModal: false,
        showTicket: false,
        lastTicket: null,
        pagoEfectivo: 0,
        pagoTarjeta: 0,
        refTarjeta: '',
        pagoTransferencia: 0,
        refTransferencia: '',

        init() {
            // Ajuste automático: al mover tarjeta o transferencia,
            // el efectivo se recalcula con lo que falta del total.
            this.$watch('pagoTarjeta', () => this.ajustarEfectivo());
            this.$watch('pagoTransferencia', () => this.ajustarEfectivo());

            this.$nextTick(() => {
                if (this.$refs.searchInput) {
                    this.$refs.searchInput.focus();
                }
            });
        },

        // === GETTERS ===
        get filteredProducts() {
            return this.products.filter(product => {
                const matchesCategory = this.selectedCategory === 'all' || product.category_id == this.selectedCategory;
                const searchLower = this.search.toLowerCase();
                const matchesSearch = product.name.toLowerCase().includes(searchLower) ||
                                     (product.sku && product.sku.toLowerCase().includes(searchLower));
                return matchesCategory && matchesSearch;
            });
        },

        get total() {
            return this.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        },

        get singleChange() {
            const received = parseFloat(this.receivedAmount) || 0;
            return received - this.total;
        },

        get totalPagado() {
            return (parseFloat(this.pagoEfectivo) || 0) + 
                   (parseFloat(this.pagoTarjeta) || 0) + 
                   (parseFloat(this.pagoTransferencia) || 0);
        },

        get faltante() {
            const diff = this.total - this.totalPagado;
            return diff > 0 ? parseFloat(diff.toFixed(2)) : 0;
        },

        get cambio() {
            const diff = this.totalPagado - this.total;
            return diff > 0 ? parseFloat(diff.toFixed(2)) : 0;
        },

        // === GESTIÓN DE PAGO ===
        selectDirectPayment(method) {
            this.isMultiPayMode = false;
            this.paymentMethod = method;
            this.showPayModal = false;
        },

        ajustarEfectivo() {
            if (!this.showPayModal) return;
            const tarjeta = Math.max(0, parseFloat(this.pagoTarjeta) || 0);
            const transferencia = Math.max(0, parseFloat(this.pagoTransferencia) || 0);
            const restante = this.total - tarjeta - transferencia;
            this.pagoEfectivo = restante > 0 ? parseFloat(restante.toFixed(2)) : 0;
        },

        abrirModalCobro() {
            if (this.cart.length === 0) return;
            this.isMultiPayMode = true;
            // Limpia y precarga el total completo en efectivo por defecto
            this.pagoTarjeta = 0;
            this.refTarjeta = '';
            this.pagoTransferencia = 0;
            this.refTransferencia = '';
            this.showPayModal = true;
            this.pagoEfectivo = parseFloat(this.total.toFixed(2));
        },

        // === LÓGICA DE BADGES Y CARRITO ===
        getStockBadge(stock) {
            if (stock <= 0) {
                return { text: 'Agotado', classes: 'bg-slate-800 text-slate-200 border-slate-700 opacity-60', disabled: true };
            } else if (stock <= 5) {
                return { text: `¡Quedan ${stock}!`, classes: 'bg-red-500/10 text-red-500 border-red-500/30 animate-pulse', disabled: false };
            } else if (stock < 10) {
                return { text: `Quedan ${stock}`, classes: 'bg-amber-500/10 text-amber-500 border-amber-500/30', disabled: false };
            } else {
                return { text: `Stock: ${stock}`, classes: 'bg-emerald-500/10 text-emerald-500 border-emerald-500/30', disabled: false };
            }
        },

        addToCart(product) {
            if (product.stock <= 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Producto agotado',
                    text: 'Este producto actualmente no cuenta con stock disponible.',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2000
                });
                return;
            }

            const existingIndex = this.cart.findIndex(item => item.id == product.id);

            if (existingIndex > -1) {
                if (this.cart[existingIndex].quantity + 1 > product.stock) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Stock insuficiente',
                        text: `Solo hay ${product.stock} unidades disponibles de este producto.`,
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                    return;
                }
                this.cart[existingIndex].quantity++;
            } else {
                if (product.stock <= 5) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Stock Bajo',
                        text: `¡Atención! Quedan solo ${product.stock} piezas de "${product.name}".`,
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 2500
                    });
                }

                this.cart.push({
                    id: product.id,
                    name: product.name,
                    price: parseFloat(product.price),
                    stock: product.stock,
                    quantity: 1
                });
            }
        },

        updateQuantity(index, amount) {
            const item = this.cart[index];
            const newQuantity = item.quantity + amount;

            if (newQuantity > item.stock) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Límite alcanzado',
                    text: `Stock máximo disponible: ${item.stock}`,
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2000
                });
                return;
            }

            if (newQuantity <= 0) {
                this.removeFromCart(index);
            } else {
                item.quantity = newQuantity;
            }
        },

        removeFromCart(index) {
            this.cart.splice(index, 1);
        },

        clearCart() {
            this.cart = [];
            this.receivedAmount = '';
            this.referenceNumber = '';
            this.isMultiPayMode = false;
            this.showPayModal = false;
            this.pagoEfectivo = 0;
            this.pagoTarjeta = 0;
            this.refTarjeta = '';
            this.pagoTransferencia = 0;
            this.refTransferencia = '';
        },

        // === FORMATOS Y UTILIDADES ===
        formatNumber(value) {
            return new Intl.NumberFormat('es-MX', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(value || 0);
        },

        formatMetodo(metodo) {
            const map = { efectivo: 'Efectivo', tarjeta: 'Tarjeta', transferencia: 'Transferencia' };
            return map[metodo] || metodo;
        },

        printTicket() {
            window.print();
        },

        // === PROCESAMIENTO DE VENTA ===
        async processSale(isMultiPay = false) {
            if (this.cart.length === 0) return;

            let payloadPagos = [];

            if (isMultiPay || this.isMultiPayMode) {
                const efec = Math.round((parseFloat(this.pagoEfectivo) || 0) * 100) / 100;
                const tar = Math.round((parseFloat(this.pagoTarjeta) || 0) * 100) / 100;
                const transf = Math.round((parseFloat(this.pagoTransferencia) || 0) * 100) / 100;

                if (efec > 0) payloadPagos.push({ metodo: 'efectivo', monto: efec, referencia: null });
                if (tar > 0) payloadPagos.push({ metodo: 'tarjeta', monto: tar, referencia: this.refTarjeta ? this.refTarjeta.trim() : null });
                if (transf > 0) payloadPagos.push({ metodo: 'transferencia', monto: transf, referencia: this.refTransferencia ? this.refTransferencia.trim() : null });

                const totalPagadoCentavos = Math.round(this.totalPagado * 100);
                const totalVentaCentavos = Math.round(this.total * 100);

                if (totalPagadoCentavos < totalVentaCentavos) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Monto insuficiente',
                        text: 'El total pagado en el desglose es menor al total a pagar.',
                        confirmButtonColor: '#F0552F'
                    });
                    return;
                }

                if (tar > 0 && (!this.refTarjeta || !this.refTarjeta.trim())) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Referencia requerida',
                        text: 'Ingresa el número de referencia / voucher para el pago con Tarjeta.',
                        confirmButtonColor: '#F0552F'
                    });
                    return;
                }

                if (transf > 0 && (!this.refTransferencia || !this.refTransferencia.trim())) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Referencia requerida',
                        text: 'Ingresa el número de referencia para el pago por Transferencia.',
                        confirmButtonColor: '#F0552F'
                    });
                    return;
                }

                if (payloadPagos.length === 0) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Pago requerido',
                        text: 'Agrega al menos un monto de pago válido.',
                        confirmButtonColor: '#F0552F'
                    });
                    return;
                }
            } else {
                if (this.paymentMethod === 'efectivo' && this.singleChange < 0) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Monto insuficiente',
                        text: 'El efectivo recibido es menor al total a pagar.',
                        confirmButtonColor: '#F0552F'
                    });
                    return;
                }

                if ((this.paymentMethod === 'tarjeta' || this.paymentMethod === 'transferencia') && !this.referenceNumber.trim()) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Referencia requerida',
                        text: `Por favor ingresa el número de ${this.paymentMethod === 'tarjeta' ? 'voucher / autorización' : 'referencia / rastreo'}.`,
                        confirmButtonColor: '#F0552F'
                    });
                    return;
                }

                payloadPagos = [{
                    metodo: this.paymentMethod,
                    monto: this.paymentMethod === 'efectivo' ? parseFloat(this.receivedAmount) : this.total,
                    referencia: this.referenceNumber.trim() || null
                }];
            }

            this.loading = true;

            try {
                const response = await fetch(this.storeUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        items: this.cart,
                        pagos: payloadPagos,
                        metodo_pago: payloadPagos[0].metodo,
                        monto_recibido: payloadPagos[0].monto,
                        num_referencia: payloadPagos[0].referencia,
                        total: this.total
                    })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    this.cart.forEach(item => {
                        const product = this.products.find(p => p.id == item.id);
                        if (product) product.stock -= item.quantity;
                    });

                    this.lastTicket = data.ticket;
                    this.showTicket = true;

                    let htmlMensaje = `<strong>Total: $${this.formatNumber(this.total)}</strong>`;
                    if (!isMultiPay && !this.isMultiPayMode && this.paymentMethod === 'efectivo') {
                        const recibido = parseFloat(this.receivedAmount);
                        const cambio = Math.max(0, recibido - this.total);
                        htmlMensaje += `<br>Recibido: $${this.formatNumber(recibido)}`;
                        htmlMensaje += `<br>Cambio: $${this.formatNumber(cambio)}`;
                    } else if (isMultiPay || this.isMultiPayMode) {
                        htmlMensaje += `<br>Pagado: $${this.formatNumber(this.totalPagado)}`;
                        if (this.cambio > 0) {
                            htmlMensaje += `<br>Cambio: $${this.formatNumber(this.cambio)}`;
                        }
                    }

                    Swal.fire({
                        icon: 'success',
                        title: '¡Venta realizada!',
                        html: htmlMensaje,
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 4000,
                        timerProgressBar: true
                    });

                    this.clearCart();
                } else {
                    throw new Error(data.message || 'Ocurrió un error al procesar la venta.');
                }

            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error en la venta',
                    text: error.message || 'Error de comunicación con el servidor.',
                    confirmButtonColor: '#F0552F'
                });
            } finally {
                this.loading = false;
            }
        }
    }));
});