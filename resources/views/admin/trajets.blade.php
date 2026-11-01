@extends('layouts.admin')

@section('content')
    <div x-data="trajetManager()" x-init="initData()" class="space-y-6">

        <div>
            <h2 class="text-lg font-semibold text-white tracking-tight">Planification des trajets</h2>
            <p class="text-slate-500 text-xs mt-0.5">Affectation et consultation des départs interurbains</p>
        </div>

        <template x-if="message">
            <div :class="success ? 'bg-emerald-950/30 border-emerald-900 text-emerald-400' : 'bg-red-950/30 border-red-900 text-red-400'" class="p-3 rounded border text-xs" x-text="message"></div>
        </template>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Formulaire de planification -->
            <div class="bg-slate-900 border border-slate-800 p-6 rounded-md h-fit">
                <h3 class="text-xs font-medium text-slate-400 uppercase tracking-wider mb-4">Nouveau départ</h3>
                <form @submit.prevent="createTrajet" class="space-y-4">

                    <div>
                        <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-wider mb-1">Ligne</label>
                        <select x-model="form.ligne_id" required
                                class="w-full bg-slate-950 border border-slate-800 rounded px-3 py-2 text-xs text-white focus:outline-none focus:border-slate-600">
                            <option value="">Sélectionner une ligne</option>
                            <template x-for="ligne in lignes" :key="ligne.id">
                                <option :value="ligne.id" x-text="ligne.ville_depart + ' -> ' + ligne.ville_arrivee + ' (' + ligne.prix_base + ' FCFA)'"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-wider mb-1">Bus</label>
                        <select x-model="form.bus_id" required
                                class="w-full bg-slate-950 border border-slate-800 rounded px-3 py-2 text-xs text-white focus:outline-none focus:border-slate-600">
                            <option value="">Sélectionner un bus</option>
                            <template x-for="bus in buses" :key="bus.id">
                                <option :value="bus.id" x-text="bus.immatriculation + ' (Capacité : ' + bus.capacite + ')'"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-wider mb-1">Chauffeur</label>
                        <select x-model="form.chauffeur_id" required
                                class="w-full bg-slate-950 border border-slate-800 rounded px-3 py-2 text-xs text-white focus:outline-none focus:border-slate-600">
                            <option value="">Sélectionner un chauffeur</option>
                            <template x-for="driver in drivers" :key="driver.id">
                                <option :value="driver.id" x-text="driver.name + ' (' + driver.telephone + ')'"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-wider mb-1">Heure de départ</label>
                        <input type="datetime-local" x-model="form.heure_depart_prevue" required
                               class="w-full bg-slate-950 border border-slate-800 rounded px-3 py-2 text-xs text-white focus:outline-none focus:border-slate-600">
                    </div>

                    <button type="submit" :disabled="loading"
                            class="w-full bg-slate-800 hover:bg-slate-700 text-white font-medium py-2.5 rounded text-xs transition-colors border border-slate-700 disabled:opacity-50">
                        <span x-show="!loading">Planifier le trajet</span>
                        <span x-show="loading">Traitement en cours...</span>
                    </button>
                </form>
            </div>

            <!-- Tableau des trajets planifiés -->
            <div class="lg:col-span-2 bg-slate-900 border border-slate-800 rounded-md overflow-hidden">
                <div class="p-4 border-b border-slate-800">
                    <h3 class="text-xs font-medium text-slate-400 uppercase tracking-wider">Trajets programmés</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-950 text-slate-400 uppercase text-[10px] tracking-wider border-b border-slate-800">
                        <tr>
                            <th class="p-3">Ligne</th>
                            <th class="p-3">Bus</th>
                            <th class="p-3">Chauffeur</th>
                            <th class="p-3">Départ</th>
                            <th class="p-3">Tarif</th>
                            <th class="p-3 text-right">Actions</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800 text-slate-300">
                        <template x-for="trajet in trajets" :key="trajet.id">
                            <tr>
                                <td class="p-3 font-medium text-white" x-text="trajet.ligne ? trajet.ligne.ville_depart + ' -> ' + trajet.ligne.ville_arrivee : 'N/A'"></td>
                                <td class="p-3 text-slate-400" x-text="trajet.bus ? trajet.bus.immatriculation : 'N/A'"></td>
                                <td class="p-3 text-slate-400" x-text="trajet.chauffeur ? trajet.chauffeur.name : 'N/A'"></td>
                                <td class="p-3 font-mono text-slate-400" x-text="trajet.heure_depart_prevue"></td>
                                <td class="p-3 text-emerald-400" x-text="trajet.ligne ? trajet.ligne.prix_base + ' FCFA' : 'N/A'"></td>
                                <td class="p-3 text-right">
                                    <button @click="deleteTrajet(trajet.id)" class="text-red-400 hover:text-red-300 transition-colors">Supprimer</button>
                                </td>
                            </tr>
                        </template>
                        <template x-if="trajets.length === 0">
                            <tr>
                                <td colspan="6" class="p-4 text-center text-slate-500">Aucun trajet planifié pour le moment.</td>
                            </tr>
                        </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <script>
        function trajetManager() {
            return {
                lignes: [],
                buses: [],
                drivers: [],
                trajets: [],
                form: { ligne_id: '', bus_id: '', chauffeur_id: '', heure_depart_prevue: '' },
                loading: false,
                message: '',
                success: false,

                async initData() {                    const headers = { 'Accept': 'application/json' };

                    try {
                        const [lignesRes, busesRes, usersRes, trajetsRes] = await Promise.all([
                            fetch('/api/v1/admin/lignes', { headers }),
                            fetch('/api/v1/admin/buses', { headers }),
                            fetch('/api/v1/admin/users', { headers }),
                            fetch('/api/v1/admin/trajets', { headers })
                        ]);

                        if (lignesRes.ok) {
                            const json = await lignesRes.json();
                            this.lignes = Array.isArray(json) ? json : (json.data || []);
                        }
                        if (busesRes.ok) {
                            const json = await busesRes.json();
                            this.buses = Array.isArray(json) ? json : (json.data || []);
                        }
                        if (usersRes.ok) {
                            const json = await usersRes.json();
                            const users = Array.isArray(json) ? json : (json.data || []);
                            this.drivers = users.filter(u => u.role === 'chauffeur');
                        }
                        if (trajetsRes.ok) {
                            const json = await trajetsRes.json();
                            this.trajets = Array.isArray(json) ? json : (json.data || []);
                        }
                    } catch (err) {
                        console.error("Erreur chargement données", err);
                    }
                },

                async createTrajet() {
                    this.loading = true;
                    this.message = '';
                    try {
                        const res = await fetch('/api/v1/admin/trajets', {
                            method: 'POST',
                            headers: {                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(this.form)
                        });

                        const data = await res.json();

                        if (res.ok && data.success) {
                            this.success = true;
                            this.message = 'Trajet planifié avec succès.';
                            this.form = { ligne_id: '', bus_id: '', chauffeur_id: '', heure_depart_prevue: '' };
                            this.initData();
                        } else {
                            this.success = false;
                            this.message = data.message || 'Erreur lors de la planification.';
                        }
                    } catch (err) {
                        this.success = false;
                        this.message = 'Erreur réseau ou serveur.';
                    } finally {
                        this.loading = false;
                    }
                },

                async deleteTrajet(id) {
                    if (!confirm('Confirmer la suppression de ce trajet ?')) return;                    try {
                        const res = await fetch(`/api/v1/admin/trajets/${id}`, {
                            method: 'DELETE',
                            headers: { 'Accept': 'application/json' }
                        });
                        const data = await res.json();
                        if (res.ok && data.success) {
                            this.success = true;
                            this.message = 'Trajet supprimé avec succès.';
                            this.initData();
                        } else {
                            this.success = false;
                            this.message = data.message || 'Erreur lors de la suppression.';
                        }
                    } catch (err) {
                        this.success = false;
                        this.message = 'Erreur réseau ou serveur.';
                    }
                }
            }
        }
    </script>
@endsection
