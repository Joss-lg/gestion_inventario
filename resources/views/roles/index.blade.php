@extends('layouts.app')
@section('title', 'Gestión de Roles y Permisos')

@section('content')
<x-app-container>
    <div x-data="roleManagement()" class="space-y-6">

        {{-- ENCABEZADO PRINCIPAL --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200/80 dark:border-slate-800/80">
            <div>
                <h1 class="page-title">Gestión de Roles y Permisos</h1>
                <p class="page-subtitle">Define qué puede hacer cada rol dentro del sistema</p>
            </div>
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <a href="{{ route('users.index') }}"
                    class="bg-transparent hover:bg-slate-100 dark:hover:bg-slate-800/60 text-slate-600 dark:text-slate-300 font-bold rounded-2xl px-5 py-2.5 border border-slate-200/80 dark:border-slate-800/80 active:scale-95 transition w-full sm:w-auto uppercase tracking-wider text-xs cursor-pointer inline-flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Usuarios</span>
                </a>
                @if(auth()->user()->hasPermission('create-roles'))
                    <button type="button" @click="openCreateModal()"
                        class="bg-[#FF4500] hover:bg-[#E63E00] text-white font-bold rounded-2xl px-5 py-2.5 shadow-md shadow-[#FF4500]/25 active:scale-95 transition w-full sm:w-auto uppercase tracking-wider text-xs cursor-pointer inline-flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Crear Rol</span>
                    </button>
                @endif
            </div>
        </div>

        {{-- MÉTRICAS --}}
        <div class="grid grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
            <div class="kpi-card">
                <span class="kpi-label">Roles Registrados</span>
                <p class="kpi-value mt-1">{{ $roles->count() }}</p>
            </div>
            <div class="kpi-card">
                <span class="kpi-label">Permisos Disponibles</span>
                <p class="kpi-value text-[#F0552F] dark:text-[#FF8A65] mt-1">{{ $permissions->count() }}</p>
            </div>
            <div class="kpi-card col-span-2 lg:col-span-1">
                <span class="kpi-label">Módulos</span>
                <p class="kpi-value text-emerald-600 dark:text-emerald-400 mt-1">{{ $permissionsByModule->count() }}</p>
            </div>
        </div>

        {{-- GRID DE ROLES --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @php $rolesProtegidos = ['administrador', 'admin']; @endphp
            @foreach ($roles as $role)
                @php
                    $esProtegido = in_array(strtolower($role->name), $rolesProtegidos);
                    $totalPerms = $permissions->count();
                    $rolePermCount = $role->permissions->count();
                @endphp
                <div class="table-container p-5 flex flex-col gap-4">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <h3 class="text-sm font-black text-slate-900 dark:text-white">{{ $role->name }}</h3>
                            <span class="text-[11px] text-slate-400 font-semibold">{{ $role->users_count }} {{ Str::plural('usuario', $role->users_count) }} asignado{{ $role->users_count === 1 ? '' : 's' }}</span>
                        </div>
                        @if ($esProtegido)
                            <span class="px-2.5 py-1 rounded-xl text-[10px] font-black uppercase tracking-wider bg-[#FF6B4A]/10 text-[#F0552F] dark:text-[#FF8A65] border border-[#FF6B4A]/20 shrink-0">Protegido</span>
                        @endif
                    </div>

                    <div class="flex items-center gap-2">
                        <div class="flex items-center gap-1">
                            @for ($i = 1; $i <= $totalPerms; $i++)
                                <span class="w-1.5 h-1.5 rounded-full {{ $i <= $rolePermCount ? 'bg-[#FF6B4A] shadow-xs shadow-[#FF6B4A]' : 'bg-slate-200 dark:bg-slate-800' }}"></span>
                            @endfor
                        </div>
                        <span class="text-[11px] font-bold text-slate-400">{{ $rolePermCount }}/{{ $totalPerms }} permisos</span>
                    </div>

                    <div class="flex flex-wrap gap-1.5 min-h-[1.5rem]">
                        @forelse ($role->permissions as $permission)
                            <span class="px-2 py-1 rounded-lg text-[10px] font-bold bg-slate-100 dark:bg-slate-900/80 text-slate-600 dark:text-slate-300 border border-slate-200/80 dark:border-slate-800/80">{{ $permission->name }}</span>
                        @empty
                            <span class="text-[11px] text-slate-400 font-semibold italic">Sin permisos asignados</span>
                        @endforelse
                    </div>

                    <div class="flex items-center gap-2 mt-auto pt-2 border-t border-slate-100 dark:border-slate-800/60">
                        @if ($esProtegido)
                            <span class="text-[11px] text-slate-400 font-semibold italic">Acceso total, no editable</span>
                        @else
                            @if(auth()->user()->hasPermission('edit-roles'))
                                <button type="button" @click="openEditModal({{ Illuminate\Support\Js::from(['id' => $role->id, 'name' => $role->name]) }}, {{ Illuminate\Support\Js::from($role->permissions->pluck('id')) }})"
                                    class="flex-1 py-2 rounded-xl bg-[#FF6B4A]/10 hover:bg-[#FF6B4A]/20 text-[#F0552F] dark:text-[#FF8A65] border border-[#FF6B4A]/30 font-bold text-[11px] uppercase tracking-wider transition-all cursor-pointer">
                                    Editar Permisos
                                </button>
                            @endif
                            @if(auth()->user()->hasPermission('delete-roles'))
                                <button type="button" @click="openDeleteModal({{ Illuminate\Support\Js::from(['id' => $role->id, 'name' => $role->name]) }})"
                                    @if ($role->users_count > 0) disabled title="No se puede eliminar, tiene usuarios asignados" @endif
                                    class="p-2 text-rose-500 dark:text-rose-400 bg-rose-500/10 hover:bg-rose-500/20 border border-rose-500/30 rounded-xl transition-all cursor-pointer disabled:opacity-30 disabled:cursor-not-allowed">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            @endif
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        {{-- MODAL CREAR / EDITAR ROL --}}
        <x-modal name="role" title="Gestión de Rol" maxWidth="max-w-2xl">
            <form :action="isEditMode ? '{{ url('roles') }}/' + currentRole.id : '{{ route('roles.store') }}'" method="POST" class="relative z-10 flex flex-col flex-1 min-h-0 bg-transparent">
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
                    <div class="space-y-1.5">
                        <label class="form-label">Nombre del Rol</label>
                        <input type="text" name="name" x-model="currentRole.name" required class="form-input" placeholder="Ej. Supervisor, Almacenista">
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-[10px] font-black uppercase tracking-widest text-[#F0552F] dark:text-[#FF8A65]/80 mb-0">Permisos del Rol</h4>
                            <span class="px-3 py-1 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-[#FF8A65] border border-emerald-500/20 dark:border-[#FF6B4A]/20">
                                <span x-text="selectedPermsCount"></span> / {{ $permissions->count() }} SELECCIONADOS
                            </span>
                        </div>

                        <div class="space-y-4">
                            @foreach ($permissionsByModule as $module => $modulePermissions)
                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-2">{{ $module }}</p>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                        @foreach ($modulePermissions as $permission)
                                            <label class="flex items-center justify-between p-3 bg-slate-100/60 dark:bg-slate-900/60 hover:bg-slate-100 dark:hover:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800/80 cursor-pointer transition-all">
                                                <div class="flex items-center gap-2.5">
                                                    <input type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                                                           x-model.number="currentRolePerms"
                                                           class="w-4 h-4 rounded border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-[#F0552F] dark:text-[#FF6B4A] focus:ring-0">
                                                    <span class="text-xs font-bold text-slate-800 dark:text-slate-200 block line-clamp-1">{{ $permission->name }}</span>
                                                </div>
                                                <span class="w-2 h-2 rounded-full transition-colors shrink-0"
                                                      :class="currentRolePerms.includes({{ $permission->id }}) ? 'bg-[#F0552F] dark:bg-[#FF6B4A] shadow-[0_0_8px_rgba(255,107,74,0.8)]' : 'bg-slate-300 dark:bg-slate-700'"></span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 p-5 sm:px-7 border-t border-slate-100 dark:border-white/5 bg-transparent shrink-0">
                    <button type="button" @click="closeModal('role')" class="btn-secondary">Cancelar</button>
                    <button type="submit" class="px-8 py-2.5 rounded-2xl bg-[#FF4500] hover:bg-[#E63E00] text-white font-bold text-xs transition-all shadow-md shadow-[#FF4500]/25 active:scale-95 cursor-pointer">
                        <span x-text="isEditMode ? 'Guardar Cambios' : 'Guardar Rol'"></span>
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
                    Esta acción eliminará de forma irreversible el rol <strong x-text="formDelete.name"></strong> del sistema.
                </p>
                <form :action="'{{ url('roles') }}/' + formDelete.id" method="POST" class="flex gap-3">
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
<script src="{{ asset('js/components/role-management.js') }}"></script>
@endpush
