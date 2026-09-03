@extends('layouts.app')

@section('title', 'SCGI - Productos')
@section('header_title', 'Productos')

@section('content')
<x-app-container>
<div class="max-w-7xl mx-auto space-y-4 sm:space-y-6" x-data="productManagement()">

    {{-- ENCABEZADO PRINCIPAL --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 pb-4 sm:pb-6 border-b border-slate-200/80 dark:border-slate-800/80">
        <div>
            <h1 class="page-title">Productos</h1>
            <p class="page-subtitle">Catálogo completo de productos e inventario global en tiempo real.</p>
        </div>
        
        <button @click="openCreateModal()" class="bg-[#FF4500] hover:bg-[#E63E00] text-white font-bold rounded-2xl px-5 py-2.5 shadow-md shadow-[#FF4500]/25 active:scale-95 transition w-full sm:w-auto uppercase tracking-wider text-xs cursor-pointer inline-flex items-center justify-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
            <span>Registrar Producto</span>
        </button>
    </div>

    {{-- TABLA / TARJETAS --}}
    <div class="table-container">
        <div class="px-6 py-5 bg-slate-50 dark:bg-slate-900/40 border-b border-slate-200/80 dark:border-slate-800/80">
            <h2 class="text-xs sm:text-sm font-black text-slate-800 dark:text-white uppercase tracking-wider">Existencias Activas</h2>
            <p class="text-[10px] sm:text-[11px] text-slate-500 dark:text-slate-400 font-bold mt-0.5">Listado maestro en tiempo real</p>
        </div>

        {{-- VISTA MÓVIL --}}
        <div class="block md:hidden divide-y divide-slate-100 dark:divide-slate-800/60">
            @forelse($products as $product)
            <div class="p-4 space-y-3 hover:bg-[#FFF1EC]/40 dark:hover:bg-[#FF6B4A]/5 transition-colors">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-400 dark:text-slate-500 tabular-nums">#{{ $product->id }}</span>
                    <span class="font-black px-3 py-1 bg-slate-100 dark:bg-slate-800/80 border border-slate-200/80 dark:border-slate-800 rounded-xl text-[10px] tracking-wide text-slate-800 dark:text-slate-200">
                        {{ $product->category->name ?? 'Sin Categoría' }}
                    </span>
                </div>

                <div class="flex items-center gap-3.5">
                    <div class="w-14 h-14 shrink-0 rounded-2xl overflow-hidden border border-slate-200/80 dark:border-slate-800/80 bg-slate-100 dark:bg-slate-900 flex items-center justify-center">
                        @if($product->image)
                            <img src="{{ route('products.image', $product->image) }}" class="w-full h-full object-cover" alt="{{ $product->name }}">
                        @else
                            <span class="text-slate-400 dark:text-slate-600 text-[9px] font-black tracking-wider uppercase">N/A</span>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="font-black text-slate-900 dark:text-white tracking-wide text-sm truncate">{{ $product->name }}</h3>
                        <p class="text-xs font-bold text-slate-500 dark:text-slate-400 mt-0.5">
                            SKU: <span class="uppercase text-slate-700 dark:text-slate-300 font-extrabold">{{ $product->sku ?? 'N/A' }}</span>
                        </p>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-2 border-t border-slate-100/60 dark:border-slate-800/40">
                    <div>
                        <div class="text-[10px] uppercase tracking-wider font-extrabold text-slate-400 dark:text-slate-500">Precio & Stock</div>
                        <div class="flex items-center gap-2 mt-0.5">
                            <span class="font-black text-[#F0552F] dark:text-[#FF8A65] text-sm">${{ number_format($product->price, 2) }}</span>
                            <span class="text-slate-300 dark:text-slate-700">•</span>
                            <span class="font-bold text-slate-700 dark:text-slate-300 text-xs">{{ $product->stock }} u.</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <button @click="openEditModal({{ $product->id }}, '{{ addslashes($product->name) }}', '{{ $product->category_id ?? '' }}', {{ $product->price }}, {{ $product->stock }}, '{{ $product->sku ?? '' }}')" 
                                class="w-9 h-9 rounded-xl bg-[#FF6B4A]/10 hover:bg-[#FF6B4A]/20 border border-[#FF6B4A]/20 text-[#F0552F] dark:text-[#FF8A65] flex items-center justify-center transition-all cursor-pointer shadow-xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </button>
                        <button @click="openDeleteModal({{ $product->id }})" 
                                class="w-9 h-9 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 border border-rose-500/20 text-rose-500 flex items-center justify-center transition-all cursor-pointer shadow-xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3.75h6a.75.75 0 01.75.75v.75h3.75a.75.75 0 010 1.5h-.588l-.83 12.451A2.25 2.25 0 0115.887 21H8.113a2.25 2.25 0 01-2.245-2.049L5.038 6.75H4.5a.75.75 0 010-1.5h3.75V4.5A.75.75 0 019 3.75zM9.75 9.75a.75.75 0 00-1.5 0v7.5a.75.75 0 001.5 0v-7.5zm3-.75a.75.75 0 01.75.75v7.5a.75.75 0 01-1.5 0v-7.5a.75.75 0 01.75-.75zm3.75.75a.75.75 0 00-1.5 0v7.5a.75.75 0 001.5 0v-7.5z"/></svg>
                        </button>
                    </div>
                </div>
            </div>
            @empty
            <div class="p-10 text-center text-slate-400 dark:text-slate-600 font-bold uppercase text-xs">No hay productos registrados.</div>
            @endforelse
        </div>

        {{-- VISTA ESCRITORIO --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr>
                        <th class="table-th w-28">ID</th>
                        <th class="table-th w-24">Imagen</th>
                        <th class="table-th">Producto</th>
                        <th class="table-th w-48">Categoría</th>
                        <th class="table-th w-32">Precio</th>
                        <th class="table-th w-28">Stock</th>
                        <th class="table-th text-right w-36">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                    <tr class="table-tr">
                        <td class="table-td font-bold text-slate-400 dark:text-slate-500 tabular-nums">#{{ $product->id }}</td>
                        <td class="table-td">
                            <div class="w-11 h-11 rounded-xl overflow-hidden border border-slate-200/80 dark:border-slate-800/80 bg-slate-100 dark:bg-slate-900 flex items-center justify-center">
                                @if($product->image)
                                    <img src="{{ route('products.image', $product->image) }}" class="w-full h-full object-cover" alt="{{ $product->name }}">
                                @else
                                    <span class="text-slate-400 dark:text-slate-600 text-[9px] font-black uppercase">N/A</span>
                                @endif
                            </div>
                        </td>
                        <td class="table-td font-black text-slate-900 dark:text-white tracking-wide text-xs sm:text-sm">{{ $product->name }}</td>
                        <td class="table-td">
                            <span class="font-black px-3 py-1.5 bg-slate-100 dark:bg-slate-800/80 border border-slate-200/80 dark:border-slate-800 rounded-xl text-[11px] tracking-wide inline-block text-slate-800 dark:text-slate-200">
                                {{ $product->category->name ?? 'Sin Categoría' }}
                            </span>
                        </td>
                        <td class="table-td font-black text-[#F0552F] dark:text-[#FF8A65] tabular-nums">${{ number_format($product->price, 2) }}</td>
                        <td class="table-td font-bold text-slate-700 dark:text-slate-400 tabular-nums">{{ $product->stock }} u.</td>
                        <td class="table-td text-right whitespace-nowrap">
                            <div class="inline-flex items-center gap-2">
                                <button @click="openEditModal({{ $product->id }}, '{{ addslashes($product->name) }}', '{{ $product->category_id ?? '' }}', {{ $product->price }}, {{ $product->stock }}, '{{ $product->sku ?? '' }}')" 
                                        class="w-9 h-9 rounded-xl bg-[#FF6B4A]/10 hover:bg-[#FF6B4A]/20 border border-[#FF6B4A]/20 text-[#F0552F] dark:text-[#FF8A65] inline-flex items-center justify-center transition-all cursor-pointer shadow-xs" title="Editar producto">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                <button @click="openDeleteModal({{ $product->id }})" 
                                        class="w-9 h-9 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 border border-rose-500/20 text-rose-500 inline-flex items-center justify-center transition-all cursor-pointer shadow-xs" title="Eliminar producto">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3.75h6a.75.75 0 01.75.75v.75h3.75a.75.75 0 010 1.5h-.588l-.83 12.451A2.25 2.25 0 0115.887 21H8.113a2.25 2.25 0 01-2.245-2.049L5.038 6.75H4.5a.75.75 0 010-1.5h3.75V4.5A.75.75 0 019 3.75zM9.75 9.75a.75.75 0 00-1.5 0v7.5a.75.75 0 001.5 0v-7.5zm3-.75a.75.75 0 01.75.75v7.5a.75.75 0 01-1.5 0v-7.5a.75.75 0 01.75-.75zm3.75.75a.75.75 0 00-1.5 0v7.5a.75.75 0 001.5 0v-7.5z"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="p-14 text-center text-slate-400 dark:text-slate-600 font-bold uppercase text-xs">No hay productos registrados.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ========================================================= --}}
    {{-- 1. MODAL CREAR --}}
    {{-- ========================================================= --}}
    <x-modal name="create" title="Crear Nuevo Producto" maxWidth="max-w-lg">
        <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data" class="relative z-10 flex flex-col flex-1 min-h-0 bg-transparent">
            @csrf
            <div class="p-5 sm:p-7 space-y-4 overflow-y-auto flex-1 min-h-0 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                <div class="space-y-1.5">
                    <label class="form-label">Nombre del Producto</label>
                    <input type="text" name="name" class="form-input" required>
                </div>

                <div class="space-y-1.5">
                    <label class="form-label">Categoría</label>
                    <select name="category_id" class="form-input cursor-pointer" required>
                        <option value="">Selecciona una categoría</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="space-y-1.5">
                        <label class="form-label">Precio Venta</label>
                        <input type="number" step="0.01" name="price" class="form-input" required>
                    </div>
                    <div class="space-y-1.5">
                        <label class="form-label">Stock Inicial</label>
                        <input type="number" name="stock" class="form-input" required>
                    </div>
                    <div class="space-y-1.5">
                        <label class="form-label">SKU</label>
                        <input type="text" name="sku" class="form-input uppercase" required>
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="form-label">Imagen (Opcional)</label>
                    <div onclick="document.getElementById('file-upload').click()" class="border-2 border-dashed border-slate-200 dark:border-slate-800 hover:border-[#FF6B4A] bg-slate-50 dark:bg-slate-900/50 rounded-2xl w-28 h-28 flex flex-col items-center justify-center cursor-pointer relative overflow-hidden transition-all">
                        <img id="image-preview" class="absolute inset-0 w-full h-full object-cover hidden z-10">
                        <div id="upload-prompt" class="text-center p-2">
                            <svg class="mx-auto h-6 w-6 text-[#FF6B4A] mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <p class="text-[9px] font-black uppercase text-[#F0552F] dark:text-[#FF8A65]">Subir Img</p>
                        </div>
                        <input id="file-upload" type="file" name="image" accept="image/*" class="hidden" @change="previewFile('create')">
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 p-5 sm:px-7 border-t border-slate-100 dark:border-white/5 bg-transparent shrink-0">
                <button type="button" @click="closeModal('create')" class="btn-secondary">Cancelar</button>
                <button type="submit" class="bg-[#FF4500] hover:bg-[#E63E00] text-white font-bold rounded-2xl px-5 py-2.5 shadow-md shadow-[#FF4500]/25 active:scale-95 transition text-xs cursor-pointer">Guardar Producto</button>
            </div>
        </form>
    </x-modal>

    {{-- ========================================================= --}}
    {{-- 2. MODAL EDITAR --}}
    {{-- ========================================================= --}}
    <x-modal name="edit" title="Modificar Producto Existente" maxWidth="max-w-lg">
        <form :action="'{{ route('products.index') }}/' + formEdit.id" method="POST" enctype="multipart/form-data" class="relative z-10 flex flex-col flex-1 min-h-0 bg-transparent">
            @csrf
            @method('PUT')
            <div class="p-5 sm:p-7 space-y-4 overflow-y-auto flex-1 min-h-0 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                <div class="space-y-1.5">
                    <label class="form-label">Nombre del Producto</label>
                    <input type="text" x-model="formEdit.name" name="name" class="form-input" required>
                </div>

                <div class="space-y-1.5">
                    <label class="form-label">Categoría</label>
                    <select x-model="formEdit.category_id" name="category_id" class="form-input cursor-pointer" required>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="space-y-1.5">
                        <label class="form-label">Precio Venta</label>
                        <input type="number" step="0.01" x-model="formEdit.price" name="price" class="form-input" required>
                    </div>
                    <div class="space-y-1.5">
                        <label class="form-label">Stock</label>
                        <input type="number" x-model="formEdit.stock" name="stock" class="form-input" required>
                    </div>
                    <div class="space-y-1.5">
                        <label class="form-label">SKU</label>
                        <input type="text" x-model="formEdit.sku" name="sku" class="form-input uppercase" required>
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="form-label">Reemplazar Imagen (Opcional)</label>
                    <div onclick="document.getElementById('file-upload-edit').click()" class="border-2 border-dashed border-slate-200 dark:border-slate-800 hover:border-[#FF6B4A] bg-slate-50 dark:bg-slate-900/50 rounded-2xl w-28 h-28 flex flex-col items-center justify-center cursor-pointer relative overflow-hidden transition-all">
                        <img id="image-preview-edit" class="absolute inset-0 w-full h-full object-cover hidden z-10">
                        <div id="upload-prompt-edit" class="text-center p-2">
                            <svg class="mx-auto h-6 w-6 text-[#FF6B4A] mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <p class="text-[9px] font-black uppercase text-[#F0552F] dark:text-[#FF8A65]">Nueva Img</p>
                        </div>
                        <input id="file-upload-edit" type="file" name="image" accept="image/*" class="hidden" @change="previewFile('edit')">
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 p-5 sm:px-7 border-t border-slate-100 dark:border-white/5 bg-transparent shrink-0">
                <button type="button" @click="closeModal('edit')" class="btn-secondary">Cancelar</button>
                <button type="submit" class="bg-[#FF4500] hover:bg-[#E63E00] text-white font-bold rounded-2xl px-5 py-2.5 shadow-md shadow-[#FF4500]/25 active:scale-95 transition text-xs cursor-pointer">Actualizar Producto</button>
            </div>
        </form>
    </x-modal>

    {{-- ========================================================= --}}
    {{-- 3. MODAL ELIMINAR --}}
    {{-- ========================================================= --}}
    <x-modal name="delete" title="¿Confirmar Destrucción?" maxWidth="max-w-sm" dotColor="bg-rose-500">
        <div class="p-6 text-center">
            <div class="w-12 h-12 rounded-2xl bg-rose-500/10 text-rose-600 dark:text-rose-400 flex items-center justify-center mx-auto mb-4 border border-rose-500/20">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <p class="text-[11px] text-slate-500 dark:text-slate-400 mb-6 font-semibold px-2 leading-relaxed">
               ¿Estás seguro de eliminar este producto? Se borrará de forma definitiva de tu inventario.
            </p>
            <form :action="'{{ route('products.index') }}/' + formDelete.id" method="POST" class="flex gap-3">
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
<script src="{{ asset('js/components/product-management.js') }}"></script>
@endpush