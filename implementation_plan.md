# Fusion des projets Rayen + WEB en un seul projet Green-Bite MVC

## Contexte

Deux projets PHP MVC séparés doivent être fusionnés dans `c:\xampp\htdocs\Green-Bite` :

| Projet | Base de données | Fonctionnalités | Problème clé |
|--------|----------------|-----------------|--------------|
| **Rayen/** | `projetwebnova` (table `users`) | Auth, login, logout, inscription, profil, CAPTCHA, Google OAuth, gestion users admin | Tous les chemins utilisent `/projetwebnova/...` |
| **WEB/** | `greenbite` (tables `produits`, `commandes`, `avis`, etc.) | Produits, commandes, panier, checkout, reviews, chatbot, barcode scanner | `user_id` saisi **manuellement** dans les formulaires |

> [!IMPORTANT]
> **Problème critique** : Les deux projets utilisent des bases de données différentes (`projetwebnova` vs `greenbite`) et des chemins complètement différents. Le WEB n'a aucun système d'authentification — les user_id sont tapés manuellement dans les champs de formulaire.

## Objectifs de la fusion

1. **Une seule base de données** : `greenbite` — importer la table `users` + tables associées dedans
2. **Un seul point d'entrée** : `index.php` à la racine de Green-Bite
3. **Une seule config DB** : `config/database.php`
4. **Authentification intégrée** : L'utilisateur connecté via Rayen est automatiquement utilisé partout
5. **Sécurité** : Empêcher un utilisateur d'accéder aux données d'un autre

---

## Proposed Changes

### Structure MVC finale

```
Green-Bite/
├── index.php                    ← Point d'entrée unique
├── config/
│   ├── database.php             ← Config DB unifiée (greenbite)
│   └── stripe.php               ← Config Stripe (copié de WEB)
├── Controller/
│   ├── bootstrap.php            ← Bootstrap unifié (session, helpers, auth)
│   ├── UserRepository.php       ← Repository users (de Rayen)
│   ├── auth.php                 ← Auth controller (de Rayen, chemins mis à jour)
│   ├── user.php                 ← User controller (de Rayen, chemins mis à jour)
│   ├── verify-email.php         ← Verification email (de Rayen)
│   ├── google-callback.php      ← Google OAuth (de Rayen)
│   ├── captcha-ajax.php         ← CAPTCHA AJAX (de Rayen)
│   ├── captcha-config.php       ← CAPTCHA config (de Rayen)
│   ├── captcha-sound.php        ← CAPTCHA audio (de Rayen)
│   ├── captcha.php              ← CAPTCHA image (de Rayen)
│   ├── ProductController.php    ← Produits (de WEB)
│   ├── OrderController.php      ← Commandes (de WEB, modifié pour session)
│   ├── ReviewController.php     ← Avis (de WEB)
│   └── ChatBotController.php    ← ChatBot (de WEB)
├── Model/
│   ├── User.php                 ← User model (de Rayen)
│   ├── Product.php              ← Product model (de WEB)
│   ├── Order.php                ← Order model (de WEB)
│   ├── Review.php               ← Review model (de WEB)
│   └── ChatBot.php              ← ChatBot model (de WEB)
├── View/
│   ├── auth.php                 ← Page login/register (de Rayen)
│   ├── style.css                ← CSS Rayen (auth + admin users)
│   ├── front-office/
│   │   ├── profile.php          ← Profil utilisateur (de Rayen)
│   │   ├── index.php            ← Catalogue produits (de WEB)
│   │   ├── product.php          ← Fiche produit (de WEB)
│   │   ├── cart.php             ← Panier (de WEB)
│   │   ├── checkout.php         ← Checkout (de WEB)
│   │   ├── order-history.php    ← Historique commandes (de WEB)
│   │   ├── chatbot.php          ← ChatBot (de WEB)
│   │   ├── barcode-scanner.php  ← Scanner (de WEB)
│   │   ├── includes/            ← Sous-includes front
│   │   └── ...                  ← Autres pages front (ai-hub, etc.)
│   ├── back-office/
│   │   ├── dashboard.php        ← Dashboard admin (de WEB)
│   │   ├── products.php         ← Admin produits (de WEB)
│   │   ├── orders.php           ← Admin commandes (de WEB)
│   │   ├── users.php            ← Admin users (de Rayen)
│   │   ├── reviews_moderation.php ← Modération avis (de WEB)
│   │   └── ...
│   ├── assets/                  ← Assets (de WEB)
│   └── includes/
│       ├── bootstrap.php        ← Bootstrap views (inclut Controller/bootstrap.php)
│       └── chatbot_widget.php   ← Widget chatbot
├── api/                         ← API endpoints (de WEB)
│   ├── product.php
│   ├── barcode_search.php
│   ├── checkout/create_order.php ← MODIFIÉ pour utiliser session
│   ├── orders/history.php        ← MODIFIÉ pour utiliser session
│   ├── reviews/
│   ├── chatbot/
│   └── payment/
├── uploads/                     ← Uploads users (de Rayen)
└── .env.example                 ← Variables d'environnement
```

---

### Composant 1 : Base de données unifiée

#### [NEW] SQL migration script

- Créer les tables `users`, `password_resets`, `email_verifications` dans la base `greenbite`
- Ajouter les clés étrangères de `commandes.id_utilisateur` vers `users.id`
- Seed admin user

#### [MODIFY] [database.php](file:///c:/xampp/htdocs/Green-Bite/config/database.php)

- DB_NAME = `'greenbite'` (la base existante du WEB)
- Fusionner les deux configs : garder `getPdo()` singleton + `Database::connection()` class + initialisation auto des tables users
- Ajouter la config Google OAuth

---

### Composant 2 : Bootstrap unifié

#### [NEW] [bootstrap.php](file:///c:/xampp/htdocs/Green-Bite/Controller/bootstrap.php)

Fusion de `Rayen/Controller/bootstrap.php` + `WEB/View/includes/bootstrap.php` :
- Session management
- Helpers : `redirect()`, `setFlash()`, `getFlash()`, `setFormState()`, `consumeFormState()`, `h()`
- Auth helpers : `requireAuth()`, `requireAdmin()`, `getLoggedUserId()`
- Validation : `isValidPersonName()`, `isValidEmailAddress()`, `isStrongPassword()`
- Photo upload : `storeUploadedUserPhoto()`
- Email : tokens, SMTP functions
- **Nouveau** : `getLoggedUserId(): int` — retourne l'ID de l'utilisateur connecté depuis `$_SESSION['user']['id']`
- Tous les chemins redirigent vers `/Green-Bite/...` au lieu de `/projetwebnova/...`

#### [NEW] [View/includes/bootstrap.php](file:///c:/xampp/htdocs/Green-Bite/View/includes/bootstrap.php)

- Inclut `Controller/bootstrap.php`
- Fonction `h()` pour HTML escaping

---

### Composant 3 : Contrôleurs Rayen (auth + users) — Mise à jour des chemins

#### [MODIFY] Tous les controllers Rayen

Changement global : **`/projetwebnova/`** → **`/Green-Bite/`**

- `auth.php` : Toutes les redirections
- `user.php` : Toutes les redirections
- `verify-email.php` : Redirections
- `google-callback.php` : Redirections + callback URI

---

### Composant 4 : Intégration session → commandes/produits (CHANGEMENT CRITIQUE)

> [!CAUTION]
> C'est le changement le plus important. Tous les endroits où `user_id` est saisi manuellement doivent utiliser `$_SESSION['user']['id']`.

#### [MODIFY] [api/checkout/create_order.php](file:///c:/xampp/htdocs/Green-Bite/api/checkout/create_order.php)

- Supprimer `$body['id_utilisateur']` du corps de la requête
- Lire `$_SESSION['user']['id']` à la place via `session_start()` + vérification auth
- Si pas connecté → erreur 401

#### [MODIFY] [api/orders/history.php](file:///c:/xampp/htdocs/Green-Bite/api/orders/history.php)

- Supprimer le paramètre GET `id_user`
- Utiliser `$_SESSION['user']['id']` automatiquement
- Sécuriser : un user ne peut voir que SES commandes

#### [MODIFY] [View/front-office/checkout.php](file:///c:/xampp/htdocs/Green-Bite/View/front-office/checkout.php)

- **Supprimer** le champ "ID Utilisateur" du formulaire
- Ajouter `requireAuth()` en haut de page — rediriger vers login si pas connecté
- Le JS envoie la commande SANS `id_utilisateur` (le backend le prend de la session)
- Afficher le nom de l'utilisateur connecté dans la navbar

#### [MODIFY] [View/front-office/cart.php](file:///c:/xampp/htdocs/Green-Bite/View/front-office/cart.php)

- **Supprimer** le champ "Votre ID Utilisateur" dans l'onglet historique
- Charger l'historique automatiquement depuis la session
- Ajouter auth guard

#### [MODIFY] [View/front-office/order-history.php](file:///c:/xampp/htdocs/Green-Bite/View/front-office/order-history.php)

- **Supprimer** le formulaire "Entrez votre ID utilisateur"
- Charger les commandes automatiquement via `$_SESSION['user']['id']`
- Auth guard

#### [MODIFY] [ReviewController.php](file:///c:/xampp/htdocs/Green-Bite/Controller/ReviewController.php)

- `addReview()` : Utiliser `$_SESSION['user']['id']` au lieu du champ `id_utilisateur` dans le POST

---

### Composant 5 : Navbar unifiée

#### [MODIFY] Toutes les vues front-office

Créer une navbar cohérente qui :
- Affiche les initiales de l'utilisateur connecté (ou lien "Se connecter")
- Lien Profil → `profile.php`
- Lien Panier → `cart.php`
- Lien Déconnexion (formulaire POST vers `Controller/auth.php`)
- Si admin → lien Dashboard Admin
- Si pas connecté → bouton "Se connecter" vers `View/auth.php`

#### [MODIFY] Toutes les vues back-office

- Ajouter lien vers la gestion des utilisateurs dans la sidebar admin
- Auth guard admin sur toutes les pages back-office

---

### Composant 6 : Point d'entrée unique

#### [NEW] [index.php](file:///c:/xampp/htdocs/Green-Bite/index.php)

```php
<?php
// Redirige vers la page d'authentification ou le catalogue
require_once __DIR__ . '/Controller/bootstrap.php';
if (isset($_SESSION['user'])) {
    header('Location: /Green-Bite/View/front-office/index.php');
} else {
    header('Location: /Green-Bite/View/auth.php');
}
exit;
```

---

### Composant 7 : Fichiers CSS

- Copier `Rayen/View/style.css` comme base pour auth + admin users
- `WEB/View/style.css` reste la feuille de styles principale pour produits/commandes
- Fusionner les deux en un seul fichier ou garder les deux et les inclure selon le contexte

---

## Résumé des fichiers

### Fichiers à COPIER (de Rayen/ vers la racine Green-Bite/) :
- `Controller/bootstrap.php` (fusionné avec WEB bootstrap)
- `Controller/auth.php`, `user.php`, `UserRepository.php`, `verify-email.php`, `google-callback.php`, `captcha-*.php`
- `Model/User.php`
- `View/auth.php` → `View/auth.php`
- `View/front-office/profile.php`
- `View/back-office/users.php`
- `View/style.css`
- `.env.example`
- `uploads/` (dossier)

### Fichiers à COPIER (de WEB/ vers la racine Green-Bite/) :
- `Controller/ProductController.php`, `OrderController.php`, `ReviewController.php`, `ChatBotController.php`
- `Model/Product.php`, `Order.php`, `Review.php`, `ChatBot.php`
- `config/stripe.php`
- `View/front-office/*`, `View/back-office/*`, `View/assets/*`, `View/includes/*`, `View/style.css`
- `View/` SQL migration files
- `api/*`

### Fichiers à MODIFIER après copie :
1. **Tous les fichiers Rayen** : Remplacer `/projetwebnova/` → `/Green-Bite/`
2. **checkout.php, cart.php, order-history.php** : Supprimer champs user_id manuels
3. **api/checkout/create_order.php** : Utiliser session au lieu de POST user_id
4. **api/orders/history.php** : Utiliser session
5. **Toutes les vues** : Mettre à jour les chemins `require_once` et liens
6. **database.php** : Unifier les deux configs

### Fichiers à NE PAS copier :
- `Rayen/index.php` (remplacé par nouveau index.php)
- `Rayen/Controller/database.php` (remplacé par config/database.php unifiée)
- `WEB/config/database.php` (remplacé)

---

## Open Questions

> [!IMPORTANT]
> 1. **CSS** : Les deux projets ont des fichiers `style.css` différents. Voulez-vous que je les fusionne en un seul fichier CSS, ou que je garde les deux séparément (un pour auth/users, un pour produits/commandes) ?
> 2. **Base de données** : La table `users` de Rayen est dans `projetwebnova`. Je vais créer cette table dans `greenbite`. Les données existantes dans `projetwebnova.users` doivent-elles être migrées ? Ou on repart avec juste l'admin seed ?
> 3. **Google OAuth** : Le fichier `.env` contient des clés Google. Je vais garder le système `.env`. Avez-vous un fichier `.env` existant à la racine de Green-Bite ?

---

## Verification Plan

### Automated Tests
1. Démarrer Apache/MySQL via XAMPP
2. Accéder à `http://localhost/Green-Bite/` → doit rediriger vers la page auth
3. Tester login avec admin (`admin@greenbit.local`)
4. Après login → accéder au catalogue produits
5. Ajouter un produit au panier et vérifier que le checkout n'a plus de champ "ID Utilisateur"
6. Passer une commande → vérifier qu'elle est liée à l'utilisateur connecté
7. Voir l'historique des commandes → automatiquement filtré par l'utilisateur
8. Tester le back-office admin (users + produits + commandes + reviews)

### Manual Verification
- Vérifier que toutes les pages redirigent vers login quand non authentifié
- Vérifier qu'un utilisateur ne peut pas voir les commandes d'un autre
- Vérifier que le profil fonctionne correctement
- Tester inscription avec CAPTCHA
