# NationsGlory Cards — Plateforme de cartes de collection IA

Plateforme web de cartes de collection pour l'univers Minecraft **NationsGlory**.
Les cartes (nom, description, statistiques, illustration) sont générées par une
**intelligence artificielle**, distribuées via des **packs à raretés pondérées**
(système gacha), collectionnées dans un inventaire et **échangées entre joueurs**
de manière sécurisée.

Projet réalisé en **Laravel 12 / PHP 8.2 / MySQL / Tailwind CSS**.

---

## Fonctionnalités

### Joueur
- Inscription / connexion / déconnexion (Laravel Breeze)
- Ouverture de packs avec tirage pondéré côté serveur et **animation de révélation**
- Inventaire filtrable (par type, par rareté) avec valeur totale de la collection
- Annuaire des joueurs et **profils publics**
- **Échanges sécurisés** : proposition, validation bilatérale, blocage des cartes
  engagées, transfert atomique, historique

### Administrateur
- Tableau de bord et statistiques
- CRUD des cartes + **génération par IA** (nom, description, stats, image)
- Types de cartes **dynamiques** (ajout / modification / suppression)
- CRUD des packs avec **éditeur de composition** et probabilités calculées en direct
- Gestion des joueurs : recherche, bannissement, consultation d'inventaire,
  suppression de cartes, attribution de packs

---

## Prérequis

- PHP >= 8.2 avec les extensions `pdo_mysql`, `gd`, `zip`, `intl`, `mbstring`, `openssl`
- Composer
- MySQL (ou MariaDB) — testé avec le MySQL fourni par XAMPP
- Node.js + npm (build des assets)

## Installation

```bash
# 1. Dépendances PHP et JS
composer install
npm install

# 2. Configuration
cp .env.example .env
php artisan key:generate

# 3. Base de données
#    Créez la base puis renseignez DB_* dans .env
#    (par défaut : nationsglory_cards, utilisateur root sans mot de passe)
mysql -u root -e "CREATE DATABASE nationsglory_cards CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 4. Migrations + données de démonstration
php artisan migrate:fresh --seed

# 5. Lien de stockage des images générées
php artisan storage:link

# 6. Build des assets
npm run build

# 7. Lancement
php artisan serve
```

L'application est disponible sur http://127.0.0.1:8000.

## Comptes de démonstration

| Rôle    | Email                       | Mot de passe |
|---------|-----------------------------|--------------|
| Admin   | admin@nationsglory.test     | password     |
| Joueur  | alice@nationsglory.test     | password     |
| Joueur  | bob@nationsglory.test       | password     |

Chaque compte démarre avec 6 packs à ouvrir.

---

## Génération IA — deux moteurs interchangeables

La génération IA est encapsulée derrière l'interface `CardGenerator`. Deux drivers
sont fournis, sélectionnables via `CARD_AI_DRIVER` dans le `.env` :

- **`fake`** (par défaut) — génération **locale, hors-ligne et gratuite**. Produit
  un nom, une description et une illustration PNG dessinée localement (GD), colorée
  selon la rareté. Permet de développer, tester et démontrer le projet **sans clé
  d'API ni coût**.
- **`openai`** — génération réelle : **GPT** pour le texte, **DALL-E** pour l'image.
  Renseignez alors `OPENAI_API_KEY` dans le `.env` et passez `CARD_AI_DRIVER=openai`.

> Les clés d'API ne vivent que dans le `.env`, jamais dans le code source. Tous les
> appels IA sont effectués côté serveur, et les images reçues sont stockées
> localement pour éviter les appels répétés.

## Tests

```bash
php artisan test
```

51 tests (SQLite en mémoire) couvrent les règles critiques : tirage pondéré,
atomicité et non-duplication des échanges, blocage des cartes, protections
d'accès administrateur, bornes des statistiques générées.

## Documentation

Le rapport technique détaillé (architecture, choix techniques, équilibrage
économique) se trouve dans [`docs/RAPPORT.md`](docs/RAPPORT.md).
