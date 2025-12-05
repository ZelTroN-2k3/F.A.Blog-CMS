# Mon CMS - v3.0.1

Ce projet est un Content Management System (CMS) développé en PHP procédural.

Initialement basé sur un tutoriel Udemy, il a été considérablement modernisé, sécurisé et étendu avec de nombreuses fonctionnalités personnalisées. L'interface d'administration est propulsée par le template **AdminLTE 3**, l'éditeur de texte par **Summernote**, et les tableaux par **DataTables**.

---

## 🚀 Nouveautés récentes (v3.0.1)

Cette version se concentre sur la stabilité et l'amélioration de l'expérience d'administration.

* **Gestion des bannissements :** Mise en place d'une nouvelle interface pour **personnaliser l'image d'arrière-plan** de la page publique "Banned".
* **Stabilité :** Refonte complète du système de déconnexion (`logout.php`) pour corriger les erreurs de redirection sur les serveurs de production.
* **Dashboard Admin :** Optimisation du widget "Raccourcis", désormais replié par défaut pour plus de clarté.

---

## ✨ Fonctionnalités Principales

### Front-office (Partie visible)
* Affichage des articles de blog avec pagination.
* Affichage des pages statiques.
* Système de commentaires sur les articles.
* Formulaire de contact fonctionnel.
* Barre latérale avec widgets (Recherche, Catégories, etc.).
* Design responsive (basé sur Bootstrap).

### Back-office (Administration)
* **Tableau de bord (Dashboard)** : Vue d'ensemble et statistiques.
* **Gestion des Articles** : CRUD complet avec éditeur riche (Summernote) et image à la une.
* **Gestion des Catégories** : Créer, modifier, supprimer.
* **Gestion des Pages** : Créer des pages statiques (ex: À propos).
* **Gestion des Utilisateurs** :
    * Inscription et connexion sécurisée.
    * Rôles : Admin et Subscriber.
    * Système de bannissement avec **page personnalisable**.
* **Modération des Commentaires** : Approuver ou supprimer les commentaires.
* **Médiathèque** : Gestion des fichiers uploadés.
* **Gestionnaire de Menu** : Outil drag-and-drop pour organiser le menu du site.
* **Gestion des Widgets** : Activer/désactiver les éléments de la sidebar.
* **Paramètres** : Configuration générale du site.

---

## 🛠️ Installation (Manuelle)

*Note : Un installateur automatique est en cours de développement.*

1.  **Base de données :**
    * Créez une base de données MySQL.
    * Importez le fichier `database.sql` (situé à la racine du projet) dans votre nouvelle base de données.

