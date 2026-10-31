# Documentation Technique Backend & Architecture MaliTrans

Ce document est destiné à tous les développeurs et collaborateurs travaillant sur le projet **MaliTrans**. Il regroupe la vision globale de l'architecture backend, le schéma de base de données, la logique métier centralisée, les spécifications des endpoints API ainsi que les procédures de test et de déploiement.

---

## 1. Vue d'Ensemble du Projet

**MaliTrans** est une plateforme de billetterie et de suivi de flotte de transport routier interurbain.

Le système se compose de trois axes principaux :
1. **Portail Web d'Administration & Guichet (Laravel / Blade / API REST)** :
    * Gestion de la flotte (bus, compagnies, lignes, tarifs).
    * Planification des trajets et affectation des chauffeurs.
    * Vente de tickets au guichet en station avec impression thermique.
2. **Backend API REST (Laravel 11+ & Sanctum)** :
    * Centralisation de la logique métier (validation de capacité, émission de QR codes unique, vérification).
    * Sécurisation des accès par jetons d'authentification (Sanctum) et rôles applicatifs.
3. **Application Mobile Conducteur (Flutter)** :
    * Consultation du trajet actif.
    * Validation en direct des billets passagers via le scanner de QR Code.
    * Émission continue de la géolocalisation GPS en tâche de fond.

---

## 2. Architecture de la Base de Données

Le schéma relationnel est conçu pour maintenir l'intégrité des données et optimiser les requêtes d'exploitation.

```
+----------------+        +-----------------+        +------------------+
|     lignes     |        |      buses      |        |      users       |
+----------------+        +-----------------+        +------------------+
| id             |<---+   | id              |<---+   | id               |<---+
| ville_depart   |    |   | immatriculation |    |   | name             |    |
| ville_arrivee  |    |   | capacite        |    |   | email            |    |
| distance_km    |    |   | statut          |    |   | telephone        |    |
| prix_base      |    |   +-----------------+    |   | role             |    |
+----------------+    |                          |   +------------------+    |
                      |                          |                           |
                      +-------+          +-------+                           |
                              |          |                                   |
                       +------|----------|------+                            |
                       |        trajets         |                            |
                       +------------------------+                            |
                       | id                     |                            |
                       | bus_id (FK)            |                            |
                       | ligne_id (FK)          |                            |
                       | chauffeur_id (FK) -----|----------------------------+
                       | heure_depart_prevue    |
                       | heure_depart_reelle    |
                       | heure_arrivee_reelle   |
                       | statut                 |
                       +------------------------+
                                   ^
                                   |
            +----------------------+----------------------+
            |                                             |
+------------------------+                    +------------------------+
|        billets         |                    |     positions_gps      |
+------------------------+                    +------------------------+
| id                     |                    | id                     |
| code_qr_hash (UNIQUE)  |                    | trajet_id (FK)         |
| trajet_id (FK)         |                    | latitude               |
| nom_passager           |                    | longitude              |
| telephone_passager     |                    | recorded_at            |
| numero_siege           |                    +------------------------+
| type_vente (WEB/GUICHET)|
| vendu_par (FK -> users)|
| prix                   |
| statut (PAYE/VALIDE)   |
| date_validation        |
+------------------------+
```

### Table des Rôles (`users.role`)
* **`admin`** : Accès complet à la gestion des bus, chauffeurs, lignes et statistiques.
* **`guichetier`** : Recherche de trajets et émission/impression de billets au guichet.
* **`chauffeur`** : Accès limité à l'application mobile pour la validation des QR codes et l'envoi de la position GPS.

---

## 3. Logique Métier : `TicketService`

Toute la logique critique liée à la réservation, l'émission et la validation des billets est centralisée dans la classe `App\Services\TicketService` afin d'éviter la dispersion de la logique métier dans les contrôleurs.

### A. Émission de Billet (`createTicket`)
```php
public function createTicket(Trajet $trajet, array $data): Billet
```
1. **Transaction Atomique (`DB::transaction`)** : Garantit qu'aucune réservation concurrente ne dépasse la capacité physique du bus.
2. **Vérification de la capacité** : Compte les billets actifs (`statut != 'ANNULE'`). Si le total atteint `bus.capacite`, une exception est levée.
3. **Génération du QR Hash** : Format sécurisé et unique :
   `MALITRANS-[STR_RANDOM_16]-[TIMESTAMP]` (ex: `MALITRANS-A8F9K2L1M4P6Q9Z3-1730245200`).

### B. Validation du Billet (`validateTicketByQrCode`)
```php
public function validateTicketByQrCode(string $qrHash, int $trajetId): Billet
```
Effectue les vérifications suivantes dans l'ordre :
1. **Existence** : Vérifie que le `code_qr_hash` existe en base.
2. **Correspondance du trajet** : S'assure que le billet correspond exactement au `trajet_id` en cours du chauffeur.
3. **Contrôle d'unicité d'utilisation** : Si `statut === 'VALIDE'`, l'accès est refusé ("Ce billet a déjà été utilisé").
4. **Annulation** : Si `statut === 'ANNULE'`, l'accès est refusé.
5. **Marquage** : Passe le statut à `'VALIDE'` et enregistre la date exacte de validation (`date_validation = now()`).

---

## 4. Documentation des Endpoints API

Toutes les requêtes API doivent inclure les en-têtes HTTP suivants :
```http
Accept: application/json
Authorization: Bearer <SANCTUM_TOKEN>
```

