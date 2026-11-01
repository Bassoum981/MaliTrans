<!DOCTYPE html>
<html lang="fr" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'MaliTrans — Guichet')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        paper: '#f6f7f2',
                        navy: '#10253d',
                        coral: '#f47d5d',
                        mint: '#b9e8c2'
                    }
                }
            }
        }
    </script>
</head>
<body class="min-h-screen bg-paper font-sans text-navy antialiased">
    <header class="border-b border-navy/10 bg-paper/95 backdrop-blur">
        <div class="mx-auto flex max-w-[1500px] items-center justify-between gap-5 px-5 py-4 sm:px-8">
            <a href="{{ route('guichet.dashboard') }}" class="flex items-center gap-3">
                <span class="grid h-11 w-11 place-items-center rounded-2xl bg-navy text-sm font-black text-white">MT</span>
                <span><span class="block text-sm font-black tracking-tight">MaliTrans</span><span class="block text-[11px] font-semibold text-navy/50">Guichet de vente</span></span>
            </a>
            <div class="flex items-center gap-3">
                <span class="hidden rounded-full bg-mint px-3 py-1.5 text-xs font-bold text-navy sm:inline-flex"><span class="mr-2 h-1.5 w-1.5 self-center rounded-full bg-emerald-600"></span>Caisse ouverte</span>
                <span class="hidden text-right sm:block"><span class="block text-xs font-bold">{{ request()->user()->name }}</span><span class="block text-[11px] text-navy/50">Guichetier</span></span>
                <a href="{{ route('logout') }}" class="rounded-xl border border-navy/15 px-3 py-2 text-xs font-bold transition hover:border-coral hover:text-coral">Quitter</a>
            </div>
        </div>
    </header>
    <main class="mx-auto max-w-[1500px] px-5 py-6 sm:px-8 sm:py-8">
        @yield('content')
    </main>
</body>
</html>