2.  **Configuration :**
    * Renommez le fichier `includes/db.php.example` en `includes/db.php` (si ce n'est pas déjà fait).
    * Modifiez `includes/db.php` avec vos identifiants BDD :
        ```php
        $db['db_host'] = "localhost";
        $db['db_user'] = "votre_user";
        $db['db_pass'] = "votre_pass";
        $db['db_name'] = "votre_db_name";
        ```

3.  **Accès :**
    * Accédez au site via votre navigateur.
    * Administration : `/admin`
    * Identifiants par défaut (À CHANGER !) : `admin@admin.com` / `password`

---

## État du projet

Projet fonctionnel et stable, en constante amélioration.
Des mises à jour régulières sont prévues pour ajouter des fonctionnalités, améliorer la sécurité et optimiser les performances.
---

# phpBlog v2.9 (Édition Modifiée)
phpBlog - News, Blog & Magazine CMS

## Améliorations (Version 2.9)

Cette version du phpBlog 2.4 a été largement améliorée pour inclure des fonctionnalités modernes, des correctifs de sécurité critiques et des optimisations de performance majeures.

---

### 🚀 Nouveautés Majeures (Post-v2.8.1)

Cette version introduit des modules de niveau professionnel pour la gestion de contenu et l'administration du site.

* **Gestionnaire de Mode Maintenance :**
    * Page dédiée (`admin/maintenance.php`) pour activer/désactiver le site.
    * Éditeur de texte complet pour personnaliser la page de maintenance (titre, message, images).
    * **Contournement Admin :** Les administrateurs connectés voient le site normalement, tandis que les visiteurs voient la page de maintenance.
    * **Indicateur Admin :** Un indicateur visuel (Rouge/Vert) "Maintenance ON/OFF" est visible dans le menu du site, uniquement pour les administrateurs.

* **Gestionnaire de Popups (CRUD) :**
    * Un gestionnaire complet (Ajouter, Modifier, Lister, Supprimer) a été ajouté à l'admin (`admin/popups.php`, `admin/add_popup.php`, `admin/edit_popup.php`).
    * Éditeur de texte (Summernote) pour le contenu, supporte les images (Base64) et leur redimensionnement.
    * **Règles d'affichage :** Contrôle total [On/Off], délai d'affichage (en secondes), affichage unique par session, et choix d'affichage (page d'accueil ou toutes les pages).
    * **Design :** Le style du popup a été épuré (suppression de l'en-tête) et les images sont automatiquement redimensionnées à 100% de la largeur du popup pour un affichage optimal.

* **Importateur de Flux RSS :**
    * Module complet pour agréger du contenu externe.
    * Gestion des flux (Ajouter/Supprimer) depuis l'admin.
    * Importation manuelle ("Importer") ou automatique (via Tâche Cron).
    * Détection avancée des images (y compris les tags `<media:content>`).
    * Gestion intelligente des doublons d'articles (via GUID) et de slugs (URLs).

* **Refonte des Paramètres & SEO :**
    * **Migration de la BDD :** Remplacement de l'ancienne table `settings` (clé/valeur) par une table moderne à ligne unique, optimisée pour la performance.
    * **SEO Avancé :** Ajout de champs gérables pour `meta_title`, `meta_author`, `meta_generator`, `meta_robots`, et les icônes (`favicon_url`, `apple_touch_icon_url`).
    * **Contrôles [On/Off] :** Ajout d'interrupteurs pour le "Sticky Header" et le "Head Custom Code".

---

### 🎨 Améliorations de l'Interface (UI/UX) (Post-v2.8.1)

* **Header "Sticky" :** Le menu principal peut être "collant" et reste visible au défilement (gérable via l'admin).
* **Footer Moderne :** Remplacement du pied de page par un design professionnel à 5 colonnes (Navigation, Réseaux, Méta, Logo), dynamique et épuré.
* **Affichage des Méta-tags :** Le `<head>` du site utilise désormais les nouveaux paramètres SEO pour un meilleur référencement et partage social.

---

### 🔧 Nouveaux Correctifs (Post-v2.8.1)

* **Mode Sombre :** Correction du script JavaScript dans le `footer()` qui empêchait le changement de thème (Light/Dark).
* **Sauvegarde Admin :** Correction d'un bug critique dans `admin/settings.php` qui empêchait la sauvegarde des 29+ paramètres.
* **Déconnexion (CSRF) :** Sécurisation du `logout.php` pour exiger une validation de jeton.
* **Filtre de Contenu (HTMLPurifier) :** Correction du filtre `core.php` pour autoriser les images en Base64 (`data:`) et leur redimensionnement (`style="width:..."`) dans les popups et la page de maintenance.
* **Base de Données :** Correction des types de colonnes (`TEXT` vers `LONGTEXT`) pour les Popups et la Maintenance afin d'autoriser les images volumineuses.

---

### 🔧 Correctifs (Live / Post-v2.9)

Ces correctifs ont été appliqués pour améliorer la stabilité et l'expérience utilisateur :

* **Correction Bug Commentaires :** Résolution d'un bug critique où les commentaires étaient postés en double. La cause était une inclusion multiple du script `post-interactions.js` dans `core.php`, qui a été corrigée.
* **Connexion Admin en Mode Maintenance :**
    * Ajout d'un point d'entrée `admin.php` à la racine pour permettre aux administrateurs de se connecter lorsque le mode maintenance est actif.
    * Création de `core-admin.php` pour fournir une logique de connexion isolée à cette page, sans charger l'intégralité du thème du site.

---

## Fonctionnalités et Base (Version 2.8.1)

Ce qui suit constitue la base fonctionnelle sur laquelle la v2.9 a été construite.

### 🚀 Fonctionnalités (Base v2.8.1)

* **Système de Tags Complet :** Ajout d'un système de tags (mots-clés).
    * Intégration de **Tagify** dans l'administration (`admin/add_post.php`, `admin/posts.php`).
    * Affichage des tags cliquables sur les articles (`post.php`).
    * Nouvelle page `tag.php` pour lister les articles par tag.
    * Ajout d'un widget "Nuage de Tags Populaires" (`core.php`).

* **Gestion avancée du menu :**
    * Ajout d'un statut "Publiée" / "Brouillon" pour chaque élément du menu.
    * Ajout d'onglets de filtrage (Tous / Publiées / Brouillons) dans l'admin.
    * Le statut d'une page est synchronisé avec l'élément de menu correspondant.

* **Refonte du Profil Utilisateur :**
    * L'avatar par défaut est géré via CSS.
    * L'en-tête du profil affiche un aperçu de l'avatar (`profile.php`).
    * La taille des avatars est contrôlée en CSS.
    * La suppression de l'avatar réinitialise correctement l'avatar par défaut (`profile.php`).

* **Améliorations du Système de Commentaires :**
    * Reconstruction pour permettre les **réponses imbriquées (threading)** (`post.php`, `core.php`).
    * Ajout de la **soumission de commentaires en AJAX** (`ajax_submit_comment.php`, `phpblog.js`).
    * Ajout d'un bouton "Répondre".
    * Ajout d'un bouton "Modifier" pour ses propres commentaires (`edit-comment.php`).
    * Ajout de **Badges Utilisateur** automatiques (Pipette, Actif, Loyal, Vétéran).

* **Optimisations des Requêtes (N+1) :**
    * Optimisation majeure des requêtes sur la barre latérale (`core.php`) : les requêtes pour le comptage des articles par catégorie et les commentaires récents ne sont exécutées qu'une seule fois.

* **Mode Sombre (Dark Mode) :**
    * Ajout d'un sélecteur de thème (Clair/Sombre) persistant (`core.php`, `phpblog.js`).
    * Le site respecte la préférence système de l'utilisateur (prefers-color-scheme).

* **Qualité de Code et Sécurité :**
    * Remplacement de `mysql_*` par `mysqli_*` avec **requêtes préparées**.
    * Implémentation de **jetons Anti-CSRF** sur tous les formulaires.
    * **HTML Purifier :** Intégration pour nettoyer tout le contenu HTML généré par les utilisateurs (articles, commentaires, widgets).
    * **Content Security Policy (CSP) :** Ajout d'en-têtes CSP.

* **Connexion Sociale (OAuth) :**
    * Ajout de la connexion via **Google**.
    * Intégration de la bibliothèque `Hybridauth`.
    * Création automatique d'un compte utilisateur (`social_callback.php`).

* **Synchronisation des Avatars :**
    * L'avatar du profil Google est automatiquement récupéré et mis à jour à chaque connexion (`social_callback.php`).

---

### ✨ Engagement des Utilisateurs (Base v2.8.1)

* **Système de Favoris :** Les utilisateurs connectés peuvent enregistrer des articles dans une liste personnelle (`my-favorites.php`) via un bouton AJAX.
* **Profils Auteurs Publics :** Une nouvelle page `author.php` affiche la biographie et tous les articles d'un auteur.
* **Badges de Commentaires :** Un système de "gamification" qui affiche des badges (ex: "Pipelette", "Actif", "Fidèle") en fonction du nombre de commentaires.

---

### 🔧 Administration (Tableau de bord v2.8.1)

* **Statistiques Exploitables :** Remplacement par des cartes d'action rapide (Articles Publiés, Ébauches, Commentaires en attente, etc.) (`admin/dashboard.php`).
* **Graphique des Vues :** Ajout d'un graphique (Chart.js) affichant les 5 articles les plus populaires.
* **Aperçu Rapide :** Widget affichant la version du blog, le nombre d'utilisateurs et le thème.
* **Création d'Utilisateurs :** Les administrateurs peuvent créer de nouveaux utilisateurs depuis l'admin (`admin/add_user.php`).
* **Système d'Ébauches (Drafts) :** Statuts "Ébauche", "Publié" ou "Inactif" pour les articles.
* **Temps de Lecture Estimé :** Affiche une estimation du temps de lecture (ex: "Lecture : 4 min") sur les articles.

---

### 🔐 Sécurité (Base v2.4+ / v2.8.1)

* **Installeur Sécurisé :** L'ancien installeur (base v2.4) a été entièrement réécrit.
    * Utilise `mysqli` avec des **requêtes préparées**.
    * Ne stocke plus les mots de passe en clair dans la session.
    * Écrit un `config.php` moderne et propre.

* **Anti-SQL Injection :** Migration de toutes les requêtes `mysql_*` vers `mysqli` avec **requêtes préparées** sur l'ensemble du site.

* **Anti-XSS (Cross-Site Scripting) :**
    * Intégration de **HTMLPurifier** pour nettoyer tout le contenu généré par les utilisateurs.
    * Mise en place de `htmlspecialchars()` sur toutes les sorties de données simples.

* **Anti-CSRF (Cross-Site Request Forgery) :**
    * Implémentation de jetons (tokens) `$_SESSION['csrf_token']` sur tous les formulaires critiques.
    * Ajout de fonctions de validation (`validate_csrf_token()`, `validate_csrf_token_get()`) dans `core.php`.

* **Protection Brute Force :**
    * Ajout d'un système de limitation des tentatives de connexion (`admin/index.php`).
    * Bloque la connexion pendant 5 minutes après 5 échecs.

* **Sécurité des Mots de Passe (Base v2.4+) :** Le stockage des mots de passe a été migré de `sha256` (obsolète) vers les fonctions PHP modernes et sécurisées `password_hash()` et `password_verify()` (`login.php`, `profile.php`, `install/done.php`).

---

### 🐞 Corrections de Bugs (Base v2.8.1)

* **Correction du Menu Public :** Le menu principal du site (`core.php`) n'affiche désormais que les éléments ayant le statut "Publiée".
* **Correction Layout Admin :** Correction d'un bug de mise en page dans `admin/users.php` (balises manquantes).
* **Correction Avatars Admin :** Correction d'un bug d'affichage où les avatars de grande taille déformaient le widget "Recent Comments" (`admin/header.php`).
* **Correction Marquee :** Correction d'une faute de frappe (`&;`) dans la barre de défilement "Latest Posts" (`core.php`).
* **Correction Page Recherche :** Correction d'un bug d'affichage (HTML échappé) sur `search.php` lors de l'affichage du nom de l'auteur.
* **Correction Erreur Fatale :** Correction d'une erreur `Fatal error: Cannot redeclare short_text()` dans `admin/header.php`.
* **Correction Installation :** Correction d'une erreur de chemin (`config.php` ou `../`) lors du processus d'installation.