### A. Espace Guichet (`/api/v1/guichet`)

#### 1. Lister les trajets disponibles
* **HTTP Method** : `GET /api/v1/guichet/trajets`
* **Query Parameters** (optionnels) :
    * `date` (format `YYYY-MM-DD`)
    * `ligne_id` (entier)
* **Response (200 OK)** :
```json
{
  "success": true,
  "data": [
    {
      "id": 12,
      "heure_depart_prevue": "2026-11-01 08:00:00",
      "statut": "PLANIFIE",
      "bus": {
        "immatriculation": "AB-123-MD",
        "capacite": 50
      },
      "ligne": {
        "ville_depart": "Bamako",
        "ville_arrivee": "Sikasso",
        "prix_base": "8000.00"
      }
    }
  ]
}
```

#### 2. Émettre un billet
* **HTTP Method** : `POST /api/v1/guichet/trajets/{trajet}/billets`
* **Body (JSON)** :
```json
{
  "nom_passager": "Amadou Traoré",
  "telephone_passager": "+22370000000",
  "numero_siege": 14,
  "prix": 8000
}
```
* **Response (201 Created)** :
```json
{
  "success": true,
  "message": "Billet émis avec succès.",
  "data": {
    "id": 105,
    "code_qr_hash": "MALITRANS-X89K2P1L4M6Q9Z3B-1730245200",
    "nom_passager": "Amadou Traoré",
    "numero_siege": 14,
    "type_vente": "GUICHET",
    "statut": "PAYE",
    "prix": 8000
  }
}
```

---

### B. Espace Chauffeur / App Mobile (`/api/v1/driver`)

#### 1. Obtenir le trajet actif du chauffeur
* **HTTP Method** : `GET /api/v1/driver/trajet-actif`
* **Response (200 OK)** :
```json
{
  "success": true,
  "data": {
    "id": 12,
    "heure_depart_prevue": "2026-11-01 08:00:00",
    "statut": "PLANIFIE",
    "bus": { "immatriculation": "AB-123-MD", "capacite": 50 },
    "ligne": { "ville_depart": "Bamako", "ville_arrivee": "Sikasso" }
  }
}
```

#### 2. Valider un billet par scan QR Code
* **HTTP Method** : `POST /api/v1/driver/trajets/{trajet}/valider-billet`
* **Body (JSON)** :
```json
{
  "code_qr_hash": "MALITRANS-X89K2P1L4M6Q9Z3B-1730245200"
}
```
* **Response (200 OK)** :
```json
{
  "success": true,
  "message": "Billet valide. Bon voyage !",
  "data": {
    "id": 105,
    "nom_passager": "Amadou Traoré",
    "statut": "VALIDE",
    "date_validation": "2026-11-01 07:45:12"
  }
}
```
* **Response d'erreur (422 Unprocessable Entity)** :
```json
{
  "success": false,
  "message": "Ce billet a déjà été utilisé (déjà validé)."
}
```

#### 3. Transmettre les coordonnées GPS
* **HTTP Method** : `POST /api/v1/driver/trajets/{trajet}/gps`
* **Body (JSON)** :
```json
{
  "latitude": 12.639232,
  "longitude": -7.998234
}
```
* **Response (200 OK)** :
```json
{
  "success": true,
  "data": {
    "id": 5041,
    "trajet_id": 12,
    "latitude": 12.639232,
    "longitude": -7.998234,
    "recorded_at": "2026-11-01 08:15:00"
  }
}
```

---

## 5. Guide de Test Postman / curl

### Test d'émission et de validation (Flux Complet)

1. **Authentification** : Récupérer le token Bearer en se connectant (`POST /api/v1/auth/login`).
2. **Création du ticket (Guichet)** :
   ```bash
   curl -X POST http://localhost/api/v1/guichet/trajets/1/billets \
     -H "Authorization: Bearer <TOKEN>" \
     -H "Content-Type: application/json" \
     -H "Accept: application/json" \
     -d '{"nom_passager": "Ousmane Diallo", "numero_siege": 5}'
   ```
   *Copier la valeur de `code_qr_hash` retournée.*

3. **Validation du ticket (Chauffeur)** :
   ```bash
   curl -X POST http://localhost/api/v1/driver/trajets/1/valider-billet \
     -H "Authorization: Bearer <TOKEN>" \
     -H "Content-Type: application/json" \
     -H "Accept: application/json" \
     -d '{"code_qr_hash": "MALITRANS-XXXXX"}'
   ```

4. **Test de double validation (Doit échouer)** :
   Répéter la commande ci-dessus avec le même code QR. La réponse doit retourner un code HTTP `422` avec le message d'erreur d'unicité.

---

## 6. Recommandations pour l'Équipe Frontend & Mobile

1. **Impression Guichet** : Le payload de retour lors de la création d'un billet contient le `code_qr_hash`. L'interface web guichet doit utiliser une bibliothèque comme `qrcode.js` pour générer le code QR visuel au moment de l'impression sur l'imprimante thermique.
2. **Scanner App Mobile Flutter** : Utiliser un package comme `mobile_scanner` ou `qr_code_scanner`. Lors du scan, envoyer directement le contenu texte du QR Code vers l'endpoint de validation.
3. **Gestion Hors-Ligne (Mobile Chauffeur)** : Il est recommandé de conserver en cache local SQLite les billets validés en cas de perte momentanée du réseau cellulaire sur les axes routiers nationales.
