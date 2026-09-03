<!DOCTYPE html>
<html lang="es" class="h-full overflow-x-hidden">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SCGI - Control y Gestión de Inventarios')</title>
    
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">

    {{-- 1. SCRIPT EN HEAD (Se ejecuta de forma síncrona ANTES de renderizar el HTML) --}}
    <script>
        // MODO OSCURO
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
        // ESTADO DEL MENÚ
        if (localStorage.getItem('sidebarCollapsed') === 'true') {
            document.documentElement.classList.add('sidebar-collapsed');
        }
    </script>

    {{-- 2. REGLAS CSS PURAS (Garantizan cero parpadeos o animaciones no deseadas) --}}
    <style>
        [x-cloak] { display: none !important; }

        /* MÓVIL (< 1024px): el sidebar SIEMPRE se muestra completo (con texto), nunca colapsado a solo iconos */
        @media (max-width: 1023.98px) {
            #sidebar-menu {
                width: 18rem !important; /* 288px exactos, igual que el modo expandido de escritorio */
            }
        }

        /* ESCRITORIO (>= 1024px): aquí sí aplica el colapso a solo iconos */
        @media (min-width: 1024px) {
            html.sidebar-collapsed #sidebar-menu {
                width: 5rem !important; /* 80px exactos */
            }
            html:not(.sidebar-collapsed) #sidebar-menu {
                width: 18rem !important; /* 288px exactos */
            }

            /* Oculta textos e información SOLO en escritorio colapsado */
            html.sidebar-collapsed .sidebar-label,
            html.sidebar-collapsed .hide-on-collapse {
                display: none !important;
            }

            html.sidebar-collapsed .center-on-collapse {
                justify-content: center !important;
                padding-left: 0.5rem !important;
                padding-right: 0.5rem !important;
            }
        }

        /* TRANSICIONES DESACTIVADAS POR DEFECTO */
        #sidebar-menu {
            transition: none !important;
        }

        /* Solo se activan las animaciones cuando la página ya cargó y el usuario hace CLIC */
        body.is-ready #sidebar-menu {
            transition: width 250ms cubic-bezier(0.4, 0, 0.2, 1) !important;
        }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="h-full font-sans antialiased text-slate-800 dark:text-slate-100 overflow-hidden bg-slate-100 dark:bg-[#0b0f19]">

    <div x-data="{ 
            sidebarOpen: false, 
            sidebarCollapsed: localStorage.getItem('sidebarCollapsed') === 'true'
         }" 
         x-init="
            // Habilitar animaciones 150ms después del render inicial
            requestAnimationFrame(() => {
                setTimeout(() => {
                    document.body.classList.add('is-ready');
                }, 150);
            });

            $watch('sidebarCollapsed', value => {
                localStorage.setItem('sidebarCollapsed', value);
                if (value) {
                    document.documentElement.classList.add('sidebar-collapsed');
                } else {
                    document.documentElement.classList.remove('sidebar-collapsed');
                }
            });
         "
         class="h-screen max-w-[100vw] flex w-full bg-slate-100 dark:bg-[#0b0f19] overflow-hidden">
        
        {{-- Overlay móvil --}}
        <div x-cloak
             x-show="sidebarOpen" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="sidebarOpen = false" 
             class="fixed inset-0 z-40 bg-slate-900/40 dark:bg-slate-950/80 backdrop-blur-sm lg:hidden"></div>

        {{-- BARRA LATERAL (SIDEBAR) --}}
        <aside id="sidebar-menu"
               :class="{
                    'translate-x-0': sidebarOpen, 
                    '-translate-x-full': !sidebarOpen
                }" 
                class="fixed lg:static inset-y-0 left-0 z-50 bg-white dark:bg-[#0e1322] text-slate-600 dark:text-slate-300 flex-shrink-0 flex flex-col border-r border-slate-200 dark:border-slate-800/80 h-full -translate-x-full lg:translate-x-0">
            
            {{-- ENCABEZADO LOGO --}}
            <div class="center-on-collapse h-20 flex items-center justify-between px-4 md:px-5 bg-slate-50/80 dark:bg-[#0b0f19]/50 border-b border-slate-200 dark:border-slate-800/80 flex-shrink-0">
                
                <div class="flex items-center gap-3 overflow-hidden">
                    <div @click="if (window.innerWidth >= 1024) sidebarCollapsed = !sidebarCollapsed"
                         class="w-10 h-10 rounded-2xl bg-[#FF4500] text-white font-extrabold flex items-center justify-center text-xl shadow-md shadow-[#FF4500]/20 flex-shrink-0 cursor-pointer hover:scale-105 transition-transform"
                         title="Presiona para desplegar/colapsar menú">
                        S
                    </div>

                    <div class="sidebar-label truncate">
                        <span class="font-bold text-slate-900 dark:text-white text-base tracking-wider block whitespace-nowrap">SCGI Negocios</span>
                        <span class="text-xs text-slate-500 dark:text-slate-400 block whitespace-nowrap">Control de Inventarios</span>
                    </div>
                </div>

                <button @click="sidebarCollapsed = true" 
                        class="sidebar-label hidden lg:flex items-center justify-center p-1.5 rounded-xl text-slate-400 hover:text-slate-700 dark:hover:text-white hover:bg-slate-200/60 dark:hover:bg-slate-800/60 transition-colors"
                        title="Colapsar menú">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>

                {{-- Botón móvil cambiado a 3 líneas --}}
                <button @click="sidebarOpen = false" class="lg:hidden text-slate-400 hover:text-slate-700 dark:hover:text-white p-1 rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
            </div>

            {{-- NAVEGACIÓN Y SECCIONES --}}
            <nav class="flex-1 px-3 py-4 space-y-6 overflow-y-auto overflow-x-hidden">
                @php($puedeVerModulos = auth()->user()->isAdmin() || auth()->user()->cajaActiva())
                
                {{-- SECCIÓN 1: PRINCIPAL --}}
                <div class="space-y-1">
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('dashboard') }}" 
                           title="Dashboard"
                           class="flex items-center gap-3.5 py-2.5 px-3.5 rounded-xl text-sm font-semibold whitespace-nowrap transition-colors {{ request()->routeIs('dashboard') ? 'bg-[#FF4500] text-white shadow-md shadow-[#FF4500]/20' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white' }}">
                            <svg class="w-5 h-5 flex-shrink-0 {{ request()->routeIs('dashboard') ? 'text-white' : 'text-[#FF6B4A] dark:text-[#FF8A65]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                            <span class="sidebar-label truncate">Dashboard</span>
                        </a>
                    @endif
                </div>

                {{-- SECCIÓN 2: INVENTARIO --}}
                @if($puedeVerModulos)
                <div class="space-y-1">
                    @if($puedeVerModulos)
                        <p class="sidebar-label px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-2 truncate">
                            Inventario
                        </p>
                    @endif

                    {{-- Catálogo y movimientos de stock dependen de sus permisos. --}}
                    @canany(['manage-products', 'register-movements', 'manage-categories'])
                        @if($puedeVerModulos)
                            <a href="{{ route('catalogo.index') }}" 
                               title="Catálogo"
                               class="flex items-center gap-3.5 py-2.5 px-3.5 rounded-xl text-sm font-semibold whitespace-nowrap transition-colors {{ request()->routeIs('catalogo.*') ? 'bg-[#FF4500] text-white shadow-md shadow-[#FF4500]/20' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white' }}">
                                <svg class="w-5 h-5 flex-shrink-0 {{ request()->routeIs('catalogo.*') ? 'text-white' : 'text-[#FF6B4A] dark:text-[#FF8A65]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                                </svg>
                                <span class="sidebar-label truncate">Catálogo</span>
                            </a>
                        @endif
                    @endcanany

                    @can('manage-categories')
                        <a href="{{ route('categories.index') }}" 
                           title="Categorías"
                           class="flex items-center gap-3.5 py-2.5 px-3.5 rounded-xl text-sm font-semibold whitespace-nowrap transition-colors {{ request()->routeIs('categories.*') ? 'bg-[#FF4500] text-white shadow-md shadow-[#FF4500]/20' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white' }}">
                            <svg class="w-5 h-5 flex-shrink-0 {{ request()->routeIs('categories.*') ? 'text-white' : 'text-[#FF6B4A] dark:text-[#FF8A65]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                            <span class="sidebar-label truncate">Categorías</span>
                        </a>
                    @endcan

                    @can('manage-products')
                        <a href="{{ route('products.index') }}" 
                           title="Productos"
                           class="flex items-center gap-3.5 py-2.5 px-3.5 rounded-xl text-sm font-semibold whitespace-nowrap transition-colors {{ request()->routeIs('products.*') ? 'bg-[#FF4500] text-white shadow-md shadow-[#FF4500]/20' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white' }}">
                            <svg class="w-5 h-5 flex-shrink-0 {{ request()->routeIs('products.*') ? 'text-white' : 'text-[#FF6B4A] dark:text-[#FF8A65]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7v10l8 4"/></svg>
                            <span class="sidebar-label truncate">Productos</span>
                        </a>
                    @endcan

                    @can('register-movements')
                        @if($puedeVerModulos)
                            <a href="{{ route('stock.index') }}" 
                               title="Movimientos de Stock"
                               class="flex items-center gap-3.5 py-2.5 px-3.5 rounded-xl text-sm font-semibold whitespace-nowrap transition-colors {{ request()->routeIs('stock.*') ? 'bg-[#FF4500] text-white shadow-md shadow-[#FF4500]/20' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white' }}">
                                <svg class="w-5 h-5 flex-shrink-0 {{ request()->routeIs('stock.*') ? 'text-white' : 'text-[#FF6B4A] dark:text-[#FF8A65]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                                <span class="sidebar-label truncate">Movimientos Stock</span>
                            </a>
                        @endif
                    @endcan
                </div>
                @endif

                {{-- SECCIÓN 3: VENTAS Y CAJA --}}
                <div class="space-y-1">
                    @if($puedeVerModulos)
                        <p class="sidebar-label px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-2 truncate">
                            Ventas & Caja
                        </p>
                    @endif

                    @if(auth()->user()->cajaActiva())
                        <a href="{{ route('caja.pos') }}" 
                           title="Punto de Venta (POS)"
                           class="flex items-center gap-3.5 py-2.5 px-3.5 rounded-xl text-sm font-semibold whitespace-nowrap transition-colors {{ request()->routeIs('caja.pos*') ? 'bg-[#FF4500] text-white shadow-md shadow-[#FF4500]/20' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white' }}">
                            <svg class="w-5 h-5 flex-shrink-0 {{ request()->routeIs('caja.pos*') ? 'text-white' : 'text-[#FF6B4A] dark:text-[#FF8A65]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            <span class="sidebar-label truncate">Punto de Venta</span>
                        </a>
                    @endif

                    <a href="{{ route('caja.index') }}" 
                       title="Control de Caja"
                       class="flex items-center justify-between py-2.5 px-3.5 rounded-xl text-sm font-semibold whitespace-nowrap transition-colors {{ request()->routeIs('caja.index') ? 'bg-[#FF4500] text-white shadow-md shadow-[#FF4500]/20' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white' }}">
                        <div class="flex items-center gap-3.5 relative">
                            <svg class="w-5 h-5 flex-shrink-0 {{ request()->routeIs('caja.index') ? 'text-white' : 'text-emerald-500 dark:text-emerald-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            <span class="sidebar-label truncate">Control de Caja</span>
                        </div>
                        
                        <div class="sidebar-label">
                            @if(auth()->user()->cajaActiva())
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 block" title="Caja Abierta"></span>
                            @else
                                <span class="text-[10px] bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-300 px-2 py-0.5 rounded-md border border-rose-200 dark:border-rose-500/30">Cerrada</span>
                            @endif
                        </div>
                    </a>

                    @if($puedeVerModulos)
                    @can('manage-users')
                        <a href="{{ route('caja.historial') }}" 
                           title="Historial de Turnos"
                           class="flex items-center gap-3.5 py-2.5 px-3.5 rounded-xl text-sm font-semibold whitespace-nowrap transition-colors {{ request()->routeIs('caja.historial*') ? 'bg-[#FF4500] text-white shadow-md shadow-[#FF4500]/20' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white' }}">
                            <svg class="w-5 h-5 flex-shrink-0 {{ request()->routeIs('caja.historial*') ? 'text-white' : 'text-amber-500 dark:text-amber-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span class="sidebar-label truncate">Historial Turnos</span>
                        </a>
                    @endcan
                    @endif
                </div>

                {{-- SECCIÓN 4: ADMINISTRACIÓN --}}
                @can('manage-users')
                    <div class="space-y-1">
                        <p class="sidebar-label px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-2 truncate">
                            Ajustes
                        </p>

                        <a href="{{ route('users.index') }}" 
                           title="Gestión de Usuarios"
                           class="flex items-center gap-3.5 py-2.5 px-3.5 rounded-xl text-sm font-semibold whitespace-nowrap transition-colors {{ request()->routeIs('users.*') ? 'bg-[#FF4500] text-white shadow-md shadow-[#FF4500]/20' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white' }}">
                            <svg class="w-5 h-5 flex-shrink-0 {{ request()->routeIs('users.*') ? 'text-white' : 'text-[#FF6B4A] dark:text-[#FF8A65]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                            <span class="sidebar-label truncate">Gestión Usuarios</span>
                        </a>
                    </div>
                @endcan

            </nav>

            {{-- FOOTER CON USUARIO --}}
            <div class="center-on-collapse p-3.5 bg-slate-50 dark:bg-[#0b0f19]/60 border-t border-slate-200 dark:border-slate-800/80 flex items-center justify-between flex-shrink-0">
                <div class="flex items-center gap-3 overflow-hidden">
                    <div class="w-9 h-9 rounded-xl bg-[#FFF1EC] dark:bg-slate-800/80 border border-[#FFD9CC] dark:border-slate-700/80 flex items-center justify-center font-bold text-[#FF4500] dark:text-white text-xs flex-shrink-0" title="{{ auth()->user()->name ?? 'Usuario SCGI' }}">
                        {{ substr(auth()->user()->name ?? 'U', 0, 2) }}
                    </div>
                    <div class="sidebar-label truncate">
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
                    <button @click="sidebarOpen = !sidebarOpen" aria-label="Abrir menú" class="lg:hidden text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white p-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>

                    <h1 class="text-base md:text-lg font-extrabold text-slate-800 dark:text-white tracking-tight truncate">
                        @yield('header_title', 'Panel General')
                    </h1>
                </div>

                <div class="flex items-center gap-2 md:gap-4">
                    <x-theme-toggle />
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-2 px-3 md:px-4 py-2.5 text-xs font-bold text-rose-600 bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/30 dark:hover:bg-rose-950/60 dark:text-rose-400 rounded-xl cursor-pointer border border-rose-100 dark:border-rose-900/40 transition-colors">
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