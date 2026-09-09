@extends('layouts.app')

@section('title', 'SCGI - Gestión de Categorías')
@section('header_title', 'Gestión de Categorías')

@section('content')
<x-app-container>
<div class="max-w-5xl mx-auto space-y-4 sm:space-y-6" x-data="categoryManagement()">
    
    {{-- ALERTAS DE SESIÓN --}}
    @if(session('success'))
        <div class="badge-emerald w-full p-4 justify-start text-xs rounded-2xl mb-4">
            <div class="p-2 rounded-xl bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <span class="font-bold tracking-wide">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="badge-rose w-full p-4 justify-start text-xs rounded-2xl mb-4">
            <div class="p-2 rounded-xl bg-rose-500/20 text-rose-600 dark:text-rose-400 shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <span class="font-bold tracking-wide">{{ session('error') }}</span>
        </div>
    @endif

    {{-- ENCABEZADO PRINCIPAL --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 pb-4 sm:pb-6 border-b border-slate-200/80 dark:border-slate-800/80">
        <div>
            <h1 class="page-title">Gestión de Categorías</h1>
            <p class="page-subtitle">Administra las clasificaciones globales para el control de inventario</p>
        </div>
        
        @if(auth()->user()->hasPermission('create-categories'))
            <button @click="openCreateModal()" class="bg-[#FF4500] hover:bg-[#E63E00] text-white font-bold rounded-2xl px-5 py-2.5 shadow-md shadow-[#FF4500]/25 active:scale-95 transition w-full sm:w-auto uppercase tracking-wider text-xs cursor-pointer inline-flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                <span>Nueva Categoría</span>
            </button>
        @endif
    </div>

    {{-- TABLA DE CATEGORÍAS --}}
    <div class="table-container">
        <div class="px-6 py-5 bg-slate-50 dark:bg-slate-900/40 border-b border-slate-200/80 dark:border-slate-800/80">
            <h2 class="text-xs sm:text-sm font-black text-slate-800 dark:text-white uppercase tracking-wider">Categorías Activas</h2>
            <p class="text-[10px] sm:text-[11px] text-slate-500 dark:text-slate-400 font-bold mt-0.5">Listado maestro en tiempo real</p>
        </div>
        
        {{-- VISTA MÓVIL --}}
        <div class="block md:hidden divide-y divide-slate-100 dark:divide-slate-800/60">
            @forelse($categories as $category)
                <div class="p-4 space-y-3 hover:bg-[#FFF1EC]/40 dark:hover:bg-[#FF6B4A]/5 transition-colors">
                    <div class="flex items-center justify-between gap-2">
                        <span class="font-black text-slate-900 dark:text-white px-3 py-1.5 bg-slate-100 dark:bg-slate-800/80 border border-slate-200/80 dark:border-slate-800 rounded-xl text-xs tracking-wide">
                            {{ $category->name }}
                        </span>
                        <span class="font-bold text-slate-400 dark:text-slate-500 text-xs tabular-nums">
                            #{{ $loop->iteration }}
                        </span>
                    </div>

                    <p class="text-xs text-slate-500 dark:text-slate-400 font-medium leading-relaxed">
                        {{ $category->description ?? 'Sin descripción' }}
                    </p>

                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100/60 dark:border-slate-800/40">
                        <div class="inline-flex items-center justify-end gap-3">
                            @if(auth()->user()->hasPermission('edit-categories'))
                                <button @click="openEditModal({{ $category->id }}, '{{ addslashes($category->name) }}', '{{ addslashes($category->description) }}')" 
                                        class="p-2 text-[#FF6B4A] dark:text-[#FF8A65] bg-[#FF6B4A]/10 hover:bg-[#FF6B4A]/20 border border-[#FF6B4A]/30 dark:border-[#FF6B4A]/30 rounded-xl transition-all cursor-pointer inline-flex items-center" title="Modificar Registro">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                </button>
                            @endif
                            @if(auth()->user()->hasPermission('delete-categories'))
                                <button @click="openDeleteModal({{ $category->id }})" 
                                        class="p-2 text-rose-500 dark:text-rose-400 bg-rose-500/10 hover:bg-rose-500/20 border border-rose-500/30 dark:border-rose-500/30 rounded-xl transition-all cursor-pointer inline-flex items-center" title="Remover del Servidor">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-10 text-center text-slate-400 dark:text-slate-600 font-bold uppercase text-xs">No se encontraron clasificaciones registradas.</div>
            @endforelse
        </div>

        {{-- VISTA DESKTOP --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr>
                        <th class="table-th w-28">ID de Registro</th>
                        <th class="table-th w-64">Nombre / Etiqueta</th>
                        <th class="table-th">Descripción Operativa</th>
                        <th class="table-th text-right w-36">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                        <tr class="table-tr">
                            <td class="table-td font-bold text-slate-400 dark:text-slate-500 tabular-nums">#{{ $loop->iteration }}</td>
                            <td class="table-td">
                                <span class="font-black text-slate-900 dark:text-white px-3 py-1.5 bg-slate-100 dark:bg-slate-800/80 border border-slate-200/80 dark:border-slate-800 rounded-xl text-[12px] tracking-wide inline-block">
                                    {{ $category->name }}
                                </span>
                            </td>
                            <td class="table-td max-w-md font-medium truncate">
                                {{ $category->description ?? 'Sin descripción' }}
                            </td>
                            <td class="table-td text-right whitespace-nowrap">
                                <div class="inline-flex items-center justify-end gap-3">
                                    @if(auth()->user()->hasPermission('edit-categories'))
                                    <button @click="openEditModal({{ $category->id }}, '{{ addslashes($category->name) }}', '{{ addslashes($category->description) }}')" 
                                            class="p-2 text-[#FF6B4A] dark:text-[#FF8A65] bg-[#FF6B4A]/10 hover:bg-[#FF6B4A]/20 border border-[#FF6B4A]/30 dark:border-[#FF6B4A]/30 rounded-xl transition-all cursor-pointer inline-flex items-center" title="Modificar Registro">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    </button>
                                @endif
                                    @if(auth()->user()->hasPermission('delete-categories'))
                                        <button @click="openDeleteModal({{ $category->id }})" 
                                                class="p-2 text-rose-500 dark:text-rose-400 bg-rose-500/10 hover:bg-rose-500/20 border border-rose-500/30 dark:border-rose-500/30 rounded-xl transition-all cursor-pointer inline-flex items-center" title="Remover del Servidor">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="p-14 text-center text-slate-400 dark:text-slate-600 font-bold uppercase text-xs">No se encontraron clasificaciones registradas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- MODALES --}}
    <x-modal name="create" title="Registrar Nueva Categoría">
        <form action="{{ route('categories.store') }}" method="POST" class="relative z-10 flex flex-col bg-transparent">
            @csrf
            <div class="p-5 sm:p-7 space-y-4">
                <div class="space-y-1.5">
                    <label class="form-label">Nombre Único</label>
                    <input type="text" name="name" value="{{ old('name') }}" class="form-input" placeholder="Ej. Hardware" required>
                </div>
                <div class="space-y-1.5">
                    <label class="form-label">Descripción del Catálogo</label>
                    <textarea name="description" rows="3" class="form-input" placeholder="Especifica qué tipo de artículos entran aquí..."></textarea>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 p-5 sm:px-7 border-t border-slate-100 dark:border-white/5 bg-transparent shrink-0">
                <button type="button" @click="closeModal('create')" class="btn-secondary">Cancelar</button>
                <button type="submit" class="bg-[#FF4500] hover:bg-[#E63E00] text-white font-bold rounded-2xl px-5 py-2.5 shadow-md shadow-[#FF4500]/25 active:scale-95 transition text-xs cursor-pointer">Guardar Categoría</button>
            </div>
        </form>
    </x-modal>

    <x-modal name="edit" title="Modificar Categoría">
        <form :action="'{{ route('categories.index') }}/' + formEdit.id" method="POST" class="relative z-10 flex flex-col bg-transparent">
            @csrf
            @method('PUT')
            <div class="p-5 sm:p-7 space-y-4">
                <div class="space-y-1.5">
                    <label class="form-label">Nombre Actualizado</label>
                    <input type="text" x-model="formEdit.name" name="name" class="form-input" required>
                </div>
                <div class="space-y-1.5">
                    <label class="form-label">Nueva Descripción Funcional</label>
                    <textarea x-model="formEdit.description" name="description" rows="3" class="form-input"></textarea>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 p-5 sm:px-7 border-t border-slate-100 dark:border-white/5 bg-transparent shrink-0">
                <button type="button" @click="closeModal('edit')" class="btn-secondary">Cancelar</button>
                <button type="submit" class="bg-[#FF4500] hover:bg-[#E63E00] text-white font-bold rounded-2xl px-5 py-2.5 shadow-md shadow-[#FF4500]/25 active:scale-95 transition text-xs cursor-pointer">Actualizar Datos</button>
            </div>
        </form>
    </x-modal>

    <x-modal name="delete" title="¿Confirmar Eliminación?" maxWidth="max-w-sm" dotColor="bg-rose-500">
        <div class="p-6 text-center">
            <div class="w-12 h-12 rounded-2xl bg-rose-500/10 text-rose-600 dark:text-rose-400 flex items-center justify-center mx-auto mb-4 border border-rose-500/20">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <p class="text-[11px] text-slate-500 dark:text-slate-400 mb-6 font-semibold px-2 leading-relaxed">
                Esta categoría se borrará de forma definitiva. Por favor, asegúrate de que no tenga productos vinculados antes de continuar.
            </p>
            <form :action="'{{ route('categories.index') }}/' + formDelete.id" method="POST" class="flex gap-3">
                @csrf
                @method('DELETE')
                <button type="button" @click="closeModal('delete')" class="btn-secondary w-1/2 justify-center">Abortar</button>
                <button type="submit" class="w-1/2 py-2.5 rounded-2xl bg-rose-600 hover:bg-rose-700 text-white font-black text-xs transition-all shadow-md active:scale-95 cursor-pointer">Eliminar</button>
            </form>
        </div>
    </x-modal>

</div>
</x-app-container>
@endsection

@push('scripts')
<script>
    window.laravelErrors = @json($errors->any());
</script>
<script src="{{ asset('js/components/category-management.js') }}"></script>
@endpush