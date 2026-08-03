document.addEventListener('alpine:init', () => {
    Alpine.data('stockManagement', () => ({
        // Objeto de modales
        modals: {
            create: false
        },
        searchQuery: '',
        selectedTypeFilter: '',

        currentMovement: {
            product_id: '',
            type: 'entrada',
            quantity: 1,
            reason_preset: '',
            reason_custom: '',
            notes: ''
        },

        init() {
            if (window.sessionSuccess) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    text: window.sessionSuccess,
                    timer: 3000,
                    showConfirmButton: false
                });
            }

            if (window.sessionError) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: window.sessionError
                });
            }
        },

        openCreateModal() {
            this.resetForm();
            this.modals.create = true;
            this.$dispatch('open-modal', 'create');
        },

        closeModal(name) {
            if (this.modals[name] !== undefined) {
                this.modals[name] = false;
            }
            this.$dispatch('close-modal', name);
        },

        resetForm() {
            this.currentMovement = {
                product_id: '',
                type: 'entrada',
                quantity: 1,
                reason_preset: '',
                reason_custom: '',
                notes: ''
            };
        },

        get finalReason() {
            if (this.currentMovement.reason_preset === 'Otro') {
                return this.currentMovement.reason_custom;
            }
            return this.currentMovement.reason_preset;
        }
    }));
});