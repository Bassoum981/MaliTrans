@extends('layouts.admin')

@section('content')
    <div x-data="userManager()" x-init="loadUsers()" class="space-y-6">

        <div>
            <h2 class="text-lg font-semibold text-white tracking-tight">Gestion des employés</h2>
            <p class="text-slate-500 text-xs mt-0.5">Création et consultation des comptes du personnel</p>
        </div>

        <template x-if="message">
            <div :class="success ? 'bg-emerald-950/30 border-emerald-900 text-emerald-400' : 'bg-red-950/30 border-red-900 text-red-400'" class="p-3 rounded border text-xs" x-text="message"></div>
        </template>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Formulaire de création -->
            <div class="bg-slate-900 border border-slate-800 p-6 rounded-md h-fit">
                <h3 class="text-xs font-medium text-slate-400 uppercase tracking-wider mb-4">Nouveau compte</h3>
                <form @submit.prevent="createUser" class="space-y-4">
                    <div>
                        <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-wider mb-1">Nom complet</label>
                        <input type="text" x-model="form.name" placeholder="Ousmane Guichet" required
                               class="w-full bg-slate-950 border border-slate-800 rounded px-3 py-2 text-xs text-white focus:outline-none focus:border-slate-600">
                    </div>

                    <div>
                        <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-wider mb-1">Email</label>
                        <input type="email" x-model="form.email" placeholder="employe@malitrans.ml" required
                               class="w-full bg-slate-950 border border-slate-800 rounded px-3 py-2 text-xs text-white focus:outline-none focus:border-slate-600">
                    </div>

                    <div>
                        <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-wider mb-1">Téléphone</label>
                        <input type="text" x-model="form.telephone" placeholder="+22371112233" required
                               class="w-full bg-slate-950 border border-slate-800 rounded px-3 py-2 text-xs text-white focus:outline-none focus:border-slate-600">
                    </div>

                    <div>
                        <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-wider mb-1">Mot de passe</label>
                        <input type="password" x-model="form.password" placeholder="••••••••" required
                               class="w-full bg-slate-950 border border-slate-800 rounded px-3 py-2 text-xs text-white focus:outline-none focus:border-slate-600">
                    </div>

                    <div>
                        <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-wider mb-1">Rôle</label>
                        <select x-model="form.role" required
                                class="w-full bg-slate-950 border border-slate-800 rounded px-3 py-2 text-xs text-white focus:outline-none focus:border-slate-600">
                            <option value="guichetier">Guichetier</option>
                            <option value="chauffeur">Chauffeur</option>
                            <option value="admin">Administrateur</option>
                        </select>
                    </div>

                    <button type="submit" :disabled="loading"
                            class="w-full bg-slate-800 hover:bg-slate-700 text-white font-medium py-2.5 rounded text-xs transition-colors border border-slate-700 disabled:opacity-50">
                        <span x-show="!loading">Créer le compte</span>
                        <span x-show="loading">Traitement...</span>
                    </button>
                </form>
            </div>

            <!-- Tableau des employés -->
            <div class="lg:col-span-2 bg-slate-900 border border-slate-800 rounded-md overflow-hidden">
                <div class="p-4 border-b border-slate-800">
                    <h3 class="text-xs font-medium text-slate-400 uppercase tracking-wider">Personnel enregistré</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-950 text-slate-400 uppercase text-[10px] tracking-wider border-b border-slate-800">
                        <tr>
                            <th class="p-3">Nom</th>
                            <th class="p-3">Email</th>
                            <th class="p-3">Téléphone</th>
                            <th class="p-3">Rôle</th>
                            <th class="p-3 text-right">Actions</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800 text-slate-300">
                        <template x-for="user in users" :key="user.id">
                            <tr>
                                <td class="p-3 font-medium text-white" x-text="user.name"></td>
                                <td class="p-3 text-slate-400" x-text="user.email"></td>
                                <td class="p-3 text-slate-400" x-text="user.telephone"></td>
                                <td class="p-3">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-slate-800 text-slate-200 border border-slate-700 uppercase" x-text="user.role"></span>
                                </td>
                                <td class="p-3 text-right">
                                    <button @click="deleteUser(user.id)" class="text-red-400 hover:text-red-300 transition-colors">Supprimer</button>
                                </td>
                            </tr>
                        </template>
                        <template x-if="users.length === 0">
                            <tr>
                                <td colspan="5" class="p-4 text-center text-slate-500">Aucun employé enregistré.</td>
                            </tr>
                        </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <script>
        function userManager() {
            return {
                users: [],
                form: { name: '', email: '', telephone: '', password: '', role: 'guichetier' },
                loading: false,
                message: '',
                success: false,

                async loadUsers() {                    try {
                        const res = await fetch('/api/v1/admin/users', {
                            headers: { 'Accept': 'application/json' }
                        });
                        if (res.ok) {
                            const json = await res.json();
                            this.users = Array.isArray(json) ? json : (json.data || []);
                        }
                    } catch (err) {
                        console.error("Erreur chargement utilisateurs", err);
                    }
                },

                async createUser() {
                    this.loading = true;
                    this.message = '';
                    try {
                        const res = await fetch('/api/v1/admin/users', {
                            method: 'POST',
                            headers: {                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(this.form)
                        });

                        const data = await res.json();

                        if (res.ok && data.success) {
                            this.success = true;
                            this.message = `Compte [${this.form.role}] créé avec succès.`;
                            this.form = { name: '', email: '', telephone: '', password: '', role: 'guichetier' };
                            this.loadUsers();
                        } else {
                            this.success = false;
                            this.message = data.message || 'Erreur lors de la création.';
                        }
                    } catch (err) {
                        this.success = false;
                        this.message = 'Erreur réseau ou serveur.';
                    } finally {
                        this.loading = false;
                    }
                },

                async deleteUser(id) {
                    if (!confirm('Confirmer la suppression de cet employé ?')) return;                    try {
                        const res = await fetch(`/api/v1/admin/users/${id}`, {
                            method: 'DELETE',
                            headers: { 'Accept': 'application/json' }
                        });
                        const data = await res.json();
                        if (res.ok && data.success) {
                            this.success = true;
                            this.message = 'Employé supprimé avec succès.';
                            this.loadUsers();
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
