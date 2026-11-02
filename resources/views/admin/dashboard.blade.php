@extends('layouts.admin')

@section('title', 'MaliTrans — Vue d’ensemble')
@section('page_label', 'Vue d’ensemble')

@section('content')
    <div x-data="dashboardAdmin()" x-init="loadStats()" class="space-y-6">

        <!-- En-tête simple et professionnel -->
        <div class="bg-slate-900 border border-slate-800 p-6 rounded-md flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-base font-semibold text-white">Tableau de bord — Bonjour, {{ request()->user()->name }}</h1>
                <p class="text-xs text-slate-400 mt-0.5">Vue globale du réseau de transport et des activités opérationnelles.</p>
            </div>
            <div>
                <a href="{{ route('admin.trajets', [], false) }}" class="inline-flex items-center justify-center bg-slate-800 hover:bg-slate-700 text-white font-medium px-4 py-2 rounded text-xs transition-colors border border-slate-700">
                    Planifier un trajet
                </a>
            </div>
        </div>

        <!-- Notification d'erreur -->
        <template x-if="error">
            <div class="bg-red-950/30 border border-red-900 text-red-400 p-3 rounded text-xs" x-text="error"></div>
        </template>

        <!-- Statistiques globales -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <template x-for="metric in metrics" :key="metric.key">
                <div class="bg-slate-900 border border-slate-800 p-5 rounded-md">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-medium text-slate-400 uppercase tracking-wider" x-text="metric.label"></span>
                        <span class="text-slate-500 text-xs" x-text="metric.icon"></span>
                    </div>
                    <div class="mt-3">
                        <span class="text-2xl font-bold text-white" x-text="stats[metric.key] ?? '—'">—</span>
                    </div>
                    <p class="mt-1 text-[11px] text-slate-500" x-text="metric.detail"></p>
                </div>
            </template>
        </div>

        <!-- Grille de navigation et informations -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Accès rapides -->
            <div class="lg:col-span-2 bg-slate-900 border border-slate-800 rounded-md overflow-hidden">
                <div class="p-4 border-b border-slate-800">
                    <h3 class="text-xs font-medium text-slate-400 uppercase tracking-wider">Accès rapides aux modules</h3>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-px bg-slate-800">
                    <a href="{{ route('admin.trajets', [], false) }}" class="bg-slate-900 p-4 hover:bg-slate-800/55 transition-colors block">
                        <span class="text-xs font-semibold text-white block">Trajets & Départs</span>
                        <span class="text-[11px] text-slate-400 mt-0.5 block">Planifier et affecter les véhicules.</span>
                    </a>
                    <a href="{{ route('admin.buses', [], false) }}" class="bg-slate-900 p-4 hover:bg-slate-800/55 transition-colors block">
                        <span class="text-xs font-semibold text-white block">Flotte de bus</span>
                        <span class="text-[11px] text-slate-400 mt-0.5 block">Gérer les immatriculations et statuts.</span>
                    </a>
                    <a href="{{ route('admin.lignes', [], false) }}" class="bg-slate-900 p-4 hover:bg-slate-800/55 transition-colors block">
                        <span class="text-xs font-semibold text-white block">Lignes interurbaines</span>
                        <span class="text-[11px] text-slate-400 mt-0.5 block">Configurer les trajets et tarifs de base.</span>
                    </a>
                    <a href="{{ route('admin.users', [], false) }}" class="bg-slate-900 p-4 hover:bg-slate-800/55 transition-colors block">
                        <span class="text-xs font-semibold text-white block">Gestion du personnel</span>
                        <span class="text-[11px] text-slate-400 mt-0.5 block">Comptes guichetiers et chauffeurs.</span>
                    </a>
                </div>
            </div>

            <!-- Bloc informations / système -->
            <div class="bg-slate-900 border border-slate-800 p-5 rounded-md flex flex-col justify-between">
                <div>
                    <h3 class="text-xs font-medium text-slate-400 uppercase tracking-wider mb-3">État du système</h3>
                    <p class="text-xs text-slate-300 leading-relaxed">
                        Le système de billetterie et les passerelles de contrôle sont opérationnels. Pensez à vérifier l'état des bus avant de valider de nouveaux départs.
                    </p>
                </div>
                <div class="mt-6 pt-3 border-t border-slate-800 text-[11px] text-slate-500 flex justify-between">
                    <span>Dernière synchro :</span>
                    <span class="font-mono text-slate-400" x-text="lastUpdated">—</span>
                </div>
            </div>

        </div>
    </div>

    <script>
        function dashboardAdmin() {
            return {
                stats: {},
                error: '',
                lastUpdated: '—',
                metrics: [
                    { key: 'buses_count', label: 'Flotte de bus', detail: 'Véhicules enregistrés', icon: '▣' },
                    { key: 'lignes_count', label: 'Lignes actives', detail: 'Itinéraires configurés', icon: '⌁' },
                    { key: 'drivers_count', label: 'Chauffeurs', detail: 'Personnel rattaché', icon: '◎' },
                    { key: 'trajets_count', label: 'Trajets', detail: 'Départs planifiés', icon: '↗' },
                ],
                async loadStats() {
                    try {
                        const response = await fetch('/api/v1/admin/stats', { headers: { Accept: 'application/json' } });
                        const data = await response.json();
                        if (!response.ok) throw new Error(data.message || 'Impossible de charger les statistiques.');
                        this.stats = data.data || {};
                        this.lastUpdated = new Date().toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
                    } catch (error) {
                        this.error = error.message;
                    }
                },
            };
        }
    </script>
@endsection
