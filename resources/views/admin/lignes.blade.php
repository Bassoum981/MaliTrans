@extends('layouts.admin')

@section('title', 'MaliTrans — Lignes & tarifs')
@section('page_label', 'Lignes & tarifs')

@section('content')
    <div x-data="ligneManager()" x-init="loadLignes()" class="space-y-8">

        <div>
            <h1 class="text-3xl font-black tracking-[-.04em] text-white">Lignes interurbaines</h1>
            <p class="mt-2 text-sm text-slate-500">Configuration et gestion des itinéraires.</p>
        </div>

        <template x-if="message">
            <div :class="success ? 'bg-emerald-950/30 border-emerald-900 text-emerald-400' : 'bg-red-950/30 border-red-900 text-red-400'" class="p-3 rounded border text-xs" x-text="message"></div>
        </template>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Formulaire Dynamique -->
            <div class="h-fit rounded-2xl border border-line bg-panel p-6 shadow-panel">
                <h3 class="text-xs font-medium text-slate-400 uppercase tracking-wider mb-4" x-text="editMode ? 'Modifier la ligne' : 'Nouvelle ligne'"></h3>
                <form @submit.prevent="saveLigne" class="space-y-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-wider mb-1">Départ</label>
                            <input type="text" x-model="form.ville_depart" placeholder="Bamako" required
                                   class="w-full bg-slate-950 border border-slate-800 rounded px-3 py-2 text-xs text-white focus:outline-none focus:border-slate-600">
                        </div>
                        <div>
                            <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-wider mb-1">Arrivée</label>
                            <input type="text" x-model="form.ville_arrivee" placeholder="Kayes" required
                                   class="w-full bg-slate-950 border border-slate-800 rounded px-3 py-2 text-xs text-white focus:outline-none focus:border-slate-600">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-wider mb-1">Prix (FCFA)</label>
                            <input type="number" x-model="form.prix_base" placeholder="10000" required
                                   class="w-full bg-slate-950 border border-slate-800 rounded px-3 py-2 text-xs text-white focus:outline-none focus:border-slate-600">
                        </div>
                        <div>
                            <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-wider mb-1">Distance (km)</label>
                            <input type="number" x-model="form.distance_km" placeholder="500" required
                                   class="w-full bg-slate-950 border border-slate-800 rounded px-3 py-2 text-xs text-white focus:outline-none focus:border-slate-600">
                        </div>
                    </div>

                    <div class="flex gap-2 pt-2">
                        <button type="submit" :disabled="loading"
                                class="flex-1 bg-slate-800 hover:bg-slate-700 text-white font-medium py-2.5 rounded text-xs transition-colors border border-slate-700 disabled:opacity-50"
                                x-text="editMode ? 'Mettre à jour' : 'Créer la ligne'">
                        </button>
                        <button type="button" @click="resetForm" x-show="editMode"
                                class="px-3 bg-slate-950 hover:bg-slate-800 text-slate-400 rounded text-xs border border-slate-800">
                            Annuler
                        </button>
                    </div>
                </form>
            </div>

            <!-- Tableau des lignes -->
            <div class="overflow-hidden rounded-2xl border border-line bg-panel shadow-panel lg:col-span-2">
                <div class="p-4 border-b border-slate-800">
                    <h3 class="text-xs font-medium text-slate-400 uppercase tracking-wider">Itinéraires configurés</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-950 text-slate-400 uppercase text-[10px] tracking-wider border-b border-slate-800">
                        <tr>
                            <th class="p-3">Trajet</th>
                            <th class="p-3">Prix de base</th>
                            <th class="p-3">Distance</th>
                            <th class="p-3 text-right">Actions</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800 text-slate-300">
                        <template x-for="ligne in lignes" :key="ligne.id">
                            <tr>
                                <td class="p-3 font-medium text-white" x-text="ligne.ville_depart + ' -> ' + ligne.ville_arrivee"></td>
                                <td class="p-3 text-emerald-400" x-text="ligne.prix_base + ' FCFA'"></td>
                                <td class="p-3 text-slate-400" x-text="ligne.distance_km ? ligne.distance_km + ' km' : 'N/A'"></td>
                                <td class="p-3 text-right space-x-2">
                                    <button @click="editLigne(ligne)" class="text-slate-400 hover:text-white transition-colors">Modifier</button>
                                    <button @click="deleteLigne(ligne.id)" class="text-red-400 hover:text-red-300 transition-colors">Supprimer</button>
                                </td>
                            </tr>
                        </template>
                        <template x-if="lignes.length === 0">
                            <tr>
                                <td colspan="4" class="p-4 text-center text-slate-500">Aucune ligne configurée.</td>
                            </tr>
                        </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <script>
        function ligneManager() {
            return {
                lignes: [],
                form: { id: null, ville_depart: '', ville_arrivee: '', prix_base: '', distance_km: '' },
                editMode: false,
                loading: false,
                message: '',
                success: false,

                requestHeaders() {
                    return {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    };
                },

                async loadLignes() {                    try {
                        const res = await fetch('/api/v1/admin/lignes', {
                            headers: this.requestHeaders()
                        });
                        if (res.ok) {
                            const json = await res.json();
                            this.lignes = Array.isArray(json) ? json : (json.data || []);
                        }
                    } catch (err) {
                        console.error("Erreur chargement lignes", err);
                    }
                },

                editLigne(ligne) {
                    this.editMode = true;
                    this.form = { ...ligne };
                },

                resetForm() {
                    this.editMode = false;
                    this.form = { id: null, ville_depart: '', ville_arrivee: '', prix_base: '', distance_km: '' };
                },

                async saveLigne() {
                    this.loading = true;
                    this.message = '';                    const url = this.editMode ? `/api/v1/admin/lignes/${this.form.id}` : '/api/v1/admin/lignes';
                    const method = this.editMode ? 'PUT' : 'POST';

                    try {
                        const res = await fetch(url, {
                            method: method,
                            headers: { ...this.requestHeaders(), 'Content-Type': 'application/json' },
                            body: JSON.stringify(this.form)
                        });

                        const data = await res.json();

                        if (res.ok && data.success) {
                            this.success = true;
                            this.message = this.editMode ? 'Ligne mise à jour avec succès.' : 'Ligne créée avec succès.';
                            this.resetForm();
                            this.loadLignes();
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

                async deleteLigne(id) {
                    if (!confirm('Confirmer la suppression de cette ligne ?')) return;                    try {
                        const res = await fetch(`/api/v1/admin/lignes/${id}`, {
                            method: 'DELETE',
                            headers: this.requestHeaders()
                        });
                        const data = await res.json();
                        if (res.ok && data.success) {
                            this.success = true;
                            this.message = 'Ligne supprimée avec succès.';
                            this.loadLignes();
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
