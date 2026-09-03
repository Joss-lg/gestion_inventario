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
document.addEventListener('alpine:init', () => {
    Alpine.data('userManagement', () => ({
        modals: { user: false, role: false, delete: false },
        isEditMode: false,
        currentUser: { id: null, name: '', email: '', role_id: 2, is_active: true },
        currentUserPerms: [],
        roleForm: { name: '', description: '', is_active: true },
        rolePerms: [],

        // Objeto único para manejar la eliminación (sirve para usuario Y rol)
        formDelete: { id: null, name: '', type: 'user' },

        get selectedPermsCount() { 
            return Array.isArray(this.currentUserPerms) ? this.currentUserPerms.length : 0; 
        },
        get selectedRolePermsCount() { 
            return Array.isArray(this.rolePerms) ? this.rolePerms.length : 0; 
        },

        // --- LÓGICA DE VISIBILIDAD Y TABLA DE PERMISOS POR MÓDULO ---

        /**
         * Verifica si un permiso específico está activo en el usuario actual.
         * Se asegura de comparar los elementos convirtiéndolos a números de forma limpia.
         */
        hasPermission(permId) {
            if (!permId || permId === 0) return false;
            const targetId = Number(permId);
            return this.currentUserPerms.some(id => Number(id) === targetId);
        },

        /**
         * Controla el toggle del permiso 'Ver' (Visibilidad).
         * Si se desmarca 'Ver', limpia automáticamente los permisos hijos asociados (Crear, Editar, Borrar).
         */
        handleViewToggle(viewPermId, childPermIds = []) {
            if (!viewPermId) return;
            
            this.$nextTick(() => {
                const isViewActive = this.hasPermission(viewPermId);
                
                if (!isViewActive) {
                    const childIdsClean = (Array.isArray(childPermIds) ? childPermIds : [])
                        .filter(id => id !== null && id !== undefined)
                        .map(id => Number(id));

                    this.currentUserPerms = this.currentUserPerms.filter(
                        id => !childIdsClean.includes(Number(id))
                    );
                }
            });
        },

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

            this.formDelete = { id: user.id, name: user.name || '', type: 'user' };
            this.modals.delete = true;
        },

        openDeleteRoleModal(roleId, roleName) {
            this.formDelete = { id: roleId, name: roleName || '', type: 'role' };
            this.modals.delete = true;
        },

        openCreateModal() {
            this.isEditMode = false;
            this.currentUser = { id: null, name: '', email: '', role_id: 2, is_active: true };
            this.currentUserPerms = [];
            this.modals.user = true;
        },

        setUserData(user, userPermsIds) {
            if (!user) return;
            this.isEditMode = true;
            this.currentUser = {
                id: user.id ? parseInt(user.id, 10) : null,
                name: user.name || '',
                email: user.email || '',
                role_id: user.role_id ? parseInt(user.role_id, 10) : 2,
                is_active: user.is_active === undefined ? true : Boolean(Number(user.is_active))
            };
            this.currentUserPerms = Array.isArray(userPermsIds) ? userPermsIds.map(id => parseInt(id, 10)) : [];
            this.modals.user = true;
        },

        openCreateRoleModal() {
            this.roleForm = { name: '', description: '', is_active: true };
            this.rolePerms = [];
            this.modals.role = true;
        },

        closeModal(name) {
            if (this.modals[name] !== undefined) this.modals[name] = false;
        }
    }));
});