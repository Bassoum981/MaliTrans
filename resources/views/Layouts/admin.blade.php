<!DOCTYPE html>
<html lang="fr" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MaliTrans — Administration</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        slate: {
                            950: '#020617',
                            900: '#0F172A',
                            850: '#151E2E',
                            800: '#1E293B',
                            700: '#334155',
                            600: '#475569'
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-slate-950 text-slate-100 font-sans antialiased min-h-screen flex">

<!-- Sidebar Minimaliste & Sombre -->
<aside class="w-64 bg-slate-900 border-r border-slate-800/80 flex flex-col justify-between p-4 shrink-0">
    <div>
        <!-- Logo / Brand -->
        <div class="flex items-center space-x-3 px-3 py-2 mb-6">
            <div class="w-9 h-9 bg-slate-800 border border-slate-700 rounded-lg flex items-center justify-center font-semibold text-sm text-slate-100 tracking-wider">
                MT
            </div>
            <div>
                <h1 class="font-semibold text-sm text-slate-100 tracking-wide">MaliTrans</h1>
                <span class="text-[11px] text-slate-500 font-medium">Administration</span>
            </div>
        </div>

        <!-- Navigation Links -->
        <nav class="space-y-1">
            <!-- Dashboard -->
            <a href="{{ route('admin.dashboard') }}"
               class="flex items-center space-x-3 px-3 py-2 rounded-md text-xs font-medium transition-colors group {{ request()->routeIs('admin.dashboard') ? 'bg-slate-800/90 text-white font-semibold border-l-2 border-slate-400' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-850' }}">
                <svg class="w-4 h-4 transition-colors {{ request()->routeIs('admin.dashboard') ? 'text-slate-200' : 'text-slate-500 group-hover:text-slate-300' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                </svg>
                <span>Dashboard</span>
            </a>

            <!-- Flotte de Bus -->
            <a href="{{ route('admin.buses') }}"
               class="flex items-center space-x-3 px-3 py-2 rounded-md text-xs font-medium transition-colors group {{ request()->routeIs('admin.buses') ? 'bg-slate-800/90 text-white font-semibold border-l-2 border-slate-400' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-850' }}">
                <svg class="w-4 h-4 transition-colors {{ request()->routeIs('admin.buses') ? 'text-slate-200' : 'text-slate-500 group-hover:text-slate-300' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                </svg>
                <span>Flotte de Bus</span>
            </a>

            <!-- Lignes -->
            <a href="{{ route('admin.lignes') }}"
               class="flex items-center space-x-3 px-3 py-2 rounded-md text-xs font-medium transition-colors group {{ request()->routeIs('admin.lignes') ? 'bg-slate-800/90 text-white font-semibold border-l-2 border-slate-400' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-850' }}">
                <svg class="w-4 h-4 transition-colors {{ request()->routeIs('admin.lignes') ? 'text-slate-200' : 'text-slate-500 group-hover:text-slate-300' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503-11.485l4.775 2.865a.75.75 0 01.372.64v8.19a.75.75 0 01-.372.64l-4.775 2.865a.75.75 0 01-.756 0l-5.006-3.004-5.006 3.004a.75.75 0 01-.756 0L1.878 16.5a.75.75 0 01-.372-.64v-8.19a.75.75 0 01.372-.64l4.775-2.865a.75.75 0 01.756 0l5.006 3.004 5.006-3.004a.75.75 0 01.756 0z" />
                </svg>
                <span>Lignes Interurbaines</span>
            </a>

            <!-- Planification Trajets -->
            <a href="{{ route('admin.trajets') }}"
               class="flex items-center space-x-3 px-3 py-2 rounded-md text-xs font-medium transition-colors group {{ request()->routeIs('admin.trajets') ? 'bg-slate-800/90 text-white font-semibold border-l-2 border-slate-400' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-850' }}">
                <svg class="w-4 h-4 transition-colors {{ request()->routeIs('admin.trajets') ? 'text-slate-200' : 'text-slate-500 group-hover:text-slate-300' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                </svg>
                <span>Planification Trajets</span>
            </a>

            <!-- Gestion Employés -->
            <a href="{{ route('admin.users') }}"
               class="flex items-center space-x-3 px-3 py-2 rounded-md text-xs font-medium transition-colors group {{ request()->routeIs('admin.users') ? 'bg-slate-800/90 text-white font-semibold border-l-2 border-slate-400' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-850' }}">
                <svg class="w-4 h-4 transition-colors {{ request()->routeIs('admin.users') ? 'text-slate-200' : 'text-slate-500 group-hover:text-slate-300' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                </svg>
                <span>Gestion Employés</span>
            </a>
        </nav>
    </div>

    <!-- Footer / Token Status -->
    <div class="border-t border-slate-800 pt-3 px-2" x-data="{ token: localStorage.getItem('token_admin') }">
        <div class="flex items-center justify-between mb-2">
            <span class="text-[11px] text-slate-500 font-medium">Session Administrateur</span>
            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
        </div>
        <button @click="localStorage.removeItem('token_admin'); window.location.href='/login'"
                class="flex items-center space-x-2 w-full text-left text-xs text-slate-400 hover:text-slate-200 transition-colors py-1">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12" />
            </svg>
            <span>Déconnexion</span>
        </button>
    </div>
</aside>

<!-- Content Area -->
<main class="flex-1 p-8 overflow-y-auto bg-slate-950">
    @yield('content')
</main>

</body>
</html>
