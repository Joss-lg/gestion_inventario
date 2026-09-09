@extends('layouts.app')
@section('title', 'Gestión de Usuarios')

@section('content')
<x-app-container>
    <div x-data="userManagement()" class="space-y-6">

        {{-- ENCABEZADO PRINCIPAL --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200/80 dark:border-slate-800/80">
            <div>
                <h1 class="page-title">Gestión de Usuarios</h1>
                <p class="page-subtitle">Administra los accesos, roles y permisos del personal del sistema</p>
            </div>
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <a href="{{ route('roles.index') }}"
                    class="bg-transparent hover:bg-slate-100 dark:hover:bg-slate-800/60 text-slate-600 dark:text-slate-300 font-bold rounded-2xl px-5 py-2.5 border border-slate-200/80 dark:border-slate-800/80 active:scale-95 transition w-full sm:w-auto uppercase tracking-wider text-xs cursor-pointer inline-flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Roles y Permisos</span>
                </a>

                @if(auth()->user()->hasPermission('create-users'))
                    <button type="button" @click="openCreateModal()"
                        class="bg-[#FF4500] hover:bg-[#E63E00] text-white font-bold rounded-2xl px-5 py-2.5 shadow-md shadow-[#FF4500]/25 active:scale-95 transition w-full sm:w-auto uppercase tracking-wider text-xs cursor-pointer inline-flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                        </svg>
                        <span>Nuevo Usuario</span>
                    </button>
                @endif
            </div>
        </div>

        {{-- MÉTRICAS Y KPIS --}}
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-3 sm:gap-4">
            <div class="kpi-card">
                <span class="kpi-label">Total Usuarios</span>
                <p class="kpi-value mt-1">{{ $totalUsers }}</p>
            </div>
            <div class="kpi-card">
                <span class="kpi-label">Administradores</span>
                <p class="kpi-value text-[#F0552F] dark:text-[#FF8A65] mt-1">{{ $totalAdmins }}</p>
            </div>
            <div class="kpi-card">
                <span class="kpi-label">Operadores</span>
                <p class="kpi-value text-emerald-600 dark:text-emerald-400 mt-1">{{ $totalOperators }}</p>
            </div>
            <div class="kpi-card">
                <span class="kpi-label">Usuarios Activos</span>
                <p class="kpi-value text-sky-600 dark:text-sky-400 mt-1">{{ $activeUsers }}</p>
            </div>
            <div class="kpi-card col-span-2 lg:col-span-1">
                <span class="kpi-label">Cajas Abiertas</span>
                <p class="kpi-value text-amber-600 dark:text-amber-400 mt-1">{{ $cajasAbiertasHoy }}</p>
            </div>
        </div>

        {{-- CONTENEDOR PRINCIPAL / TABLA Y TARJETAS --}}
        <div class="table-container">
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr>
                            <th class="table-th">Usuario</th>
                            <th class="table-th">Rol</th>
                            <th class="table-th">Permisos</th>
                            <th class="table-th">Estado</th>
                            <th class="table-th">Caja Activa</th>
                            <th class="table-th text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                        <tr class="table-tr">
                            <td class="table-td">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-2xl bg-[#FF6B4A]/10 dark:bg-[#FF6B4A]/20 text-[#F0552F] dark:text-[#FF8A65] font-black flex items-center justify-center text-sm border border-[#FF6B4A]/20 shrink-0">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-900 dark:text-white">{{ $user->name }}</div>
                                        <div class="text-[11px] text-slate-400 font-semibold">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="table-td">
                                <span class="px-3 py-1 rounded-xl text-[10px] font-black uppercase tracking-wider border shadow-xs {{ optional($user->role)->name === 'Administrador' ? 'bg-[#FF6B4A]/10 text-[#F0552F] dark:text-[#FF8A65] border-[#FF6B4A]/20' : 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20' }}">
                                    {{ optional($user->role)->name ?? 'Sin Rol' }}
                                </span>
                            </td>
                            <td class="table-td">
                                <div class="flex items-center gap-2">
                                    <div class="flex items-center gap-1">
                                        @php
                                            $totalPermsCount = $totalPermissions;
                                            $userPermCount = optional($user->role)->permissions->count() ?? 0;
                                        @endphp
                                        @for ($i = 1; $i <= $totalPermsCount; $i++)
                                            <span class="w-1.5 h-1.5 rounded-full {{ $i <= $userPermCount ? 'bg-[#FF6B4A] shadow-xs shadow-[#FF6B4A]' : 'bg-slate-200 dark:bg-slate-800' }}"></span>
                                        @endfor
                                    </div>
                                    <span class="text-[11px] font-bold text-slate-400">
                                        {{ $userPermCount }}/{{ $totalPermsCount }}
                                    </span>
                                </div>
                            </td>
                            <td class="table-td">
                                @if($user->is_active)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-[10px] font-black uppercase tracking-wider bg-sky-500/10 text-sky-600 dark:text-sky-400 border border-sky-500/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-sky-500 animate-pulse"></span>
                                        Activo
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-[10px] font-black uppercase tracking-wider bg-slate-500/10 text-slate-400 border border-slate-500/20">
                                        Inactivo
                                    </span>
                                @endif
                            </td>
                            <td class="table-td">
                                @if ($user->cajaMovimientos && $user->cajaMovimientos->isNotEmpty())
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl text-[10px] font-black uppercase tracking-wider bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                        Abierta
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl text-[10px] font-black uppercase tracking-wider bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20">
                                        Cerrada
                                    </span>
                                @endif
                            </td>
                            <td class="table-td text-right">
                                <div class="flex items-center justify-end gap-3 whitespace-nowrap">
                                    @if(auth()->user()->hasPermission('edit-users'))
                                        <button type="button" @click="setUserData({{ Illuminate\Support\Js::from($user) }})"
                                            class="p-2 text-[#FF6B4A] dark:text-[#FF8A65] bg-[#FF6B4A]/10 hover:bg-[#FF6B4A]/20 border border-[#FF6B4A]/30 dark:border-[#FF6B4A]/30 rounded-xl transition-all cursor-pointer inline-flex items-center" title="Editar Usuario">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                        </button>
                                    @endif
                                    @if($user->id !== 1 && auth()->user()->hasPermission('delete-users'))
                                        <button type="button" @click="openDeleteModal({{ Illuminate\Support\Js::from($user) }})"
                                            class="p-2 text-rose-500 dark:text-rose-400 bg-rose-500/10 hover:bg-rose-500/20 border border-rose-500/30 dark:border-rose-500/30 rounded-xl transition-all cursor-pointer inline-flex items-center" title="Eliminar Usuario">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- VISTA MÓVIL --}}
            <div class="block md:hidden divide-y divide-slate-100 dark:divide-slate-800/60">
                @foreach($users as $user)
                <div class="p-4 space-y-3 hover:bg-[#FFF1EC]/40 dark:hover:bg-[#FF6B4A]/5 transition-colors">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3 overflow-hidden">
                            <div class="w-9 h-9 rounded-2xl bg-[#FF6B4A]/10 text-[#F0552F] dark:text-[#FF8A65] font-black flex items-center justify-center text-sm border border-[#FF6B4A]/20 shrink-0">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            <div class="truncate">
                                <div class="font-bold text-slate-900 dark:text-white text-xs truncate">{{ $user->name }}</div>
                                <div class="text-[11px] text-slate-400 truncate font-semibold">{{ $user->email }}</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 shrink-0">
                            @if(auth()->user()->hasPermission('edit-users'))
                                <button type="button" @click="setUserData({{ Illuminate\Support\Js::from($user) }})"
                                    class="p-2 text-[#FF6B4A] dark:text-[#FF8A65] bg-[#FF6B4A]/10 hover:bg-[#FF6B4A]/20 border border-[#FF6B4A]/30 dark:border-[#FF6B4A]/30 rounded-xl active:scale-95 transition-all cursor-pointer inline-flex items-center" title="Editar Usuario">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                </button>
                            @endif
                            @if($user->id !== 1 && auth()->user()->hasPermission('delete-users'))
                                <button type="button" @click="openDeleteModal({{ Illuminate\Support\Js::from($user) }})"
                                    class="p-2 text-rose-500 dark:text-rose-400 bg-rose-500/10 hover:bg-rose-500/20 border border-rose-500/30 dark:border-rose-500/30 rounded-xl active:scale-95 transition-all cursor-pointer inline-flex items-center" title="Eliminar Usuario">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- MODAL REGISTRAR / EDITAR USUARIO --}}
        <x-modal name="user" title="Gestión de Usuario" maxWidth="max-w-2xl">
            <form :action="isEditMode ? '{{ url('usuarios') }}/' + currentUser.id : '{{ route('users.store') }}'" method="POST" class="relative z-10 flex flex-col flex-1 min-h-0 bg-transparent">
                @csrf
                <input type="hidden" name="_method" value="PUT" :disabled="!isEditMode">
                
                @if ($errors->any())
                    <div class="mx-5 sm:mx-7 mt-4 p-3 bg-rose-500/10 border border-rose-500/20 text-rose-600 dark:text-rose-400 text-xs rounded-2xl shrink-0">
                        <p class="font-bold uppercase tracking-wider text-[11px] mb-1">Se encontraron errores:</p>
                        <ul class="list-disc pl-4 space-y-0.5 text-[11px]">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="p-5 sm:p-7 space-y-5 overflow-y-auto flex-1 min-h-0 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                    <div>
                        <h4 class="text-[10px] font-black uppercase tracking-widest text-[#F0552F] dark:text-[#FF8A65]/80 mb-3">Datos Básicos</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                            <div class="space-y-1.5">
                                <label class="form-label">Nombre Completo</label>
                                <input type="text" name="name" x-model="currentUser.name" required class="form-input" placeholder="Ej. Juan Pérez">
                            </div>
                            <div class="space-y-1.5">
                                <label class="form-label">Correo Electrónico</label>
                                <input type="email" name="email" x-model="currentUser.email" required class="form-input" placeholder="admin@scgi.mx">
                            </div>
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="form-label">
                            Contraseña <span x-show="isEditMode" class="text-slate-400 dark:text-slate-500 font-normal lowercase">(opcional al editar)</span>
                        </label>
                        <input type="password" name="password" :required="!isEditMode" class="form-input" placeholder="••••••••">
                    </div>

                    <template x-if="currentUser.role && currentUser.role.name === 'Administrador'">
                        <div>
                            <input type="hidden" name="role_id" value="1">
                            <input type="hidden" name="is_active" value="1">
                        </div>
                    </template>

                    <template x-if="!(currentUser.role && currentUser.role.name === 'Administrador')">
                        <div class="space-y-5">
                            <div>
                                <h4 class="text-[10px] font-black uppercase tracking-widest text-[#F0552F] dark:text-[#FF8A65]/80 mb-3">Rol del Usuario</h4>
                                @php
                                    $orderedRoles = $roles->sortBy(function ($role) {
                                        return match (strtolower($role->name)) {
                                            'administrador' => 0,
                                            'cajero' => 1,
                                            default => 2,
                                        };
                                    })->values();
                                @endphp
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    @foreach ($orderedRoles as $role)
                                        <label @click="currentUser.role_id = {{ $role->id }}"
                                            class="relative flex flex-col p-4 rounded-2xl border-2 cursor-pointer transition-all"
                                            :class="currentUser.role_id == {{ $role->id }} ? 'border-[#FF6B4A] dark:border-[#FF6B4A] bg-[#FFF1EC]/60 dark:bg-[#FF6B4A]/10 shadow-md dark:shadow-[0_0_15px_rgba(255,107,74,0.2)]' : 'border-slate-200/80 dark:border-slate-800/80 bg-slate-50/50 dark:bg-slate-900/40 hover:border-slate-300 dark:hover:border-slate-700'">
                                            
                                            <input type="radio" name="role_id" value="{{ $role->id }}"
                                                x-model.number="currentUser.role_id"
                                                :checked="currentUser.role_id == {{ $role->id }}" class="sr-only">
                                                
                                            <div class="flex items-center justify-between mb-2.5">
                                                <div class="w-8 h-8 rounded-xl flex items-center justify-center {{ $role->name === 'Administrador' ? 'bg-[#FF6B4A]/15 text-[#F0552F] dark:bg-[#FF6B4A]/20 dark:text-[#FF8A65]' : 'bg-emerald-500/15 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400' }}">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                                </div>
                                            </div>

                                            <span class="text-xs font-black text-slate-900 dark:text-white">{{ $role->name }}</span>
                                            <span class="text-[10px] text-slate-500 dark:text-slate-400 font-semibold mt-0.5">{{ $role->name === 'Administrador' ? 'Acceso completo a las funciones' : 'Operaciones limitadas' }}</span>
                                            <span class="mt-2.5 text-[10px] font-black text-[#F0552F] dark:text-[#FF8A65] flex items-center gap-1 uppercase tracking-wider" x-show="currentUser.role_id == {{ $role->id }}">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                SELECCIONADO
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <div class="flex items-center justify-between p-4 bg-slate-100/80 dark:bg-slate-900/80 border border-slate-200/80 dark:border-slate-800/80 rounded-2xl">
                                <div>
                                    <span class="text-xs font-bold text-slate-900 dark:text-white block uppercase tracking-wider">Estado de la cuenta</span>
                                    <span class="text-[10px] text-slate-500 dark:text-slate-400 font-semibold">Determina si el usuario puede ingresar al sistema</span>
                                </div>
                                <input type="hidden" name="is_active" :value="currentUser.is_active ? 1 : 0">
                                <button type="button" @click="currentUser.is_active = !currentUser.is_active"
                                    class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none"
                                    :class="currentUser.is_active ? 'bg-[#F0552F] dark:bg-[#FF6B4A]' : 'bg-slate-300 dark:bg-slate-700'">
                                    <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow-md transition duration-200 ease-in-out"
                                        :class="currentUser.is_active ? 'translate-x-5' : 'translate-x-0'"></span>
                                </button>
                            </div>

                            <div class="p-3 bg-slate-100/60 dark:bg-slate-900/60 border border-slate-200/80 dark:border-slate-800/80 rounded-2xl">
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 font-semibold leading-relaxed">
                                    Los permisos de este usuario dependen del rol seleccionado.
                                    <a href="{{ route('roles.index') }}" class="text-[#F0552F] dark:text-[#FF8A65] font-bold underline">Configúralos en Roles y Permisos.</a>
                                </p>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="flex items-center justify-end gap-3 p-5 sm:px-7 border-t border-slate-100 dark:border-white/5 bg-transparent shrink-0">
                    <button type="button" @click="closeModal('user')" class="btn-secondary">Cancelar</button>
                    <button type="submit" class="px-8 py-2.5 rounded-2xl bg-[#FF4500] hover:bg-[#E63E00] text-white font-bold text-xs transition-all shadow-md shadow-[#FF4500]/25 active:scale-95 cursor-pointer">
                        <span x-text="isEditMode ? 'Guardar Cambios' : 'Guardar Usuario'"></span>
                    </button>
                </div>
            </form>
        </x-modal>

        {{-- MODAL CONFIRMAR ELIMINACIÓN --}}
        <x-modal name="delete" title="¿Confirmar Eliminación?" maxWidth="max-w-sm" dotColor="bg-rose-500">
            <div class="p-6 text-center">
                <div class="w-12 h-12 rounded-2xl bg-rose-500/10 text-rose-600 dark:text-rose-400 flex items-center justify-center mx-auto mb-4 border border-rose-500/20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 mb-6 font-semibold px-2 leading-relaxed">
                    Esta acción eliminará de forma irreversible al usuario <strong x-text="formDelete.name"></strong> del sistema.
                </p>
                <form :action="'{{ url('usuarios') }}/' + formDelete.id" method="POST" class="flex gap-3">
                    @csrf
                    @method('DELETE')
                    <button type="button" @click="closeModal('delete')" class="btn-secondary w-1/2 justify-center">Cancelar</button>
                    <button type="submit" class="w-1/2 py-2.5 rounded-2xl bg-rose-600 hover:bg-rose-700 text-white font-black text-xs transition-all shadow-md active:scale-95 cursor-pointer">Eliminar</button>
                </form>
            </div>
        </x-modal>

    </div>
</x-app-container>
@endsection

@push('scripts')
<script>
    window.sessionSuccess = @json(session('success'));
    window.sessionError = @json(session('error'));
</script>
<script src="{{ asset('js/components/user-management.js') }}"></script>
@endpush