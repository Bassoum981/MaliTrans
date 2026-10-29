# 🚌 Mali_Trans

> **Système Intelligent de Billettique, Vente en Guichet et Suivi Télématique de Flotte de Transport Public au Mali.**

[![Laravel](https://img.shields.io/badge/Laravel-11.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.4-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://mysql.com)
[![License](https://img.shields.io/badge/License-MIT-green.style=for-the-badge)](#license)

---

## 📌 À propos du projet

Dans le cadre de la modernisation des transports publics et de l'acquisition récente d'une flotte de bus par l'État malien, **Mali_Trans** propose une plateforme numérique globale permettant de :
- Numériser la vente des billets à l'unité sans imposer d'abonnement lourd.
- Automatiser la billettique physique en guichet avec impression thermique de tickets à QR Code.
- Offrir aux chauffeurs une application mobile légère pour scanner/valider les titres de transport et émettre la position GPS du véhicule.
- Fournir aux usagers le suivi du bus en temps réel (*Live Tracking*) via une interface Web responsive.
- Donner aux gestionnaires un tableau de bord d'administration centralisé pour le suivi financier et l'exploitation de la flotte.

---

## 🏗️ Architecture du Système

Le projet est articulé autour de 4 composants principaux :

┌───────────────────────────────────┐
│     BACKEND API (Laravel 11)      │
│           Base MySQL 8.4          │
└─┬──────────────┬──────────────┬───┘
│              │              │
┌────────────────┘              │              └────────────────┐
▼                               ▼                               ▼
┌────────────────────┐          ┌────────────────────┐          ┌────────────────────┐
│  APP WEB CLIENT    │          │    APP GUICHET     │          │   APP CHAUFFEUR    │
│  (Voyageurs / Web) │          │ (Vente / Imprimante)│          │  (Mobile Flutter)  │
└────────────────────┘          └────────────────────┘          └────────────────────┘


1. **Application Web Client (Voyageurs) :** Recherche de trajet, achat de billet à l'unité (Mobile Money), génération de QR Code et suivi GPS sur carte.
2. **Application Guichet (Agents) :** Interface POS rapide pour encaissement en espèces et impression de ticket via protocole ESC/POS.
3. **Application Mobile Chauffeur :** Sélection du trajet, module de scan caméra pour validation anti-fraude des QR Codes, et émission GPS en arrière-plan.
4. **Back-Office Administrateur :** Gestion des bus, chauffeurs, lignes, tarifs et reporting financier.

---

## 🚀 Stack Technologique

* **Backend & API :** [Laravel 11](https://laravel.com) (PHP 8.3), [Laravel Sanctum](https://laravel.com/docs/sanctum) (Authentification API)
* **Base de données :** [MySQL 8.4](https://www.mysql.com)
* **Temps réel / WebSockets :** [Laravel Reverb](https://laravel.com/docs/reverb) / Pusher
* **Application Mobile :** [Flutter](https://flutter.dev) (Dart)
* **Frontend Web :** Laravel Blade, Tailwind CSS, Vue.js / Alpine.js
* **Cartographie :** Leaflet.js / OpenStreetMap

---

## 🗄️ Schéma de la Base de Données

```sql
-- Structure essentielle du SGBD MySQL (Mali_Trans)

CREATE TABLE buses (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    immatriculation VARCHAR(20) UNIQUE NOT NULL,
    capacite INT NOT NULL,
    statut ENUM('EN_SERVICE', 'EN_MAINTENANCE', 'HORS_SERVICE') DEFAULT 'EN_SERVICE',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE lignes (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    point_depart VARCHAR(100) NOT NULL,
    point_arrivee VARCHAR(100) NOT NULL,
    tarif_unitaire DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE trajets (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    bus_id BIGINT NOT NULL,
    chauffeur_id BIGINT NOT NULL,
    ligne_id BIGINT NOT NULL,
    heure_depart_prevue DATETIME NOT NULL,
    heure_depart_reelle DATETIME NULL,
    heure_arrivee_reelle DATETIME NULL,
    statut ENUM('PLANIFIE', 'EN_COURS', 'TERMINE', 'ANNULE') DEFAULT 'PLANIFIE',
    FOREIGN KEY (bus_id) REFERENCES buses(id),
    FOREIGN KEY (ligne_id) REFERENCES lignes(id)
);

CREATE TABLE billets (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    code_qr_hash VARCHAR(255) UNIQUE NOT NULL,
    trajet_id BIGINT NOT NULL,
    type_vente ENUM('WEB', 'GUICHET') NOT NULL,
    prix DECIMAL(10, 2) NOT NULL,
    statut ENUM('PAYE', 'VALIDE', 'ANNULE') DEFAULT 'PAYE',
    date_validation DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (trajet_id) REFERENCES trajets(id)
);

CREATE TABLE positions_gps (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    trajet_id BIGINT NOT NULL,
    latitude DECIMAL(10, 8) NOT NULL,
    longitude DECIMAL(11, 8) NOT NULL,
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (trajet_id) REFERENCES trajets(id)
);
