document.addEventListener('alpine:init', () => {
    Alpine.data('posApp', () => ({
        products: window.posData ? window.posData.products : [],
        storeUrl: window.posData ? window.posData.storeUrl : '',
        csrfToken: window.posData ? window.posData.csrfToken : '',
        
        search: '',
        selectedCategory: 'all',
        cart: [],
        paymentMethod: 'efectivo',
        receivedAmount: '',
        loading: false,

        init() {
            // Foco automático en el buscador al cargar el componente
            this.$nextTick(() => {
                if (this.$refs.searchInput) {
                    this.$refs.searchInput.focus();
                }
            });
        },

        get filteredProducts() {
            return this.products.filter(product => {
                const matchesCategory = this.selectedCategory === 'all' || product.category_id === this.selectedCategory;
                const searchLower = this.search.toLowerCase();
                const matchesSearch = product.name.toLowerCase().includes(searchLower) || 
                                     (product.sku && product.sku.toLowerCase().includes(searchLower));
                return matchesCategory && matchesSearch;
            });
        },

        get total() {
            return this.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        },

        get change() {
            const received = parseFloat(this.receivedAmount) || 0;
            return received - this.total;
        },

        // Métodos de ayuda para insignias de interfaz
        getStockBadge(stock) {
            if (stock <= 0) {
                return {
                    text: 'Agotado',
                    classes: 'bg-slate-800 text-slate-200 border-slate-700 opacity-60',
                    disabled: true
                };
            } else if (stock <= 5) {
                return {
                    text: `¡Quedan ${stock}!`,
                    classes: 'bg-red-500/10 text-red-500 border-red-500/30 animate-pulse',
                    disabled: false
                };
            } else if (stock < 10) {
                return {
                    text: `Quedan ${stock}`,
                    classes: 'bg-amber-500/10 text-amber-500 border-amber-500/30',
                    disabled: false
                };
            } else {
                return {
                    text: `Stock: ${stock}`,
                    classes: 'bg-emerald-500/10 text-emerald-500 border-emerald-500/30',
                    disabled: false
                };
            }
        },

        addToCart(product) {
            // 1. VALIDACIÓN: Producto Agotado
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

            // CORRECCIÓN AQUÍ: Usar == en lugar de === para evitar duplicados por tipo de dato
            const existingIndex = this.cart.findIndex(item => item.id == product.id);

            // 2. VALIDACIÓN: Superar stock en carrito
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
                // Notificación emergente si el producto agregado tiene stock crítico
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
        },

        formatNumber(value) {
            return new Intl.NumberFormat('es-MX', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(value || 0);
        },

        async processSale() {
            if (this.cart.length === 0) return;

            if (this.paymentMethod === 'efectivo' && this.change < 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Monto insuficiente',
                    text: 'El efectivo recibido es menor al total a pagar.',
                    confirmButtonColor: '#4f46e5'
                });
                return;
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
                        metodo_pago: this.paymentMethod,
                        monto_recibido: this.paymentMethod === 'efectivo' ? parseFloat(this.receivedAmount) : this.total,
                        total: this.total
                    })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Venta Realizada!',
                        html: `Total: <b>$${this.formatNumber(data.total || this.total)}</b><br>` + 
                              (this.paymentMethod === 'efectivo' ? `Cambio: <b>$${this.formatNumber(data.cambio || this.change)}</b>` : ''),
                        confirmButtonColor: '#4f46e5'
                    });

                    // Descontar stock localmente para actualizar la vista sin necesidad de recargar
                    // CORRECCIÓN AQUÍ TAMBIÉN: Usar == para que encuentre correctamente el producto y actualice la vista
                    this.cart.forEach(item => {
                        const product = this.products.find(p => p.id == item.id);
                        if (product) product.stock -= item.quantity;
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
                    confirmButtonColor: '#4f46e5'
                });
            } finally {
                this.loading = false;
            }
        }
    }));
});