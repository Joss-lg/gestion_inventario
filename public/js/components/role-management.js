document.addEventListener('DOMContentLoaded', () => {
    const isDarkMode = () => document.documentElement.classList.contains('dark');

    // Configuración global de Toasts para notificaciones de sesión
    window.notify = window.notify || ((icon, title) => {
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
    });

    if (window.sessionSuccess) window.notify('success', window.sessionSuccess);
    if (window.sessionError) window.notify('error', window.sessionError);
});

// Componente Alpine.js para la Gestión de Roles y Permisos
document.addEventListener('alpine:init', () => {
    Alpine.data('roleManagement', () => ({
        modals: { role: false, delete: false },
        isEditMode: false,
        currentRole: { id: null, name: '' },
        currentRolePerms: [],
        formDelete: { id: null, name: '' },

        get selectedPermsCount() {
            return Array.isArray(this.currentRolePerms) ? this.currentRolePerms.length : 0;
        },

        openCreateModal() {
            this.isEditMode = false;
            this.currentRole = { id: null, name: '' };
            this.currentRolePerms = [];
            this.modals.role = true;
        },

        openEditModal(role, rolePermIds) {
            if (!role) return;
            this.isEditMode = true;
            this.currentRole = {
                id: role.id ? parseInt(role.id, 10) : null,
                name: role.name || '',
            };
            this.currentRolePerms = Array.isArray(rolePermIds) ? rolePermIds.map(id => parseInt(id, 10)) : [];
            this.modals.role = true;
        },

        openDeleteModal(role) {
            if (!role) return;
            this.formDelete = { id: role.id, name: role.name || '' };
            this.modals.delete = true;
        },

        closeModal(name) {
            if (this.modals[name] !== undefined) this.modals[name] = false;
        },
    }));
});
