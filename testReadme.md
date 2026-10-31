cat << 'EOF' > TESTS_README.md
# MaliTrans Backend - Guide des Tests et Flux Fonctionnels

Ce document récapitule l'architecture des tests et le déroulement du flux fonctionnel automatisé pour le backend **MaliTrans**.

---

## 🚀 1. Résumé des Actions Réalisées

1. **Configuration de la Base de Données et Migrations** :
    - Schéma relationnel complet (`users`, `buses`, `lignes`, `trajets`, `billets`, `positions_gps`).
    - Intégration de Laravel Sanctum pour l'authentification par token (`HasApiTokens`).
2. **Logique Métier (`TicketService`)** :
    - Gestion atomique de la vente de billets avec contrôle de la capacité maximale du bus en temps réel via des transactions DB.
    - Génération et hachage sécurisé des QR codes au format `MALITRANS-[HASH]-[TIMESTAMP]`.
3. **Contrôleurs API** :
    - `GuichetController` : Liste des trajets et émission de billets.
    - `DriverController` : Validation de billets par scan QR code et suivi GPS.
4. **Suite de Tests Fonctionnels (`MaliTransFlowTest`)** :
    - **Scénario 1** : Un guichetier consulte la liste des trajets disponibles (`GET /api/v1/guichet/trajets`).
    - **Scénario 2** : Un guichetier émet un billet (`POST /api/v1/guichet/trajets/{trajet}/billets`) ➔ Un chauffeur valide le billet par son hash QR (`POST /api/v1/driver/trajets/{trajet}/valider-billet`) ➔ Vérification du rejet de la double validation (code HTTP 422).

---

## 🧪 2. Commandes Utiles

Pour exécuter la suite de tests et valider le bon fonctionnement du backend :

```bash
# Lancer l'ensemble des tests du projet
php artisan test

# Lancer uniquement les tests de flux MaliTrans
php artisan test tests/Feature/MaliTransFlowTest.php

# Réinitialiser la base de données de test et relancer
php artisan migrate:fresh --env=testing
php artisan test
