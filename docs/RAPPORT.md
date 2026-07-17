# Rapport technique — NationsGlory Cards

Plateforme web de cartes de collection générées par IA, avec système de packs à
raretés pondérées, inventaire, échanges sécurisés et administration complète.

---

## 1. Architecture générale

Le projet suit l'architecture **MVC** imposée par Laravel, avec une couche
**Services** dédiée à la logique métier complexe.

```
Requête HTTP
   │
   ▼
Routes (routes/web.php)  ──►  Middleware (auth, admin, not.banned)
   │
   ▼
Controllers (HTTP)  ──►  Services (métier)  ──►  Models (Eloquent)  ──►  MySQL
   │
   ▼
Views (Blade + Tailwind + Alpine.js)
```

### Découpage des responsabilités

| Couche          | Rôle                                                        |
|-----------------|-------------------------------------------------------------|
| **Controllers** | Validation des entrées HTTP, orchestration, réponses        |
| **Services**    | Règles métier réutilisables et testables hors HTTP          |
| **Models**      | Entités, relations, contraintes d'intégrité, invariants     |
| **Views**       | Rendu Blade, interactions légères via Alpine.js             |

Les **Services** sont le cœur du projet. Ils sont volontairement découplés de la
requête HTTP : ils reçoivent des modèles et renvoient des modèles ou lèvent des
exceptions. Cela garantit deux choses exigées par le cahier des charges :

1. La logique sensible (tirage, échange) s'exécute **uniquement côté serveur**.
2. Elle est **testable unitairement**, sans passer par le navigateur.

Services implémentés :

- `WeightedDrawService` — tirage aléatoire pondéré générique.
- `PackOpeningService` — ouverture d'un pack (transaction atomique).
- `TradeService` — cycle de vie complet d'un échange sécurisé.
- `CardGeneration\*` — génération IA (interface + drivers + composer).

---

## 2. Stack technique et justifications

| Composant         | Choix                        | Justification                                              |
|-------------------|------------------------------|-----------------------------------------------------------|
| Backend           | PHP 8.2 / **Laravel 12**     | Imposé ; ORM Eloquent, migrations, validation, transactions |
| Base de données   | **MySQL** (utf8mb4)          | Relationnel, transactions ACID nécessaires aux échanges   |
| Authentification  | **Laravel Breeze** (Blade)   | Léger, complet (inscription/connexion/reset), intégré Tailwind |
| Frontend          | **Blade + Tailwind CSS 3**   | Rendu serveur simple, responsive, cohérent                |
| Interactions      | **Alpine.js** (fourni Breeze)| Sélection de cartes, révélation, probabilités en direct   |
| Génération IA     | OpenAI (GPT + DALL-E) / fake | Driver échangeable via configuration                      |
| Stockage images   | **Laravel Storage** (disk public) | Images générées servies via lien symbolique          |

---

## 3. Modèle de données

### Schéma relationnel

```
users ──< user_cards >── cards ──> card_types
  │           │            │
  │           │            └──────> rarities
  │           │
  │           └── locked_by_trade_id ──> trades
  │
  ├──< user_packs >── packs ──< pack_card >── cards
  ├──< pack_openings
  ├──< trades (sender_id / receiver_id)
  │
trades ──< trade_items >── user_cards
```

### Tables principales

| Table           | Rôle                                                            |
|-----------------|-----------------------------------------------------------------|
| `users`         | Comptes ; champs `is_admin`, `banned_at`, `ban_reason`          |
| `rarities`      | Raretés : couleur, poids par défaut, valeur de base, bornes de stats |
| `card_types`    | Types **dynamiques** ; `prompt_hint` injecté dans le prompt IA  |
| `cards`         | Catalogue : nom, description, type, rareté, valeur, stats, image |
| `packs`         | Packs : nombre de cartes par ouverture                          |
| `pack_card`     | Composition d'un pack : **poids** de chaque carte               |
| `user_cards`    | Inventaire : **un exemplaire par ligne**                        |
| `user_packs`    | Packs non ouverts détenus par un joueur                         |
| `pack_openings` | Historique des ouvertures                                       |
| `trades`        | Échanges : `sender`, `receiver`, `status`                       |
| `trade_items`   | Cartes engagées dans un échange (`offered` / `requested`)       |

### Décisions de modélisation notables

**Un exemplaire = une ligne dans `user_cards`.** On ne stocke pas une quantité
mais bien chaque exemplaire possédé. C'est indispensable pour pouvoir **bloquer
individuellement** une carte engagée dans un échange sans immobiliser les doublons
que le joueur possède par ailleurs. Les vues regroupent ensuite les exemplaires
par carte pour l'affichage (quantité `xN`).

