@extends('layouts.guichet')

@push('head')
    <style>
        @media print {
            body * { visibility: hidden !important; }
            #printable-ticket, #printable-ticket * { visibility: visible !important; }
            #printable-ticket { position: absolute; inset: 0; width: 100%; max-width: 400px; margin: 0 auto; }
        }
    </style>
@endpush

@section('content')
    <div x-data="guichetApp()" x-init="loadTrips()" class="space-y-6">

        <!-- En-tête / Barre de recherche simplifiée et fonctionnelle -->
        <div class="bg-slate-900 border border-slate-800 p-6 rounded-md">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-base font-semibold text-white">Guichet de vente rapide</h1>
                    <p class="text-xs text-slate-400 mt-0.5">Sélectionnez un trajet ci-dessous pour émettre un billet.</p>
                </div>
                <div class="flex items-center gap-3">
                    <div>
                        <label class="block text-[10px] uppercase font-semibold text-slate-400 mb-1">Date du voyage</label>
                        <input type="date" x-model="filters.date" @change="loadTrips()" class="bg-slate-950 border border-slate-700 text-white rounded px-3 py-1.5 text-xs outline-none focus:border-slate-500">
                    </div>
                    <div>
                        <label class="block text-[10px] uppercase font-semibold text-slate-400 mb-1">Filtrer par destination</label>
                        <input type="search" x-model="filters.search" placeholder="Ex. Kayes, Ségou..." class="bg-slate-950 border border-slate-700 text-white rounded px-3 py-1.5 text-xs outline-none focus:border-slate-500 placeholder-slate-600">
                    </div>
                </div>
            </div>
        </div>

        <!-- Notification d'erreur ou succès -->
        <template x-if="message">
            <div class="p-3 rounded text-xs border" :class="success ? 'bg-emerald-950/30 border-emerald-900 text-emerald-400' : 'bg-red-950/30 border-red-900 text-red-400'" x-text="message"></div>
        </template>

        <!-- Grille principale de vente : Liste des trajets (gauche) & Formulaire de saisie (droite) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

            <!-- LISTE DES DÉPARTS DISPONIBLES (7 colonnes) -->
            <div class="lg:col-span-7 bg-slate-900 border border-slate-800 rounded-md overflow-hidden">
                <div class="p-4 border-b border-slate-800 flex items-center justify-between">
                    <h2 class="text-xs font-medium text-slate-400 uppercase tracking-wider">1. Choisir un départ</h2>
                    <button @click="loadTrips()" class="text-xs text-slate-400 hover:text-white underline">Actualiser</button>
                </div>

                <div class="divide-y divide-slate-800 max-h-[600px] overflow-y-auto">
                    <template x-for="trip in filteredTrips()" :key="trip.id">
                        <div @click="selectTrip(trip)" class="p-4 transition-colors cursor-pointer flex items-center justify-between" :class="selectedTrip && selectedTrip.id === trip.id ? 'bg-slate-800/80 border-l-2 border-white' : 'hover:bg-slate-800/40'">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-mono text-xs font-bold text-slate-300 bg-slate-800 px-2 py-0.5 rounded" x-text="formatTime(trip.heure_depart_prevue)"></span>
                                    <span class="text-sm font-bold text-white" x-text="trip.ligne.ville_depart + ' → ' + trip.ligne.ville_arrivee"></span>
                                </div>
                                <div class="mt-1.5 flex items-center gap-3 text-[11px] text-slate-400">
                                    <span>Bus : <strong class="text-slate-300" x-text="trip.bus.immatriculation"></strong></span>
                                    <span>Capacité : <strong class="text-slate-300" x-text="trip.bus.capacite + ' pl.'"></strong></span>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="text-sm font-bold text-white block" x-text="formatPrice(trip.ligne.prix_base)"></span>
                                <span class="text-[10px] text-slate-400 uppercase">Tarif unique</span>
                            </div>
                        </div>
                    </template>

                    <div x-show="loading" class="p-8 text-center text-xs text-slate-500">Chargement des plannings...</div>
                    <div x-show="!loading && filteredTrips().length === 0" class="p-8 text-center text-xs text-slate-500">Aucun départ disponible pour cette date.</div>
                </div>
            </div>

            <!-- FORMULAIRE DE VENTE DU BILLET (5 colonnes) -->
            <div class="lg:col-span-5 bg-slate-900 border border-slate-800 rounded-md p-5">
                <div class="border-b border-slate-800 pb-3 mb-4">
                    <h2 class="text-xs font-medium text-slate-400 uppercase tracking-wider">2. Saisir le passager</h2>
                </div>

                <div x-show="!selectedTrip" class="py-12 text-center">
                    <p class="text-xs text-slate-500">Veuillez sélectionner un trajet dans la liste de gauche pour afficher le formulaire de vente.</p>
                </div>

                <form x-show="selectedTrip" @submit.prevent="sellTicket()" class="space-y-4">
                    <div class="bg-slate-950 p-3 rounded border border-slate-800 text-xs space-y-1">
                        <span class="text-slate-500 block uppercase text-[10px]">Trajet sélectionné :</span>
                        <strong class="text-white block text-sm" x-text="selectedTrip ? selectedTrip.ligne.ville_depart + ' → ' + selectedTrip.ligne.ville_arrivee : ''"></strong>
                        <div class="text-slate-400 flex justify-between pt-1">
                            <span>Heure : <span class="text-slate-200" x-text="selectedTrip ? formatTime(selectedTrip.heure_depart_prevue) : ''"></span></span>
                            <span>Prix : <strong class="text-white" x-text="selectedTrip ? formatPrice(selectedTrip.ligne.prix_base) : ''"></strong></span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">Nom complet du passager *</label>
                        <input x-model="form.nom_passager" required placeholder="Ex. Amadou Traoré" class="w-full bg-slate-950 border border-slate-700 rounded px-3 py-2 text-xs text-white outline-none focus:border-slate-500">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-1">Téléphone</label>
                            <input x-model="form.telephone_passager" placeholder="+223..." class="w-full bg-slate-950 border border-slate-700 rounded px-3 py-2 text-xs text-white outline-none focus:border-slate-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-1">N° de siège</label>
                            <input type="number" min="1" :max="selectedTrip ? selectedTrip.bus.capacite : 1" x-model="form.numero_siege" placeholder="Auto" class="w-full bg-slate-950 border border-slate-700 rounded px-3 py-2 text-xs text-white outline-none focus:border-slate-500">
                        </div>
                    </div>

                    <div class="pt-2">
                        <button type="submit" :disabled="saving" class="w-full bg-slate-800 hover:bg-slate-700 text-white font-medium py-2.5 px-4 rounded text-xs transition-colors border border-slate-700 disabled:opacity-50">
                            <span x-text="saving ? 'Émission en cours...' : 'Confirmer et émettre le billet'"></span>
                        </button>
                    </div>
                </form>
            </div>

        </div>

        <!-- MODALE DE VALIDATION ET IMPRESSION DU BILLET -->
        <div x-show="ticket" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4">
            <div class="bg-slate-900 border border-slate-800 rounded-lg w-full max-w-sm p-6 space-y-4 shadow-xl">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <span class="text-xs font-bold text-emerald-400 uppercase">Billet émis avec succès</span>
                    <button @click="ticket = null" class="text-slate-400 hover:text-white">✕</button>
                </div>

                <!-- Reçu / Ticket Prêt à Imprimer -->
                <div id="printable-ticket" class="bg-white text-slate-900 p-5 rounded space-y-3 text-xs font-sans">
                    <div class="flex justify-between items-start border-b border-slate-200 pb-2">
                        <div>
                            <h3 class="font-black text-sm tracking-tight">MALI TRANS</h3>
                            <p class="text-[9px] text-slate-500 uppercase">Reçu de transport interurbain</p>
                        </div>
                        <span class="bg-slate-100 text-slate-800 font-bold px-2 py-0.5 rounded text-[10px]">PAYÉ</span>
                    </div>

                    <div class="space-y-1.5 pt-1">
                        <div><span class="text-slate-500 text-[10px] block uppercase">Passager</span><strong class="text-sm font-bold" x-text="ticket ? ticket.nom_passager : ''"></strong></div>
                        <div class="grid grid-cols-2 gap-2 pt-1">
                            <div><span class="text-slate-500 text-[10px] block uppercase">Trajet</span><span class="font-semibold" x-text="ticket && ticket.trajet && ticket.trajet.ligne ? ticket.trajet.ligne.ville_depart + ' → ' + ticket.trajet.ligne.ville_arrivee : '—'"></span></div>
                            <div><span class="text-slate-500 text-[10px] block uppercase">Siège N°</span><span class="font-semibold" x-text="ticket ? ticket.numero_siege : '—'"></span></div>
                        </div>
                        <div class="grid grid-cols-2 gap-2 pt-1">
                            <div><span class="text-slate-500 text-[10px] block uppercase">Montant</span><span class="font-semibold" x-text="ticket ? formatPrice(ticket.prix) : '—'"></span></div>
                            <div><span class="text-slate-500 text-[10px] block uppercase">Date</span><span class="font-semibold" x-text="ticket ? formatDate(ticket.date_paiement || ticket.created_at) : '—'"></span></div>
                        </div>
                    </div>

                    <div class="border-t border-slate-200 pt-3 flex items-center justify-between gap-3">
                        <div id="ticket-qr" class="w-20 h-20 bg-slate-50 p-1 shrink-0 flex items-center justify-center border border-slate-200"></div>
                        <div class="text-right overflow-hidden">
                            <span class="text-[9px] text-slate-400 block uppercase">Code de validation</span>
                            <span class="font-mono text-[9px] font-bold text-slate-700 break-all" x-text="ticket ? ticket.code_qr_hash : ''"></span>
                        </div>
                    </div>
                </div>

                <div class="flex gap-2 pt-2">
                    <button @click="ticket = null" class="flex-1 bg-slate-800 hover:bg-slate-700 text-slate-300 py-2 rounded text-xs font-medium border border-slate-700">Fermer</button>
                    <button @click="window.print()" class="flex-1 bg-white hover:bg-slate-200 text-slate-900 py-2 rounded text-xs font-bold">Imprimer le reçu</button>
                </div>
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
                requestHeaders() {
                    return {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    };
                },
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
                            headers: { ...this.requestHeaders(), 'Content-Type': 'application/json' },
                            body: JSON.stringify(this.form),
                        });
                        const data = await response.json();
                        if (!response.ok) throw new Error(data.message || 'Impossible d’émettre le billet.');
                        this.ticket = data.data;
                        this.$nextTick(() => this.renderQrCode());
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
                formatDate(value) {
                    if (!value) return '—';
                    return new Date(value).toLocaleString('fr-FR', { dateStyle: 'medium', timeStyle: 'short' });
                },
                renderQrCode() {
                    const target = document.getElementById('ticket-qr');
                    if (!target || !this.ticket) return;
                    target.innerHTML = '';
                    if (typeof QRCode === 'undefined') {
                        target.textContent = 'QR indisponible';
                        return;
                    }
                    new QRCode(target, {
                        text: this.ticket.code_qr_hash,
                        width: 80,
                        height: 80,
                        colorDark: '#0f172a',
                        colorLight: '#ffffff',
                        correctLevel: QRCode.CorrectLevel.M,
                    });
                },
            };
        }
    </script>
@endsection
