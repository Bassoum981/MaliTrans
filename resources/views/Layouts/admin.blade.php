<!DOCTYPE html>
<html lang="fr" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'MaliTrans — Administration')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        (() => {
            const theme = localStorage.getItem('malitrans-theme') || 'light';
            document.documentElement.classList.toggle('dark', theme === 'dark');
            document.documentElement.classList.toggle('light', theme === 'light');
        })();
    </script>
    <style>
        /* Unification stricte des couleurs de fond et de texte pour le mode clair et sombre */
        html.light body { background-color: #f8fafc; color: #0f172a; }
        html.dark body { background-color: #090d16; color: #f8fafc; }
    </style>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        slate: {
                            850: '#151c2c',
                            900: '#0f172a',
                            950: '#090d16',
                        }
                    }
                }
            }
        }
    </script>
    @stack('head')
</head>
<body class="min-h-screen font-sans antialiased" x-data="themeController()" x-init="initTheme()">
<div class="min-h-screen lg:flex">
    <!-- Overlay mobile -->
    <div x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 z-30 bg-black/50 backdrop-blur-sm lg:hidden" @click="sidebarOpen = false"></div>

    <!-- Sidebar Administration Unifiée -->
    <aside class="fixed inset-y-0 left-0 z-40 flex w-64 -translate-x-full flex-col border-r border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 px-5 py-6 transition-transform lg:translate-x-0">
        <div class="flex items-center justify-between">
            <a href="{{ route('admin.dashboard', [], false) }}" class="flex items-center gap-3">
                <span class="grid h-10 w-10 place-items-center rounded-xl bg-slate-900 dark:bg-white text-sm font-black text-white dark:text-slate-900">MT</span>
                <div>
                    <span class="block text-sm font-bold tracking-tight text-slate-900 dark:text-white">MaliTrans</span>
                    <span class="block text-[11px] text-slate-500">Administration</span>
                </div>
            </a>
            <button class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 lg:hidden" @click="sidebarOpen = false" aria-label="Fermer le menu">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="mt-8">
            <p class="mb-3 px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400">Pilotage</p>
            <nav class="space-y-1">
                @php
                    $adminLinks = [
                        [
                            'route' => 'admin.dashboard',
                            'label' => 'Vue d’ensemble',
                            'svg' => '<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v5a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM14 5a1 1 0 011-1h4a1 1 0 011 1v2a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 16a1 1 0 011-1h4a1 1 0 011 1v3a1 1 0 01-1 1H5a1 1 0 01-1-1v-3zM14 13a1 1 0 011-1h4a1 1 0 011 1v7a1 1 0 01-1 1h-4a1 1 0 01-1-1v-7z"/></svg>'
                        ],
                        [
                            'route' => 'admin.trajets',
                            'label' => 'Trajets',
                            'svg' => '<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>'
                        ],
                        [
                            'route' => 'admin.buses',
                            'label' => 'Flotte de bus',
                            'svg' => '<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>'
                        ],
                        [
                            'route' => 'admin.lignes',
                            'label' => 'Lignes & tarifs',
                            'svg' => '<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>'
                        ],
                        [
                            'route' => 'admin.users',
                            'label' => 'Personnel',
                            'svg' => '<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>'
                        ],
                    ];
                @endphp
                @foreach ($adminLinks as $link)
                    <a href="{{ route($link['route'], [], false) }}" class="{{ request()->routeIs($link['route']) ? 'bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-white font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/50 hover:text-slate-900 dark:hover:text-white' }} flex items-center gap-3 rounded-lg px-3 py-2.5 text-xs transition">
                        <span class="text-slate-500 dark:text-slate-400">{!! $link['svg'] !!}</span>
                        {{ $link['label'] }}
                    </a>
                @endforeach
            </nav>
        </div>

        <div class="mt-auto rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-850 p-3.5">
            <div class="flex items-center gap-3">
                <span class="grid h-9 w-9 place-items-center rounded-lg bg-slate-200 dark:bg-slate-700 text-xs font-bold text-slate-700 dark:text-slate-200">{{ str(request()->user()->name ?? 'A')->substr(0, 1)->upper() }}</span>
                <div class="min-w-0">
                    <p class="truncate text-xs font-semibold text-slate-900 dark:text-white">{{ request()->user()->name ?? 'Administrateur' }}</p>
                    <p class="text-[10px] text-slate-500">Administrateur</p>
                </div>
            </div>
            <a href="{{ route('logout', [], false) }}" class="mt-3 flex items-center justify-between rounded-lg border border-slate-200 dark:border-slate-700 px-3 py-1.5 text-[11px] font-medium text-slate-600 dark:text-slate-400 transition hover:border-red-300 hover:text-red-600">
                Fermer la session
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
            </a>
        </div>
    </aside>

    <!-- Contenu Principal -->
    <main class="min-w-0 flex-1 lg:pl-64">
        <header class="sticky top-0 z-20 flex h-16 items-center justify-between border-b border-slate-200 dark:border-slate-800 bg-white/80 dark:bg-slate-900/80 px-5 backdrop-blur-md sm:px-8">
            <div class="flex items-center gap-3">
                <button class="rounded-lg border border-slate-200 dark:border-slate-700 p-2 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 lg:hidden" @click="sidebarOpen = true" aria-label="Ouvrir le menu">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <div>
                    <h1 class="text-xs font-bold uppercase tracking-wider text-slate-500">MaliTrans — Administration</h1>
                    <p class="text-sm font-semibold text-slate-900 dark:text-white">@yield('page_label', 'Tableau de bord')</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <button type="button" @click="toggleTheme()" class="rounded-lg border border-slate-200 dark:border-slate-700 p-2 text-xs text-slate-600 dark:text-slate-300 transition hover:bg-slate-100 dark:hover:bg-slate-800" :aria-label="theme === 'dark' ? 'Activer le mode clair' : 'Activer le mode sombre'">
                    <span x-text="theme === 'dark' ? '☼ Mode clair' : '☾ Mode sombre'"></span>
                </button>
            </div>
        </header>

        <div class="p-5 sm:p-8 max-w-7xl mx-auto">
            @yield('content')
        </div>
    </main>
</div>
@stack('scripts')
<script>
    function themeController() {
        return {
            sidebarOpen: false,
            theme: localStorage.getItem('malitrans-theme') || 'light',
            initTheme() { this.applyTheme(); },
            toggleTheme() {
                this.theme = this.theme === 'dark' ? 'light' : 'dark';
                localStorage.setItem('malitrans-theme', this.theme);
                this.applyTheme();
            },
            applyTheme() {
                document.documentElement.classList.toggle('dark', this.theme === 'dark');
                document.documentElement.classList.toggle('light', this.theme === 'light');
            },
        };
    }
</script>
</body>
</html>
