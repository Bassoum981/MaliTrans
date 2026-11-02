<!DOCTYPE html>
<html lang="dark" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'MaliTrans — Guichet de vente')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <script>
        (() => {
            const theme = localStorage.getItem('malitrans-theme') || 'dark';
            document.documentElement.classList.toggle('dark', theme === 'dark');
            document.documentElement.classList.toggle('light', theme === 'light');
        })();
    </script>
    <style>
        /* Unification stricte des fonds et des textes */
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

<!-- Topbar Guichet Unifiée -->
<header class="sticky top-0 z-20 flex h-16 items-center justify-between border-b border-slate-200 dark:border-slate-800 bg-white/80 dark:bg-slate-900/80 px-5 backdrop-blur-md sm:px-8">
    <div class="mx-auto flex w-full max-w-[1500px] items-center justify-between">

        <!-- Logo & Titre -->
        <a href="{{ route('guichet.dashboard', [], false) }}" class="flex items-center gap-3">
            <span class="grid h-10 w-10 place-items-center rounded-xl bg-slate-900 dark:bg-white text-sm font-black text-white dark:text-slate-900">MT</span>
            <div>
                <span class="block text-sm font-bold tracking-tight text-slate-900 dark:text-white">MaliTrans</span>
                <span class="block text-[11px] text-slate-500">Guichet de vente & Émission</span>
            </div>
        </a>

        <!-- Actions & Profil -->
        <div class="flex items-center gap-4">
            <!-- Indicateur Caisse Ouverte -->
            <div class="hidden items-center gap-2 rounded-full border border-emerald-500/20 bg-emerald-500/10 px-3 py-1 text-xs font-semibold text-emerald-600 dark:text-emerald-400 sm:inline-flex">
                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                Caisse ouverte
            </div>

            <!-- Info Agent -->
            <div class="hidden text-right sm:block">
                <span class="block text-xs font-semibold text-slate-900 dark:text-white">{{ request()->user()->name ?? 'Guichetier' }}</span>
                <span class="block text-[10px] text-slate-500">Agent de guichet</span>
            </div>

            <!-- Bouton Bascule Thème -->
            <button type="button" @click="toggleTheme()" class="flex items-center gap-2 rounded-lg border border-slate-200 dark:border-slate-700 px-3 py-2 text-xs font-medium text-slate-600 dark:text-slate-300 transition hover:bg-slate-100 dark:hover:bg-slate-800" aria-label="Changer le thème">
                <svg x-show="theme === 'dark'" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                <svg x-show="theme === 'light'" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                <span class="hidden md:inline" x-text="theme === 'dark' ? 'Clair' : 'Sombre'"></span>
            </button>

            <!-- Bouton Quitter -->
            <a href="{{ route('logout', [], false) }}" class="flex items-center gap-1.5 rounded-lg border border-slate-200 dark:border-slate-700 px-3 py-2 text-xs font-medium text-slate-600 dark:text-slate-300 transition hover:border-red-300 hover:text-red-600">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                <span>Quitter</span>
            </a>
        </div>

    </div>
</header>

<!-- Contenu Principal -->
<main class="mx-auto max-w-[1500px] px-5 py-6 sm:px-8 sm:py-8">
    @yield('content')
</main>

@stack('scripts')
<script>
    function themeController() {
        return {
            theme: localStorage.getItem('malitrans-theme') || 'dark',
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
