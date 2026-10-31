@extends('layouts.admin')

@section('content')
    <div x-data="dashboardAdmin()" x-init="loadStats()" class="space-y-6">

        <div>
            <h2 class="text-lg font-semibold text-white tracking-tight">Tableau de bord</h2>
            <p class="text-slate-500 text-xs mt-0.5">Indicateurs opérationnels de la plateforme</p>
        </div>

        <!-- Cartes statistiques -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-slate-900 border border-slate-800 p-4 rounded-md">
                <div class="text-slate-500 text-[11px] font-medium uppercase tracking-wider">Bus enregistrés</div>
                <div class="text-2xl font-semibold text-white mt-1.5" x-text="stats.buses_count">0</div>
            </div>
            <div class="bg-slate-900 border border-slate-800 p-4 rounded-md">
                <div class="text-slate-500 text-[11px] font-medium uppercase tracking-wider">Lignes actives</div>
                <div class="text-2xl font-semibold text-white mt-1.5" x-text="stats.lignes_count">0</div>
            </div>
            <div class="bg-slate-900 border border-slate-800 p-4 rounded-md">
                <div class="text-slate-500 text-[11px] font-medium uppercase tracking-wider">Chauffeurs habilités</div>
                <div class="text-2xl font-semibold text-white mt-1.5" x-text="stats.drivers_count">0</div>
            </div>
            <div class="bg-slate-900 border border-slate-800 p-4 rounded-md">
                <div class="text-slate-500 text-[11px] font-medium uppercase tracking-wider">Trajets planifiés</div>
                <div class="text-2xl font-semibold text-white mt-1.5" x-text="stats.trajets_count">0</div>
            </div>
        </div>

        <!-- Actions rapides -->
        <div class="bg-slate-900 border border-slate-800 p-5 rounded-md">
            <h3 class="text-xs font-medium text-slate-400 uppercase tracking-wider mb-3">Raccourcis de gestion</h3>
            <div class="flex flex-wrap gap-2.5">
                <a href="{{ route('admin.buses') }}" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded text-xs font-medium transition-colors border border-slate-700">
                    Enregistrer un bus
                </a>
                <a href="{{ route('admin.lignes') }}" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded text-xs font-medium transition-colors border border-slate-700">
                    Créer une ligne
                </a>
                <a href="{{ route('admin.trajets') }}" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded text-xs font-medium transition-colors border border-slate-700">
                    Planifier un trajet
                </a>
                <a href="{{ route('admin.users') }}" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded text-xs font-medium transition-colors border border-slate-700">
                    Ajouter un employé
                </a>
            </div>
        </div>

    </div>

    <script>
        function dashboardAdmin() {
            return {
                stats: { buses_count: 0, lignes_count: 0, drivers_count: 0, trajets_count: 0 },
                async loadStats() {
                    const token = localStorage.getItem('token_admin');
                    try {
                        const res = await fetch('/api/v1/admin/stats', {
                            headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
                        });
                        if (res.ok) {
                            const json = await res.json();
                            this.stats = json.data;
                        }
                    } catch (err) {
                        console.error("Erreur chargement dashboard", err);
                    }
                }
            }
        }
    </script>
@endsection
