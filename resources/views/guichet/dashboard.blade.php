@extends('layouts.guichet')

@section('content')
    <div x-data="guichetApp()" x-init="loadTrips()" class="space-y-6">
        <section class="grid gap-5 lg:grid-cols-[1.35fr_.65fr]">
            <div class="overflow-hidden rounded-[2rem] bg-navy p-6 text-white shadow-2xl shadow-navy/15 sm:p-8">
                <div class="max-w-2xl">
                    <p class="mb-4 text-xs font-bold uppercase tracking-[.22em] text-mint/80">Poste de vente</p>
                    <h1 class="max-w-xl text-3xl font-black tracking-[-.04em] sm:text-5xl">Vendez un billet en quelques secondes.</h1>
                    <p class="mt-4 max-w-xl text-sm leading-6 text-white/65">Sélectionnez un départ, renseignez le passager et imprimez le reçu avec son code de validation.</p>
                </div>
                <div class="mt-8 grid max-w-xl grid-cols-2 gap-3 sm:grid-cols-3">
                    <div class="rounded-2xl bg-white/10 p-4"><p class="text-2xl font-black" x-text="trips.length">0</p><p class="mt-1 text-[11px] font-semibold text-white/55">Départs disponibles</p></div>
                    <div class="rounded-2xl bg-white/10 p-4"><p class="text-2xl font-black" x-text="selectedTrip ? selectedTrip.bus.capacite : '—'">—</p><p class="mt-1 text-[11px] font-semibold text-white/55">Places / bus</p></div>
                    <div class="hidden rounded-2xl bg-coral p-4 text-white sm:block"><p class="text-2xl font-black">FCFA</p><p class="mt-1 text-[11px] font-semibold text-white/75">Paiement comptant</p></div>
                </div>
            </div>
            <div class="rounded-[2rem] border border-navy/10 bg-white p-6 shadow-sm sm:p-8">
                <div class="flex items-start justify-between gap-4">
                    <div><p class="text-xs font-bold uppercase tracking-[.18em] text-navy/45">Recherche rapide</p><h2 class="mt-2 text-xl font-black tracking-tight">Trouver un départ</h2></div>
                    <span class="grid h-10 w-10 place-items-center rounded-2xl bg-coral/10 text-xl text-coral">⌕</span>
                </div>
                <div class="mt-6 space-y-3">
                    <label class="block text-xs font-bold text-navy/60">Date</label>
                    <input type="date" x-model="filters.date" @change="loadTrips()" class="w-full rounded-xl border border-navy/15 bg-paper px-4 py-3 text-sm font-semibold outline-none transition focus:border-coral focus:ring-4 focus:ring-coral/10">
                    <label class="block pt-2 text-xs font-bold text-navy/60">Ville d’arrivée</label>
                    <input type="search" x-model="filters.search" placeholder="Ex. Sikasso, Mopti…" class="w-full rounded-xl border border-navy/15 bg-paper px-4 py-3 text-sm font-semibold outline-none transition focus:border-coral focus:ring-4 focus:ring-coral/10">
                </div>
            </div>
        </section>

        <template x-if="message">
            <div class="rounded-2xl border px-4 py-3 text-sm font-semibold" :class="success ? 'border-emerald-700/20 bg-emerald-50 text-emerald-800' : 'border-red-700/20 bg-red-50 text-red-800'" x-text="message"></div>
        </template>

        <section class="grid gap-6 xl:grid-cols-[1.1fr_.9fr]">
            <div class="rounded-[2rem] border border-navy/10 bg-white p-5 shadow-sm sm:p-7">
                <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-end">
                    <div><p class="text-xs font-bold uppercase tracking-[.18em] text-navy/45">Planning du jour</p><h2 class="mt-2 text-2xl font-black tracking-tight">Départs disponibles</h2></div>
                    <button @click="loadTrips()" class="rounded-xl border border-navy/15 px-3 py-2 text-xs font-bold transition hover:border-coral hover:text-coral">Actualiser</button>
                </div>
                <div class="mt-6 space-y-3">
                    <template x-for="trip in filteredTrips()" :key="trip.id">
                        <button @click="selectTrip(trip)" class="group w-full rounded-2xl border p-4 text-left transition hover:-translate-y-0.5 hover:border-coral/50 hover:shadow-lg hover:shadow-coral/10" :class="selectedTrip && selectedTrip.id === trip.id ? 'border-coral bg-coral/5 ring-2 ring-coral/10' : 'border-navy/10 bg-paper/60'">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div class="flex items-center gap-4">
                                    <span class="grid h-14 w-14 place-items-center rounded-2xl bg-navy text-sm font-black text-white" x-text="formatTime(trip.heure_depart_prevue)">08:00</span>
                                    <span><span class="block text-sm font-black" x-text="trip.ligne.ville_depart + ' → ' + trip.ligne.ville_arrivee"></span><span class="mt-1 block text-xs font-semibold text-navy/50" x-text="trip.bus.immatriculation + ' · ' + trip.bus.capacite + ' places'"></span></span>
                                </div>
                                <span class="inline-flex w-fit rounded-full bg-mint px-3 py-1.5 text-xs font-black text-navy" x-text="formatPrice(trip.ligne.prix_base)">8 000 FCFA</span>
                            </div>
                        </button>
                    </template>
                    <div x-show="loading" class="rounded-2xl bg-paper px-4 py-8 text-center text-sm font-semibold text-navy/50">Chargement des départs…</div>
                    <div x-show="!loading && filteredTrips().length === 0" class="rounded-2xl border border-dashed border-navy/15 px-4 py-10 text-center"><p class="text-sm font-bold">Aucun départ trouvé</p><p class="mt-1 text-xs text-navy/50">Modifiez la date ou la recherche.</p></div>
                </div>
            </div>

            <div class="rounded-[2rem] bg-white p-5 shadow-sm ring-1 ring-navy/10 sm:p-7">
                <div class="flex items-start justify-between gap-3"><div><p class="text-xs font-bold uppercase tracking-[.18em] text-navy/45">Nouvelle vente</p><h2 class="mt-2 text-2xl font-black tracking-tight">Émettre un billet</h2></div><span class="grid h-10 w-10 place-items-center rounded-2xl bg-navy text-lg text-white">＋</span></div>
                <div x-show="!selectedTrip" class="mt-8 rounded-2xl bg-paper p-6 text-center"><p class="text-sm font-black">Sélectionnez un départ</p><p class="mt-2 text-xs leading-5 text-navy/50">Le formulaire de vente apparaîtra ici.</p></div>
                <form x-show="selectedTrip" @submit.prevent="sellTicket()" class="mt-6 space-y-4">
                    <div class="rounded-2xl bg-coral/10 p-4"><p class="text-[11px] font-bold uppercase tracking-wider text-coral">Départ sélectionné</p><p class="mt-1 text-sm font-black" x-text="selectedTrip ? selectedTrip.ligne.ville_depart + ' → ' + selectedTrip.ligne.ville_arrivee + ' · ' + formatTime(selectedTrip.heure_depart_prevue) : ''"></p></div>
                    <div><label class="mb-1.5 block text-xs font-bold text-navy/60">Nom du passager</label><input x-model="form.nom_passager" required placeholder="Ex. Amadou Traoré" class="w-full rounded-xl border border-navy/15 bg-paper px-4 py-3 text-sm font-semibold outline-none transition focus:border-coral focus:ring-4 focus:ring-coral/10"></div>
                    <div class="grid gap-4 sm:grid-cols-2"><div><label class="mb-1.5 block text-xs font-bold text-navy/60">Téléphone</label><input x-model="form.telephone_passager" placeholder="+223 70 00 00 00" class="w-full rounded-xl border border-navy/15 bg-paper px-4 py-3 text-sm font-semibold outline-none transition focus:border-coral focus:ring-4 focus:ring-coral/10"></div><div><label class="mb-1.5 block text-xs font-bold text-navy/60">N° de siège</label><input type="number" min="1" :max="selectedTrip ? selectedTrip.bus.capacite : 1" x-model="form.numero_siege" placeholder="Auto" class="w-full rounded-xl border border-navy/15 bg-paper px-4 py-3 text-sm font-semibold outline-none transition focus:border-coral focus:ring-4 focus:ring-coral/10"></div></div>
                    <div class="flex items-center justify-between rounded-xl border border-navy/10 px-4 py-3"><span class="text-xs font-bold text-navy/60">Montant à encaisser</span><strong class="text-lg font-black" x-text="selectedTrip ? formatPrice(selectedTrip.ligne.prix_base) : '—'"></strong></div>
                    <button type="submit" :disabled="saving" class="flex w-full items-center justify-center gap-2 rounded-xl bg-coral px-4 py-3.5 text-sm font-black text-white shadow-lg shadow-coral/20 transition hover:bg-coral/90 disabled:cursor-wait disabled:opacity-60"><span x-text="saving ? 'Émission en cours…' : 'Émettre le billet'"></span><span x-show="!saving">↗</span></button>
                </form>
            </div>
        </section>

        <div x-show="ticket" x-transition class="fixed inset-0 z-50 grid place-items-center bg-navy/60 p-5 backdrop-blur-sm">
            <div class="w-full max-w-md rounded-[2rem] bg-white p-7 shadow-2xl">
                <div class="flex items-start justify-between"><div><p class="text-xs font-bold uppercase tracking-[.18em] text-emerald-600">Vente confirmée</p><h2 class="mt-2 text-2xl font-black">Billet prêt</h2></div><button @click="ticket = null" class="text-navy/40 hover:text-navy">✕</button></div>
                <div class="mt-6 rounded-2xl bg-paper p-5"><p class="text-xs font-bold text-navy/50">Passager</p><p class="mt-1 font-black" x-text="ticket ? ticket.nom_passager : ''"></p><p class="mt-4 text-xs font-bold text-navy/50">Code de validation</p><p class="mt-1 break-all font-mono text-sm font-bold text-coral" x-text="ticket ? ticket.code_qr_hash : ''"></p></div>
                <button @click="window.print()" class="mt-5 w-full rounded-xl bg-navy px-4 py-3 text-sm font-black text-white">Imprimer le reçu</button>
            </div>
        </div>
    </div>

    <script>
        function guichetApp() {
            return {
                trips: [],
                selectedTrip: null,
                ticket: null,
                loading: false,
                saving: false,
                success: false,
                message: '',
                filters: { date: new Date().toISOString().slice(0, 10), search: '' },
                form: { nom_passager: '', telephone_passager: '', numero_siege: '' },
                async loadTrips() {
                    this.loading = true;
                    this.message = '';
                    try {
                        const params = new URLSearchParams({ date: this.filters.date });
                        const response = await fetch('/api/v1/guichet/trajets?' + params.toString(), { headers: { Accept: 'application/json' } });
                        const data = await response.json();
                        if (!response.ok) throw new Error(data.message || 'Impossible de charger les départs.');
                        this.trips = data.data || [];
                        if (this.selectedTrip && !this.trips.some((trip) => trip.id === this.selectedTrip.id)) this.selectedTrip = null;
                    } catch (error) {
                        this.success = false;
                        this.message = error.message;
                    } finally {
                        this.loading = false;
                    }
                },
                filteredTrips() {
                    const query = this.filters.search.trim().toLowerCase();
                    if (!query) return this.trips;
                    return this.trips.filter((trip) => `${trip.ligne.ville_depart} ${trip.ligne.ville_arrivee}`.toLowerCase().includes(query));
                },
                selectTrip(trip) {
                    this.selectedTrip = trip;
                    this.form = { nom_passager: '', telephone_passager: '', numero_siege: '' };
                    this.message = '';
                },
                async sellTicket() {
                    this.saving = true;
                    this.message = '';
                    try {
                        const response = await fetch(`/api/v1/guichet/trajets/${this.selectedTrip.id}/billets`, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
                            body: JSON.stringify(this.form),
                        });
                        const data = await response.json();
                        if (!response.ok) throw new Error(data.message || 'Impossible d’émettre le billet.');
                        this.ticket = data.data;
                        this.success = true;
                        this.form = { nom_passager: '', telephone_passager: '', numero_siege: '' };
                        await this.loadTrips();
                    } catch (error) {
                        this.success = false;
                        this.message = error.message;
                    } finally {
                        this.saving = false;
                    }
                },
                formatTime(value) {
                    return new Date(value).toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
                },
                formatPrice(value) {
                    return new Intl.NumberFormat('fr-FR').format(Number(value)) + ' FCFA';
                },
            };
        }
    </script>
@endsection
