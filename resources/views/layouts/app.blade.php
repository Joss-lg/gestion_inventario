<!DOCTYPE html>
<html lang="es" class="h-full overflow-x-hidden transition-colors duration-200">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SCGI - Control y Gestión de Inventarios')</title>
    
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">

    <script>
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="h-full font-sans antialiased text-slate-800 dark:text-slate-100 overflow-hidden bg-slate-100 dark:bg-[#0b0f19] transition-colors duration-200">

    {{-- Estado global con Alpine.js --}}
    <div x-data="{ sidebarOpen: false, sidebarCollapsed: false }" class="h-screen max-w-[100vw] flex w-full bg-slate-100 dark:bg-[#0b0f19] overflow-hidden">
        
        {{-- Overlay móvil --}}
        <div x-show="sidebarOpen" 
             @click="sidebarOpen = false" 
             class="fixed inset-0 z-40 bg-slate-900/40 dark:bg-slate-950/80 backdrop-blur-xs lg:hidden"
             style="display: none;"></div>

        {{-- BARRA LATERAL (SIDEBAR) --}}
        <aside :class="{
                    'translate-x-0': sidebarOpen, 
                    '-translate-x-full': !sidebarOpen,
                    'lg:w-72': !sidebarCollapsed,
                    'lg:w-20': sidebarCollapsed
                }" 
                class="fixed lg:static inset-y-0 left-0 z-50 w-72 bg-white dark:bg-[#0e1322] text-slate-600 dark:text-slate-300 flex-shrink-0 flex flex-col border-r border-slate-200 dark:border-slate-800/80 h-full -translate-x-full lg:translate-x-0 transition-all duration-300 ease-in-out">
            
            {{-- LOGO Y TITULO --}}
            <div class="h-20 flex items-center justify-between px-5 bg-slate-50/80 dark:bg-[#0b0f19]/50 border-b border-slate-200 dark:border-slate-800/80 flex-shrink-0">
                <div class="flex items-center gap-3 overflow-hidden">
                    <div class="w-10 h-10 rounded-2xl bg-indigo-600 text-white font-extrabold flex items-center justify-center text-xl shadow-md shadow-indigo-600/20 flex-shrink-0">
                        S
                    </div>
                    <div x-show="!sidebarCollapsed" x-transition.opacity class="truncate">
                        <span class="font-bold text-slate-900 dark:text-white text-base tracking-wider block whitespace-nowrap">SCGI Negocios</span>
                        <span class="text-xs text-slate-500 dark:text-slate-400 block whitespace-nowrap">Control de Inventarios</span>
                    </div>
                </div>
                <button @click="sidebarOpen = false" class="lg:hidden text-slate-400 hover:text-slate-700 dark:hover:text-white p-1 rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- NAVEGACIÓN Y SECCIONES --}}
            <nav class="flex-1 px-3 py-4 space-y-6 overflow-y-auto overflow-x-hidden">
                
                {{-- SECCIÓN 1: PRINCIPAL --}}
                <div class="space-y-1">
                    <p x-show="!sidebarCollapsed" x-transition.opacity class="px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-2 truncate">
                        Principal
                    </p>
                    
                    @can('manage-dashboard')
                        <a href="{{ route('dashboard') }}" 
                           title="Dashboard"
                           class="flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl text-sm font-semibold whitespace-nowrap transition-colors {{ request()->routeIs('dashboard') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/20' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white' }}">
                            <svg class="w-5 h-5 flex-shrink-0 {{ request()->routeIs('dashboard') ? 'text-white' : 'text-indigo-500 dark:text-indigo-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                            <span x-show="!sidebarCollapsed" x-transition.opacity class="truncate">Dashboard</span>
                        </a>
                    @endcan
                </div>

                {{-- SECCIÓN 2: CATÁLOGOS E INVENTARIO --}}
                <div class="space-y-1">
                    <p x-show="!sidebarCollapsed" x-transition.opacity class="px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-2 truncate">
                        Inventario
                    </p>

                    @can('manage-products')
                        <a href="{{ route('catalogo.index') }}" 
                           title="Catálogo"
                           class="flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl text-sm font-semibold whitespace-nowrap transition-colors {{ request()->routeIs('catalogo.*') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/20' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white' }}">
                            <svg class="w-5 h-5 flex-shrink-0 {{ request()->routeIs('catalogo.*') ? 'text-white' : 'text-indigo-500 dark:text-indigo-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                            </svg>
                            <span x-show="!sidebarCollapsed" x-transition.opacity class="truncate">Catálogo</span>
                        </a>
                    @endcan

                    @can('manage-categories')
                        <a href="{{ route('categories.index') }}" 
                           title="Categorías"
                           class="flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl text-sm font-semibold whitespace-nowrap transition-colors {{ request()->routeIs('categories.*') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/20' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white' }}">
                            <svg class="w-5 h-5 flex-shrink-0 {{ request()->routeIs('categories.*') ? 'text-white' : 'text-indigo-500 dark:text-indigo-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                            <span x-show="!sidebarCollapsed" x-transition.opacity class="truncate">Categorías</span>
                        </a>
                    @endcan

                    @can('manage-products')
                        <a href="{{ route('products.index') }}" 
                           title="Productos"
                           class="flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl text-sm font-semibold whitespace-nowrap transition-colors {{ request()->routeIs('products.*') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/20' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white' }}">
                            <svg class="w-5 h-5 flex-shrink-0 {{ request()->routeIs('products.*') ? 'text-white' : 'text-indigo-500 dark:text-indigo-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7v10l8 4"/></svg>
                            <span x-show="!sidebarCollapsed" x-transition.opacity class="truncate">Productos</span>
                        </a>
                    @endcan

                    @can('register-movements')
                        <a href="{{ route('stock.index') }}" 
                           title="Movimientos de Stock"
                           class="flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl text-sm font-semibold whitespace-nowrap transition-colors {{ request()->routeIs('stock.*') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/20' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white' }}">
                            <svg class="w-5 h-5 flex-shrink-0 {{ request()->routeIs('stock.*') ? 'text-white' : 'text-indigo-500 dark:text-indigo-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                            <span x-show="!sidebarCollapsed" x-transition.opacity class="truncate">Movimientos Stock</span>
                        </a>
                    @endcan
                </div>

                {{-- SECCIÓN 3: VENTAS Y CAJA --}}
                <div class="space-y-1">
                    <p x-show="!sidebarCollapsed" x-transition.opacity class="px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-2 truncate">
                        Ventas & Caja
                    </p>

                        @can('manage-products')
                            <a href="{{ route('caja.pos') }}" 
                            title="Punto de Venta (POS)"
                            class="flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl text-sm font-semibold whitespace-nowrap transition-colors {{ request()->routeIs('caja.pos*') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/20' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white' }}">
                                <svg class="w-5 h-5 flex-shrink-0 {{ request()->routeIs('caja.pos*') ? 'text-white' : 'text-indigo-500 dark:text-indigo-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                                <span x-show="!sidebarCollapsed" x-transition.opacity class="truncate">Punto de Venta</span>
                            </a>
                        @endcan

                    <a href="{{ route('caja.index') }}" 
                       title="Control de Caja"
                       class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-semibold whitespace-nowrap transition-colors {{ request()->routeIs('caja.index') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/20' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white' }}">
                        <div class="flex items-center gap-3.5">
                            <svg class="w-5 h-5 flex-shrink-0 {{ request()->routeIs('caja.index') ? 'text-white' : 'text-emerald-500 dark:text-emerald-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            <span x-show="!sidebarCollapsed" x-transition.opacity class="truncate">Control de Caja</span>
                        </div>
                        <template x-if="!sidebarCollapsed">
                            <div>
                                @if(auth()->user()->cajaActiva())
                                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 block" title="Caja Abierta"></span>
                                @else
                                    <span class="text-[10px] bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-300 px-2 py-0.5 rounded-md border border-rose-200 dark:border-rose-500/30">Cerrada</span>
                                @endif
                            </div>
                        </template>
                    </a>

                    @can('manage-users')
                        <a href="{{ route('caja.historial') }}" 
                           title="Historial de Turnos"
                           class="flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl text-sm font-semibold whitespace-nowrap transition-colors {{ request()->routeIs('caja.historial*') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/20' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white' }}">
                            <svg class="w-5 h-5 flex-shrink-0 {{ request()->routeIs('caja.historial*') ? 'text-white' : 'text-amber-500 dark:text-amber-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span x-show="!sidebarCollapsed" x-transition.opacity class="truncate">Historial Turnos</span>
                        </a>
                    @endcan
                </div>

                {{-- SECCIÓN 4: ADMINISTRACIÓN --}}
                @can('manage-users')
                    <div class="space-y-1">
                        <p x-show="!sidebarCollapsed" x-transition.opacity class="px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-2 truncate">
                            Ajustes
                        </p>

                        <a href="{{ route('users.index') }}" 
                           title="Gestión de Usuarios"
                           class="flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl text-sm font-semibold whitespace-nowrap transition-colors {{ request()->routeIs('users.*') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/20' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white' }}">
                            <svg class="w-5 h-5 flex-shrink-0 {{ request()->routeIs('users.*') ? 'text-white' : 'text-indigo-500 dark:text-indigo-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                            <span x-show="!sidebarCollapsed" x-transition.opacity class="truncate">Gestión Usuarios</span>
                        </a>
                    </div>
                @endcan

            </nav>

            {{-- FOOTER CON USUARIO --}}
            <div class="p-3.5 bg-slate-50 dark:bg-[#0b0f19]/60 border-t border-slate-200 dark:border-slate-800/80 flex items-center justify-between flex-shrink-0">
                <div class="flex items-center gap-3 overflow-hidden">
                    <div class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-slate-800/80 border border-indigo-100 dark:border-slate-700/80 flex items-center justify-center font-bold text-indigo-600 dark:text-white text-xs flex-shrink-0">
                        {{ substr(auth()->user()->name ?? 'U', 0, 2) }}
                    </div>
                    <div x-show="!sidebarCollapsed" x-transition.opacity class="truncate">
                        <p class="text-xs font-bold text-slate-800 dark:text-white truncate">{{ auth()->user()->name ?? 'Usuario SCGI' }}</p>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate">{{ auth()->user()->email ?? 'admin@scgi.local' }}</p>
                    </div>
                </div>
            </div>
        </aside>

        {{-- CONTENIDO PRINCIPAL --}}
        <div class="flex-1 flex flex-col min-w-0 h-full">
            <header class="h-20 bg-white dark:bg-[#0b0f19] border-b border-slate-200 dark:border-slate-800/80 px-4 md:px-8 flex items-center justify-between shadow-xs z-10 w-full flex-shrink-0 transition-colors duration-200">
                
                <div class="flex items-center gap-3">
                    <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white p-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>

                    <button @click="sidebarCollapsed = !sidebarCollapsed" class="hidden lg:flex items-center justify-center p-2 rounded-xl text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h12M4 18h16"/></svg>
                    </button>

                    <h1 class="text-base md:text-lg font-extrabold text-slate-800 dark:text-white tracking-tight truncate">
                        @yield('header_title', 'Panel General')
                    </h1>
                </div>

                <div class="flex items-center gap-2 md:gap-4">
                    <x-theme-toggle />
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-2 px-3 md:px-4 py-2.5 text-xs font-bold text-rose-600 bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/30 dark:hover:bg-rose-950/60 dark:text-rose-400 rounded-xl cursor-pointer border border-rose-100 dark:border-rose-900/40">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            <span class="hidden sm:inline">Cerrar Sesión</span>
                        </button>
                    </form>
                </div>
            </header>

            <main class="flex-1 p-4 md:p-8 overflow-y-auto w-full bg-slate-100 dark:bg-[#0b0f19] transition-colors duration-200">
                <div class="w-full max-w-7xl mx-auto">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    <x-toast-alerts />
    @stack('scripts')
</body>
</html>