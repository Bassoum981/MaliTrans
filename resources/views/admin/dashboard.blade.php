@extends('layouts.admin')

@section('title', 'MaliTrans — Vue d’ensemble')
@section('page_label', 'Vue d’ensemble')

@section('content')
    <div x-data="dashboardAdmin()" x-init="loadStats()" class="space-y-8">
        <section class="flex flex-col justify-between gap-5 md:flex-row md:items-end">
            <div>
                <p class="text-sm font-semibold text-slate-400">Bonjour, {{ request()->user()->name }}.</p>
                <h1 class="mt-2 max-w-2xl text-3xl font-black tracking-[-.04em] text-white sm:text-5xl">Gardez chaque départ sous contrôle.</h1>
                <p class="mt-4 max-w-xl text-sm leading-6 text-slate-400">Une lecture claire de la flotte, des lignes et des trajets planifiés pour prendre les bonnes décisions au bon moment.</p>
            </div>
            <a href="{{ route('admin.trajets', [], false) }}" class="inline-flex w-fit items-center gap-2 rounded-xl bg-cyan px-4 py-3 text-sm font-black text-ink shadow-lg shadow-cyan/10 transition hover:-translate-y-0.5">Planifier un trajet <span>↗</span></a>
        </section>

        <template x-if="error">
            <div class="rounded-2xl border border-red-400/20 bg-red-400/10 px-4 py-3 text-sm font-semibold text-red-200" x-text="error"></div>
        </template>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <template x-for="metric in metrics" :key="metric.key">
                <div class="rounded-2xl border border-line bg-panel p-5 shadow-panel">
                    <div class="flex items-start justify-between gap-3"><span class="text-xs font-bold uppercase tracking-[.15em] text-slate-500" x-text="metric.label"></span><span class="grid h-9 w-9 place-items-center rounded-xl bg-white/5 text-cyan" x-text="metric.icon"></span></div>
                    <p class="mt-7 text-4xl font-black tracking-[-.05em] text-white" x-text="stats[metric.key] ?? '—'">—</p>
                    <p class="mt-2 text-xs font-semibold text-slate-500" x-text="metric.detail"></p>
                </div>
            </template>
        </section>

        <section class="grid gap-5 xl:grid-cols-[1.3fr_.7fr]">
            <div class="rounded-2xl border border-line bg-panel p-6 shadow-panel sm:p-7">
                <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-end">
                    <div><p class="text-xs font-bold uppercase tracking-[.18em] text-cyan/70">Raccourcis opérationnels</p><h2 class="mt-2 text-2xl font-black tracking-tight text-white">Que souhaitez-vous gérer ?</h2></div>
                    <span class="text-xs font-semibold text-slate-500">Accès direct</span>
                </div>
                <div class="mt-6 grid gap-3 sm:grid-cols-2">
                    <a href="{{ route('admin.trajets', [], false) }}" class="group rounded-2xl border border-line bg-ink/40 p-4 transition hover:-translate-y-0.5 hover:border-cyan/40"><span class="grid h-10 w-10 place-items-center rounded-xl bg-cyan/10 text-lg text-cyan">↗</span><span class="mt-4 block text-sm font-black text-white">Planifier un trajet</span><span class="mt-1 block text-xs leading-5 text-slate-500">Affecter un bus et un chauffeur à une ligne.</span><span class="mt-4 block text-xs font-bold text-cyan opacity-0 transition group-hover:opacity-100">Ouvrir →</span></a>
                    <a href="{{ route('admin.buses', [], false) }}" class="group rounded-2xl border border-line bg-ink/40 p-4 transition hover:-translate-y-0.5 hover:border-lime/40"><span class="grid h-10 w-10 place-items-center rounded-xl bg-lime/10 text-lg text-lime">▣</span><span class="mt-4 block text-sm font-black text-white">Mettre à jour la flotte</span><span class="mt-1 block text-xs leading-5 text-slate-500">Suivre la capacité et l’état des véhicules.</span><span class="mt-4 block text-xs font-bold text-lime opacity-0 transition group-hover:opacity-100">Ouvrir →</span></a>
                    <a href="{{ route('admin.lignes', [], false) }}" class="group rounded-2xl border border-line bg-ink/40 p-4 transition hover:-translate-y-0.5 hover:border-cyan/40"><span class="grid h-10 w-10 place-items-center rounded-xl bg-cyan/10 text-lg text-cyan">⌁</span><span class="mt-4 block text-sm font-black text-white">Gérer les lignes</span><span class="mt-1 block text-xs leading-5 text-slate-500">Modifier les villes, distances et tarifs.</span><span class="mt-4 block text-xs font-bold text-cyan opacity-0 transition group-hover:opacity-100">Ouvrir →</span></a>
                    <a href="{{ route('admin.users', [], false) }}" class="group rounded-2xl border border-line bg-ink/40 p-4 transition hover:-translate-y-0.5 hover:border-lime/40"><span class="grid h-10 w-10 place-items-center rounded-xl bg-lime/10 text-lg text-lime">◎</span><span class="mt-4 block text-sm font-black text-white">Gérer le personnel</span><span class="mt-1 block text-xs leading-5 text-slate-500">Créer et retirer les comptes de l’équipe.</span><span class="mt-4 block text-xs font-bold text-lime opacity-0 transition group-hover:opacity-100">Ouvrir →</span></a>
                </div>
            </div>
            <div class="rounded-2xl bg-cyan p-6 text-ink shadow-xl shadow-cyan/10 sm:p-7">
                <p class="text-xs font-black uppercase tracking-[.18em] text-ink/55">À retenir</p>
                <h2 class="mt-3 text-2xl font-black tracking-tight">La qualité du départ se prépare en amont.</h2>
                <p class="mt-4 text-sm leading-6 text-ink/70">Gardez les lignes tarifées, les bus en service et les chauffeurs affectés pour éviter les blocages au guichet.</p>
                <div class="mt-8 border-t border-ink/15 pt-4 text-xs font-bold text-ink/60">Dernière synchronisation : <span x-text="lastUpdated">—</span></div>
            </div>
        </section>
    </div>

    <script>
        function dashboardAdmin() {
            return {
                stats: {},
                error: '',
                lastUpdated: '—',
                metrics: [
                    { key: 'buses_count', label: 'Flotte', detail: 'bus enregistrés', icon: '▣' },
                    { key: 'lignes_count', label: 'Réseau', detail: 'lignes configurées', icon: '⌁' },
                    { key: 'drivers_count', label: 'Équipe', detail: 'chauffeurs habilités', icon: '◎' },
                    { key: 'trajets_count', label: 'Planning', detail: 'trajets planifiés', icon: '↗' },
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
