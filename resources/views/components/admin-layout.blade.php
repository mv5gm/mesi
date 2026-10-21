<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Mesi' }}</title>
    <link rel="icon" type="image/png" sizes="64x64" href="{{ asset('img/favicon-64.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('img/apple-touch-icon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @php
        $configOpen = request()->routeIs('categorias.*', 'monedas.*', 'almacenes.*', 'metodos-pago.*');
    @endphp
</head>
<body class="min-h-screen bg-gray-100 font-sans antialiased text-gray-900">
    <aside id="sidebar" class="fixed inset-y-0 left-0 z-40 flex w-64 -translate-x-full flex-col bg-gray-900 text-gray-100 transition-transform duration-200 md:translate-x-0">
        <div class="flex h-16 items-center justify-between gap-3 px-6">
            <img
                src="{{ asset('img/mesi-logo-sidebar.png') }}"
                alt="Mesi"
                class="h-9 w-auto object-contain"
            >
            <button
                type="button"
                onclick="toggleSidebar()"
                class="rounded-lg p-1 text-gray-400 hover:text-white md:hidden"
                aria-label="Cerrar menú"
            >
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <nav class="mt-2 flex-1 space-y-1 px-3">
            <a
                href="{{ route('dashboard') }}"
                class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('dashboard') ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}"
            >
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                Inicio
            </a>

            @can('gestionar productos')
                <a
                    href="{{ route('productos.index') }}"
                    class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('productos.*') ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}"
                >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                    Productos
                </a>
            @endcan

            @can('gestionar existencias')
                <a
                    href="{{ route('existencias.index') }}"
                    class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('existencias.*') ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}"
                >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                    Existencias
                </a>
            @endcan

            @can('gestionar proveedores')
                <a
                    href="{{ route('proveedores.index') }}"
                    class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('proveedores.*') ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}"
                >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                    Proveedores
                </a>
            @endcan

            @can('gestionar compras')
                <a
                    href="{{ route('compras.index') }}"
                    class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('compras.*') ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}"
                >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    Compras
                </a>
            @endcan

            @can('gestionar tasas cambio')
                <a
                    href="{{ route('tasas-cambio.index') }}"
                    class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('tasas-cambio.*') ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}"
                >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 16V4m0 0L3 8m4-4l4 4m6 4v12m0 0l4-4m-4 4l-4-4" />
                    </svg>
                    Tasas de cambio
                </a>
            @endcan

            @can('gestionar clientes')
                <a
                    href="{{ route('clientes.index') }}"
                    class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('clientes.*') ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}"
                >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    Clientes
                </a>
            @endcan

            @can('gestionar ventas')
                <a
                    href="{{ route('ventas.index') }}"
                    class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('ventas.*') ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}"
                >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h3m-4 4h16a2 2 0 002-2V8a2 2 0 00-2-2H7a2 2 0 00-2 2v9a2 2 0 00.5 1.3zM17 11h-2m-2 0h-2m-2 0H7" />
                    </svg>
                    Ventas
                </a>
            @endcan

            @can('gestionar usuarios')
                <a
                    href="{{ route('users.index') }}"
                    class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('users.*') ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}"
                >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-4.5-2.64M17 20H7m10 0v-2a3 3 0 00-1-2.23M7 20v-2a3 3 0 012-2.83m0 0V14a3 3 0 10-3-3m3 3v1m6-7a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Usuarios
                </a>
            @endcan

            @canany(['gestionar categorias', 'gestionar monedas', 'gestionar almacenes', 'gestionar metodos de pago'])
                <div>
                    <button
                        id="config-toggle"
                        type="button"
                        onclick="toggleConfigSubmenu()"
                        class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors {{ $configOpen ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}"
                        aria-expanded="{{ $configOpen ? 'true' : 'false' }}"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        </svg>
                        Configuraciones
                        <svg id="config-chevron" class="ml-auto h-4 w-4 transition-transform duration-200 {{ $configOpen ? 'rotate-180' : '' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div id="config-submenu" class="{{ $configOpen ? 'block' : 'hidden' }} ml-5 mt-1 space-y-1 border-l border-gray-700 pl-3">
                        @can('gestionar categorias')
                            <a
                                href="{{ route('categorias.index') }}"
                                class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('categorias.*') ? 'bg-gray-800 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}"
                            >
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 7v5a2 2 0 012-2h2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2V7m0 0a2 2 0 012-2h14a2 2 0 012 2m-16 0a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2h-2a2 2 0 00-2 2v0zm6 4h7m0 0l-3-3m3 3l-3 3" />
                                </svg>
                                Categorías
                            </a>
                        @endcan

                        @can('gestionar monedas')
                            <a
                                href="{{ route('monedas.index') }}"
                                class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('monedas.*') ? 'bg-gray-800 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}"
                            >
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Monedas
                            </a>
                        @endcan

                        @can('gestionar almacenes')
                            <a
                                href="{{ route('almacenes.index') }}"
                                class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('almacenes.*') ? 'bg-gray-800 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}"
                            >
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                                Almacenes
                            </a>
                        @endcan

                        @can('gestionar metodos de pago')
                            <a
                                href="{{ route('metodos-pago.index') }}"
                                class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('metodos-pago.*') ? 'bg-gray-800 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}"
                            >
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h2m-2 4h10a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2m0-4h.01" />
                                </svg>
                                Métodos de pago
                            </a>
                        @endcan
                    </div>
                </div>
            @endcanany
        </nav>

        <div class="border-t border-gray-700 p-4">
            <div class="mb-3 flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-indigo-600 text-sm font-bold">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div class="min-w-0">
                    <p class="truncate text-sm font-semibold">{{ auth()->user()->name }}</p>
                    <p class="truncate text-xs text-gray-400">{{ '@'.auth()->user()->username }}</p>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full rounded-lg bg-gray-800 px-3 py-2 text-xs font-medium text-gray-300 hover:bg-gray-700 hover:text-white">
                    Cerrar sesión
                </button>
            </form>
        </div>
    </aside>

    <div id="sidebar-backdrop" onclick="toggleSidebar()" class="fixed inset-0 z-30 hidden bg-black/50 md:hidden" aria-hidden="true"></div>

    <div class="flex min-h-screen flex-col md:pl-64">
        <header class="sticky top-0 z-20 flex h-16 items-center gap-4 bg-white px-4 shadow-sm md:px-6">
            <button
                type="button"
                onclick="toggleSidebar()"
                class="rounded-lg p-1 text-gray-500 hover:bg-gray-100 md:hidden"
                aria-label="Abrir menú"
            >
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
            <div class="ml-auto text-sm font-medium text-gray-600 md:hidden">
                {{ auth()->user()->name }}
            </div>
        </header>

        <main class="flex-1 px-4 py-6 md:px-8 md:py-8">
            {{ $slot }}
        </main>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const backdrop = document.getElementById('sidebar-backdrop');
            const isOpen = !sidebar.classList.contains('-translate-x-full');

            sidebar.classList.toggle('-translate-x-full');
            backdrop.classList.toggle('hidden');

            if (isOpen) {
                backdrop.classList.remove('hidden');
            } else {
                backdrop.classList.add('hidden');
            }
        }

        function toggleConfigSubmenu() {
            const submenu = document.getElementById('config-submenu');
            const chevron = document.getElementById('config-chevron');
            const button = document.getElementById('config-toggle');

            submenu.classList.toggle('hidden');
            chevron.classList.toggle('rotate-180');
            button.setAttribute('aria-expanded', submenu.classList.contains('hidden') ? 'false' : 'true');
        }
    </script>

    @stack('scripts')
</body>
</html>