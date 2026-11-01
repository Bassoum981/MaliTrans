<!DOCTYPE html>
<html lang="fr" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'MaliTrans — Administration')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        ink: '#07111f',
                        panel: '#0d1a2b',
                        line: '#1d3046',
                        cyan: '#56d9e8',
                        lime: '#b8e986'
                    },
                    boxShadow: {
                        panel: '0 20px 60px rgba(2, 12, 27, .35)'
                    }
                }
            }
        }
    </script>
    @stack('head')
</head>
<body class="min-h-screen bg-ink font-sans text-slate-100 antialiased" x-data="{ sidebarOpen: false }">
    <div class="min-h-screen lg:flex">
        <div x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 z-30 bg-ink/70 backdrop-blur-sm lg:hidden" @click="sidebarOpen = false"></div>

        <aside
            class="fixed inset-y-0 left-0 z-40 flex w-72 -translate-x-full flex-col border-r border-line bg-panel px-5 py-6 transition-transform lg:static lg:translate-x-0"
            :class="{ 'translate-x-0': sidebarOpen }"
        >
            <div class="flex items-center justify-between">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
                    <span class="grid h-11 w-11 place-items-center rounded-2xl bg-cyan text-lg font-black text-ink shadow-lg shadow-cyan/20">MT</span>
                    <span>
                        <span class="block text-sm font-bold tracking-tight text-white">MaliTrans</span>
                        <span class="block text-[11px] font-medium text-slate-500">Centre de contrôle</span>
                    </span>
                </a>
                <button class="rounded-lg p-2 text-slate-500 hover:bg-white/5 hover:text-white lg:hidden" @click="sidebarOpen = false" aria-label="Fermer le menu">✕</button>
            </div>

            <div class="mt-10">
                <p class="mb-3 px-3 text-[10px] font-bold uppercase tracking-[.2em] text-slate-500">Pilotage</p>
                <nav class="space-y-1.5">
                    @php
                        $adminLinks = [
                            ['route' => 'admin.dashboard', 'label' => 'Vue d’ensemble', 'icon' => '⌂'],
                            ['route' => 'admin.trajets', 'label' => 'Trajets', 'icon' => '↗'],
                            ['route' => 'admin.buses', 'label' => 'Flotte de bus', 'icon' => '▣'],
                            ['route' => 'admin.lignes', 'label' => 'Lignes & tarifs', 'icon' => '⌁'],
                            ['route' => 'admin.users', 'label' => 'Personnel', 'icon' => '◎'],
                        ];
                    @endphp
                    @foreach ($adminLinks as $link)
                        <a
                            href="{{ route($link['route']) }}"
                            class="{{ request()->routeIs($link['route']) ? 'bg-cyan/10 text-cyan ring-1 ring-inset ring-cyan/20' : 'text-slate-400 hover:bg-white/[.04] hover:text-white' }} group flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold transition"
                        >
                            <span class="{{ request()->routeIs($link['route']) ? 'bg-cyan text-ink' : 'bg-white/5 text-slate-500 group-hover:text-white' }} grid h-8 w-8 place-items-center rounded-lg text-base transition">{{ $link['icon'] }}</span>
                            {{ $link['label'] }}
                        </a>
                    @endforeach
                </nav>
            </div>

            <div class="mt-auto rounded-2xl border border-line bg-ink/50 p-4">
                <div class="flex items-center gap-3">
                    <span class="grid h-10 w-10 place-items-center rounded-full bg-lime/15 text-sm font-bold text-lime">{{ str(request()->user()->name)->substr(0, 1)->upper() }}</span>
                    <div class="min-w-0">
                        <p class="truncate text-sm font-semibold text-white">{{ request()->user()->name }}</p>
                        <p class="text-xs text-slate-500">Administrateur</p>
                    </div>
                </div>
                <a href="{{ route('logout') }}" class="mt-4 flex items-center justify-between rounded-xl border border-line px-3 py-2 text-xs font-semibold text-slate-400 transition hover:border-red-400/40 hover:text-red-300">
                    Fermer la session <span>↗</span>
                </a>
            </div>
        </aside>

        <main class="min-w-0 flex-1">
            <header class="sticky top-0 z-20 flex h-20 items-center justify-between border-b border-line/80 bg-ink/85 px-5 backdrop-blur-xl sm:px-8">
                <div class="flex items-center gap-3">
                    <button class="grid h-10 w-10 place-items-center rounded-xl border border-line text-slate-300 hover:bg-white/5 lg:hidden" @click="sidebarOpen = true" aria-label="Ouvrir le menu">☰</button>
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-[.22em] text-cyan/70">Opérations MaliTrans</p>
                        <p class="mt-1 text-sm font-semibold text-white">@yield('page_label', 'Administration')</p>
                    </div>
                </div>
                <div class="hidden items-center gap-3 sm:flex">
                    <span class="inline-flex items-center gap-2 rounded-full border border-lime/20 bg-lime/10 px-3 py-1.5 text-xs font-semibold text-lime"><span class="h-1.5 w-1.5 rounded-full bg-lime"></span> Système opérationnel</span>
                </div>
            </header>

            <div class="p-5 sm:p-8">
                @yield('content')
            </div>
        </main>
    </div>
    @stack('scripts')
</body>
</html>