**Blocage exclusif par colonne unique.** Une carte engagée porte
`user_cards.locked_by_trade_id`. Comme il s'agit d'une **seule** colonne (et non
d'une table de liaison), un exemplaire ne peut être engagé que dans un échange à
la fois : le blocage est exclusif **par construction**. Impossible de proposer
deux fois la même carte à deux joueurs différents.

**Contraintes d'intégrité au niveau base.** Les clés étrangères utilisent
`restrictOnDelete` là où une suppression détruirait des données possédées ou
engagées (on ne peut pas supprimer une carte encore détenue, ni un exemplaire
engagé dans un échange), et `cascadeOnDelete` pour les données strictement
dépendantes. L'unicité `(pack_id, card_id)` empêche qu'une carte soit listée deux
fois dans un pack.

---

## 4. Logique de jeu et équilibrage

> Le cahier des charges précise que l'équilibrage n'a pas de solution unique et
> que ce sont **la cohérence et la justification des choix** qui sont évaluées.
> Cette section documente donc chaque décision.

### 4.1 Distribution des raretés

Les valeurs indicatives du CDC (70 / 20 / 9 / 1) sont reprises comme **poids par
défaut** de chaque rareté :

| Rareté      | Couleur | Poids par défaut | Probabilité résultante |
|-------------|---------|------------------|------------------------|
| Commune     | Gris    | 70               | ~70 %                  |
| Rare        | Bleu    | 20               | ~20 %                  |
| Épique      | Violet  | 9                | ~9 %                   |
| Légendaire  | Doré    | 1                | ~1 %                   |

La probabilité réelle d'une carte dans un pack est
`poids_de_la_carte / somme_des_poids_du_pack`. Le poids par défaut de la rareté
n'est qu'une **valeur de confort** proposée à l'administrateur au moment
d'ajouter une carte à un pack ; il reste libre de l'ajuster carte par carte.

### 4.2 Grille de valeurs

La valeur croît **fortement** avec la rareté, pour qu'une légendaire (obtenue
environ une fois sur cent) vaille beaucoup plus qu'une pile de communes :

| Rareté      | Valeur de base | Ratio vs commune |
|-------------|----------------|------------------|
| Commune     | 10             | ×1               |
| Rare        | 40             | ×4               |
| Épique      | 120            | ×12              |
| Légendaire  | 500            | ×50              |

**Justification du ratio 1:50.** Espérance de valeur d'un tirage avec la
distribution 70/20/9/1 :

```
0,70×10 + 0,20×40 + 0,09×120 + 0,01×500 = 7 + 8 + 10,8 + 5 = 30,8
```

Aucune rareté ne domine seule l'espérance : les communes (7), les rares (8), les
épiques (10,8) et les légendaires (5) contribuent dans le même ordre de grandeur.
Le joueur ressent donc la valeur à chaque palier de rareté, et pas uniquement au
« jackpot » légendaire. Un ratio plus faible (ex. 1:10) rendrait les légendaires
anecdotiques ; un ratio beaucoup plus fort (ex. 1:500) écraserait toutes les
autres raretés. La valeur par défaut d'une carte est celle de sa rareté, mais
l'administrateur peut l'ajuster individuellement.

### 4.3 Contrôle de l'inflation

Le CDC met en garde : si trop de packs sont distribués, même les légendaires
perdent leur intérêt. Deux mécanismes limitent l'inflation :

1. **Un pack doit être possédé pour être ouvert.** Il n'existe pas d'ouverture
   « à volonté » : le joueur consomme un `user_pack`. La quantité de cartes en
   circulation est donc bornée par la quantité de packs distribués, elle-même
   contrôlée par l'administrateur.
2. **La distribution reste rare aux paliers élevés.** Avec 70 % de communes, la
   masse de cartes créées est surtout composée de communes de faible valeur ;
   la rareté effective des légendaires est préservée.

### 4.4 Règles d'échange

Le projet retient l'échange **libre** (sans contrainte de valeur équivalente),
mais **sécurisé et symétrique** : les deux joueurs doivent explicitement
accepter, et les cartes sont bloquées pendant la négociation. Ce choix privilégie
la liberté de jeu (dons, échanges déséquilibrés assumés entre amis) tout en
garantissant qu'aucun échange ne peut léser un joueur sans son accord. Une
contrainte de valeur équivalente pourrait être ajoutée dans `TradeService` sans
toucher au reste de l'application.

---

## 5. Sécurité et intégrité

### 5.1 Tirage anti-triche

Le tirage de packs est **exclusivement serveur**. `WeightedDrawService` utilise
`random_int()` (générateur cryptographique, non prédictible et non rejouable) et
n'a **aucune dépendance à la requête HTTP** : le client n'envoie jamais de poids
ni de résultat, il ne fait que demander l'ouverture. Les poids sont relus dans la
base au moment du tirage, jamais reçus du navigateur.

### 5.2 Atomicité des échanges et des ouvertures

Les opérations qui déplacent des cartes s'exécutent dans une **transaction**
(`DB::transaction`) avec **verrou pessimiste** (`lockForUpdate`) :

