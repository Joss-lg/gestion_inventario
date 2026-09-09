document.addEventListener('DOMContentLoaded', () => {
    const isDarkMode = () => document.documentElement.classList.contains('dark');

    // 1. Configuración global de Toasts para notificaciones de sesión
    window.notify = (icon, title) => {
        if (typeof Swal === 'undefined') return;
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 4000,
            timerProgressBar: true,
            background: isDarkMode() ? '#0f172a' : '#ffffff',
            color: isDarkMode() ? '#f8fafc' : '#0f172a',
        });
        Toast.fire({ icon, title });
    };

    if (window.sessionSuccess) window.notify('success', window.sessionSuccess);
    if (window.sessionError) window.notify('error', window.sessionError);
});

// 2. Componente Alpine.js para la Gestión de Usuarios
// Los permisos ahora se gestionan por rol (ver roleManagement en /roles),
// este componente ya no maneja permisos ni roles individuales.
document.addEventListener('alpine:init', () => {
    Alpine.data('userManagement', () => ({
        modals: { user: false, delete: false },
        isEditMode: false,
        currentUser: { id: null, name: '', email: '', role_id: 2, is_active: true },

        // Objeto para manejar la eliminación de usuarios
        formDelete: { id: null, name: '' },

        // --- APERTURA Y CONTROL DE MODALES ---

        openDeleteModal(user) {
            if (!user) return;

            if (user.id === 1 || parseInt(user.id, 10) === 1) {
                if (window.notify) {
                    window.notify('error', 'El Super Administrador principal no puede ser eliminado.');
                } else {
                    alert('El Super Administrador principal no puede ser eliminado.');
                }
                return;
            }

            this.formDelete = { id: user.id, name: user.name || '' };
            this.modals.delete = true;
        },

        openCreateModal() {
            this.isEditMode = false;
            this.currentUser = { id: null, name: '', email: '', role_id: 2, is_active: true };
            this.modals.user = true;
        },

        setUserData(user) {
            if (!user) return;
            this.isEditMode = true;
            this.currentUser = {
                id: user.id ? parseInt(user.id, 10) : null,
                name: user.name || '',
                email: user.email || '',
                role_id: user.role_id ? parseInt(user.role_id, 10) : 2,
                is_active: user.is_active === undefined ? true : Boolean(Number(user.is_active))
            };
            this.modals.user = true;
        },

        closeModal(name) {
            if (this.modals[name] !== undefined) this.modals[name] = false;
        }
    }));
});
