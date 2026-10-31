@extends('layouts.admin')

@section('content')
    <div x-data="busManager()" x-init="loadBuses()" class="space-y-6">

        <div>
            <h2 class="text-lg font-semibold text-white tracking-tight">Flotte de bus</h2>
            <p class="text-slate-500 text-xs mt-0.5">Enregistrement, modification et suivi des véhicules</p>
        </div>

        <template x-if="message">
            <div :class="success ? 'bg-emerald-950/30 border-emerald-900 text-emerald-400' : 'bg-red-950/30 border-red-900 text-red-400'" class="p-3 rounded border text-xs" x-text="message"></div>
        </template>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Formulaire Dynamique (Création / Modification) -->
            <div class="bg-slate-900 border border-slate-800 p-6 rounded-md h-fit">
                <h3 class="text-xs font-medium text-slate-400 uppercase tracking-wider mb-4" x-text="editMode ? 'Modifier le bus' : 'Nouveau véhicule'"></h3>
                <form @submit.prevent="saveBus" class="space-y-4">
                    <div>
                        <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-wider mb-1">Immatriculation</label>
                        <input type="text" x-model="form.immatriculation" placeholder="Ex: ML-555-MD" required
                               class="w-full bg-slate-950 border border-slate-800 rounded px-3 py-2 text-xs text-white focus:outline-none focus:border-slate-600">
                    </div>

                    <div>
                        <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-wider mb-1">Capacité (Sièges)</label>
                        <input type="number" x-model="form.capacite" min="1" placeholder="Ex: 45" required
                               class="w-full bg-slate-950 border border-slate-800 rounded px-3 py-2 text-xs text-white focus:outline-none focus:border-slate-600">
                    </div>

                    <div>
                        <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-wider mb-1">Statut</label>
                        <select x-model="form.statut" required
                                class="w-full bg-slate-950 border border-slate-800 rounded px-3 py-2 text-xs text-white focus:outline-none focus:border-slate-600">
                            <option value="EN_SERVICE">EN SERVICE</option>
                            <option value="EN_MAINTENANCE">EN MAINTENANCE</option>
                            <option value="HORS_SERVICE">HORS SERVICE</option>
                        </select>
                    </div>

                    <div class="flex gap-2 pt-2">
                        <button type="submit" :disabled="loading"
                                class="flex-1 bg-slate-800 hover:bg-slate-700 text-white font-medium py-2.5 rounded text-xs transition-colors border border-slate-700 disabled:opacity-50"
                                x-text="editMode ? 'Mettre à jour' : 'Enregistrer le bus'">
                        </button>
                        <button type="button" @click="resetForm" x-show="editMode"
                                class="px-3 bg-slate-950 hover:bg-slate-800 text-slate-400 rounded text-xs border border-slate-800">
                            Annuler
                        </button>
                    </div>
                </form>
            </div>

            <!-- Tableau des véhicules -->
            <div class="lg:col-span-2 bg-slate-900 border border-slate-800 rounded-md overflow-hidden">
                <div class="p-4 border-b border-slate-800">
                    <h3 class="text-xs font-medium text-slate-400 uppercase tracking-wider">Véhicules enregistrés</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-950 text-slate-400 uppercase text-[10px] tracking-wider border-b border-slate-800">
                        <tr>
                            <th class="p-3">Immatriculation</th>
                            <th class="p-3">Capacité</th>
                            <th class="p-3">Statut</th>
                            <th class="p-3 text-right">Actions</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800 text-slate-300">
                        <template x-for="bus in buses" :key="bus.id">
                            <tr>
                                <td class="p-3 font-medium text-white" x-text="bus.immatriculation"></td>
                                <td class="p-3" x-text="bus.capacite + ' places'"></td>
                                <td class="p-3">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-medium border"
                                          :class="{
                                            'bg-emerald-950 text-emerald-400 border-emerald-900': bus.statut === 'EN_SERVICE',
                                            'bg-amber-950 text-amber-400 border-amber-900': bus.statut === 'EN_MAINTENANCE',
                                            'bg-red-950 text-red-400 border-red-900': bus.statut === 'HORS_SERVICE'
                                        }"
                                          x-text="bus.statut">
                                    </span>
                                </td>
                                <td class="p-3 text-right space-x-2">
                                    <button @click="editBus(bus)" class="text-slate-400 hover:text-white transition-colors">Modifier</button>
                                    <button @click="deleteBus(bus.id)" class="text-red-400 hover:text-red-300 transition-colors">Supprimer</button>
                                </td>
                            </tr>
                        </template>
                        <template x-if="buses.length === 0">
                            <tr>
                                <td colspan="4" class="p-4 text-center text-slate-500">Aucun bus enregistré.</td>
                            </tr>
                        </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <script>
        function busManager() {
            return {
                buses: [],
                form: { id: null, immatriculation: '', capacite: 45, statut: 'EN_SERVICE' },
                editMode: false,
                loading: false,
                message: '',
                success: false,

                async loadBuses() {
                    const token = localStorage.getItem('token_admin');
                    try {
                        const res = await fetch('/api/v1/admin/buses', {
                            headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
                        });
                        if (res.ok) {
                            const json = await res.json();
                            this.buses = Array.isArray(json) ? json : (json.data || []);
                        }
                    } catch (err) {
                        console.error("Erreur chargement bus", err);
                    }
                },

                editBus(bus) {
                    this.editMode = true;
                    this.form = { ...bus };
                },

                resetForm() {
                    this.editMode = false;
                    this.form = { id: null, immatriculation: '', capacite: 45, statut: 'EN_SERVICE' };
                },

                async saveBus() {
                    this.loading = true;
                    this.message = '';
                    const token = localStorage.getItem('token_admin');
                    const url = this.editMode ? `/api/v1/admin/buses/${this.form.id}` : '/api/v1/admin/buses';
                    const method = this.editMode ? 'PUT' : 'POST';

                    try {
                        const res = await fetch(url, {
                            method: method,
                            headers: {
                                'Authorization': 'Bearer ' + token,
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(this.form)
                        });

                        const data = await res.json();

                        if (res.ok && data.success) {
                            this.success = true;
                            this.message = this.editMode ? 'Bus mis à jour avec succès.' : 'Bus enregistré avec succès.';
                            this.resetForm();
                            this.loadBuses();
                        } else {
                            this.success = false;
                            this.message = data.message || 'Erreur lors de l’enregistrement.';
                        }
                    } catch (err) {
                        this.success = false;
                        this.message = 'Erreur réseau ou serveur.';
                    } finally {
                        this.loading = false;
                    }
                },

                async deleteBus(id) {
                    if (!confirm('Confirmer la suppression de ce bus ?')) return;

                    const token = localStorage.getItem('token_admin');
                    try {
                        const res = await fetch(`/api/v1/admin/buses/${id}`, {
                            method: 'DELETE',
                            headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
                        });
                        const data = await res.json();
                        if (res.ok && data.success) {
                            this.success = true;
                            this.message = 'Bus supprimé avec succès.';
                            this.loadBuses();
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