- **Ouverture de pack** : le `user_pack` est verrouillé avant tirage. Un double
  clic ou deux onglets concurrents ne peuvent pas consommer le même pack deux
  fois ni dupliquer les cartes. En cas d'erreur, rien n'est distribué et le pack
  n'est pas consommé.
- **Échange** : à l'acceptation, l'échange est verrouillé, la **possession réelle
  est re-vérifiée** (chaque exemplaire appartient toujours au bon joueur et est
  toujours bloqué par cet échange), puis les cartes changent de propriétaire.
  Tout-ou-rien : aucune carte ne peut être **dupliquée ou perdue**.

Ces invariants sont vérifiés par des tests automatisés, notamment
`test_accepting_transfers_ownership_without_duplication` qui contrôle que le
nombre total d'exemplaires reste constant après un échange.

### 5.3 Authentification, autorisation, bannissement

- Toutes les pages de jeu exigent l'authentification.
- Le back-office est protégé par le middleware `admin` (403 pour un joueur).
- Le middleware `not.banned`, appliqué globalement, **déconnecte immédiatement**
  tout joueur banni, même si sa session était déjà ouverte.
- `is_admin` est **exclu de l'assignation de masse** : impossible d'obtenir les
  droits admin via un champ de formulaire caché. Le flag n'est affecté
  qu'explicitement (seeder, code d'administration).
- Protection CSRF native de Laravel sur tous les formulaires ; mots de passe
  hachés (bcrypt).

---

## 6. Intégration de l'IA

### 6.1 Abstraction par interface

La génération est encapsulée derrière l'interface `CardGenerator` :

```
CardGenerator (interface)
├── FakeCardGenerator    → démo locale, hors-ligne, gratuite (image GD)
└── OpenAiCardGenerator  → GPT (texte) + DALL-E (image)
```

Le driver est résolu à partir de la configuration (`config/cards.php`, alimentée
par `CARD_AI_DRIVER`) dans `AppServiceProvider`. Ajouter un moteur (ex. Stable
Diffusion) revient à écrire une classe implémentant l'interface et à ajouter un
`case` — le reste de l'application est inchangé.

Cette abstraction a une vertu pédagogique et pratique : **tout le projet est
développable, testable et démontrable sans clé d'API ni coût**, tout en restant
prêt pour une génération réelle en changeant une seule variable d'environnement.

### 6.2 Construction dynamique du prompt

`PromptBuilder` construit le prompt d'image à partir des paramètres de la carte,
selon le patron suggéré par le CDC :

```
"A [rareté] [type] card for a medieval war game set in the NationsGlory universe,
 named [nom], digital art style, detailed illustration, fantasy theme"
```

Le `prompt_hint` propre à chaque type (configurable par l'administrateur) est
ajouté pour spécialiser le visuel.

### 6.3 Robustesse (CDC 9.3)

- Appels **côté serveur uniquement**.
- **Politique de réessai** (`retry`) sur les erreurs transitoires ; messages
  d'erreur explicites pour l'administrateur (clé invalide, quota dépassé,
  service indisponible) et **possibilité de relancer** la génération.
- Images reçues **stockées localement** (Laravel Storage) pour éviter les appels
  répétés.
- Clés d'API **uniquement dans le `.env`**.
- Statistiques tirées dans les **bornes de la rareté** pour rester cohérentes.

---

## 7. Tests

51 tests automatisés (PHPUnit, SQLite en mémoire) couvrent les règles critiques :

| Suite                     | Ce qui est vérifié                                             |
|---------------------------|---------------------------------------------------------------|
| `WeightedDrawServiceTest` | Distribution conforme aux poids, exclusion du poids nul, validation |
| `PackOpeningTest`         | Bon nombre de cartes, consommation d'un seul pack, refus si non possédé, traçabilité |
| `TradeServiceTest`        | Blocage, transfert atomique **sans duplication**, validation bilatérale, possession |
| `AdminAccessTest`         | Redirections, 403 pour les joueurs, bannissement effectif     |
| `CardGenerationTest`      | Driver fake en test, stats dans les bornes, stockage image, nom imposé |

Vérification complémentaire de bout en bout dans le navigateur : inscription,
ouverture de pack avec révélation, inventaire filtré, cycle d'échange complet
entre deux comptes, back-office administrateur.

---

## 8. Périmètre et pistes d'évolution

**Livré (minimum attendu du CDC) :** génération IA, packs à raretés pondérées,
inventaire filtrable, échanges sécurisés, profils publics, administration
complète, authentification, données de démonstration.

**Bonus livré :** animation d'ouverture de pack.

**Pistes d'évolution** (bonus non traités, mais que l'architecture accueille
facilement) : système de blocs (farming), timer de packs automatiques,
marketplace à monnaie virtuelle, niveaux/XP, quêtes, leaderboard. Les échanges à
valeur contrainte s'ajouteraient dans `TradeService` ; un leaderboard réutiliserait
`User::collectionValue()`.
