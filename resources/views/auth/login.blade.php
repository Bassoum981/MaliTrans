<!DOCTYPE html>
<html lang="fr" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MaliTrans — Connexion</title>
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
                            900: '#0F172A',
                            800: '#1E293B',
                            700: '#334155'
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-slate-900 text-slate-100 font-sans antialiased flex items-center justify-center min-h-screen">

<div class="w-full max-w-md p-6 bg-slate-800 rounded-2xl border border-slate-700 shadow-xl" x-data="loginApp()">

    <!-- En-tête -->
    <div class="text-center mb-8">
        <div class="inline-flex w-12 h-12 bg-blue-600 rounded-xl items-center justify-center font-bold text-2xl text-white mb-3 shadow-lg shadow-blue-600/30">
            MT
        </div>
        <h1 class="text-xl font-bold tracking-tight">Connexion à MaliTrans</h1>
        <p class="text-xs text-slate-400 mt-1">Plateforme de gestion et billetterie</p>
    </div>

    <!-- Alerte d'erreur -->
    <template x-if="errorMessage">
        <div class="mb-4 p-3 bg-red-500/10 border border-red-500/30 text-red-400 text-xs rounded-lg text-center" x-text="errorMessage"></div>
    </template>

    <!-- Formulaire -->
    <form @submit.prevent="login" class="space-y-4">
        <div>
            <label class="block text-xs font-medium text-slate-300 mb-1">Adresse Email</label>
            <input type="email5" x-model="form.email" placeholder="ex: admin@malitrans.ml" required
                   class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3.5 py-2.5 text-sm text-white focus:outline-none focus:border-blue-500 transition">
        </div>

        <div>
            <label class="block text-xs font-medium text-slate-300 mb-1">Mot de passe</label>
            <input type="password" x-model="form.password" placeholder="••••••••" required
                   class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3.5 py-2.5 text-sm text-white focus:outline-none focus:border-blue-500 transition">
        </div>

        <button type="submit" :disabled="loading"
                class="w-full bg-blue-600 hover:bg-blue-500 text-white font-medium py-2.5 rounded-lg text-sm transition shadow-lg shadow-blue-600/20 disabled:opacity-50">
            <span x-show="!loading">Se connecter</span>
            <span x-show="loading">Connexion en cours...</span>
        </button>
    </form>

    <!-- Aide aux identifiants (Pratique pour tester) -->
    <div class="mt-8 pt-4 border-t border-slate-700/60 text-xs text-slate-400 space-y-1">
        <p class="font-semibold text-slate-300">Comptes de test par défaut :</p>
        <p>👑 Admin : <code class="text-blue-400">admin@malitrans.ml</code> / <code class="text-slate-200">AdminSecurePassword2026!</code></p>
        <p>🎫 Guichet : <code class="text-blue-400">guichet@malitrans.ml</code> / <code class="text-slate-200">password123</code></p>
    </div>
</div>

<script>
    function loginApp() {
        return {
            form: {
                email: '',
                password: ''
            },
            loading: false,
            errorMessage: '',

            async login() {
                this.loading = true;
                this.errorMessage = '';

                try {
                    const response = await fetch('/api/v1/auth/login', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(this.form)
                    });

                    const data = await response.json();

                    // 🔍 Ouvre F12 > Console pour voir exactement ce que ton API renvoie
                    console.log("Réponse brute de l'API :", data);

                    // Récupération souple du token et de l'utilisateur (selon ton format de retour)
                    const token = data.token || (data.data && data.data.token);
                    const user = data.user || (data.data && data.data.user);

                    if (response.ok && token) {
                        // On harmonise la clé pour qu'elle soit lue partout facilement
                        localStorage.setItem('token_admin', token);
                        if (user && user.role) {
                            localStorage.setItem('user_role', user.role);
                        }

                        // Redirection forcée vers la page des bus admin
                        window.location.href = '/admin/buses';
                    } else {
                        this.errorMessage = data.message || 'Connexion réussie mais token introuvable dans la réponse.';
                    }
                } catch (err) {
                    console.error("Erreur catch :", err);
                    this.errorMessage = 'Erreur de connexion au serveur.';
                } finally {
                    this.loading = false;
                }
            }
        }
    }

</script>
</body>
</html>
