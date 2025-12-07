# Mon CMS - v3.4.0 (Arcade & Portfolio Edition)

Ce projet est un Content Management System (CMS) développé en PHP procédural, moderne et performant.
Il offre une solution complète pour gérer un **Blog**, un **Portfolio Technique**, une **Communauté** et désormais une **Salle d'Arcade**.

---

## 🚀 Dernière version majeure (v3.4.0) - "Fun & Engagement"

Cette version introduit une dimension ludique et marketing au CMS :

* **Arcade Room :** Une section jeux vidéo complète (Space Invaders, Snake, Tetris) pour retenir les visiteurs.
* **Events Manager :** Des outils pour animer le site lors des fêtes (Noël, Black Friday) avec des effets visuels et des bannières.
* **Module Projets :** Documentation technique avancée (BOM, Schémas, Fichiers 3D).
* **Tchat & Social :** Messagerie instantanée et commentaires.

---

# Mon CMS - v3.3.2 (Portfolio Edition)

Ce projet est un Content Management System (CMS) développé en PHP procédural, moderne et performant.
Il offre une solution complète pour gérer un **Blog**, un **Portfolio** et une **Communauté** active.

---

## 🚀 Dernière version majeure (v3.3.2) - "Portfolio & Social"

Cette version ajoute une dimension "Maker/Portfolio" au CMS, permettant de documenter des projets complexes, tout en améliorant considérablement l'aspect social.

* **Module Projets :** Une section dédiée pour présenter vos créations (Style Hackster/Instructables) avec gestion du matériel (BOM), des fichiers (STL, Code) et de l'équipe.
* **Tchat Avancé :** Une expérience de messagerie instantanée complète avec envoi de fichiers, statuts éphémères, emojis et indicateurs de frappe.
* **Outils Admin Pro :** Synchronisation de fichiers FTP, actions en masse, et gestion SEO fine sur tous les contenus.

---

## ✨ Fonctionnalités Principales

### 🌐 Front-office (Visiteurs & Membres)
* **Blog Complet :** Articles, Catégories, Tags, Recherche, Commentaires imbriqués.
* **Portfolio / Projets :** Galerie de projets filtrable par difficulté, vue détaillée technique.
* **Espace Membre :**
    * Profil public (`author.php`) et privé (`profile.php`) avec gestion d'avatar (Upload/Galerie).
    * **Tchat Privé (WhatsApp-style)** : Discussions temps réel, archivage, favoris, statuts.
    * Système de favoris (Bookmarks) pour les articles.
* **Interaction :** Sondages, Quiz, FAQ, Formulaire de contact SMTP.

### 🛠️ Back-office (Administration)
* **Tableau de bord (Dashboard)** : Statistiques globales, graphiques, dernières activités.
* **Gestion de Contenu** :
    * **Articles & Pages :** Éditeur riche (Summernote), SEO (Meta tags, Slugs), Gestionnaire de médias intégré.
    * **Projets :** Éditeur par onglets pour structurer la documentation technique.
* **Médiathèque Avancée** :
    * Upload, visualisation, suppression.
    * **Outil de Synchronisation** : Détection automatique des fichiers ajoutés par FTP.
* **Gestion Utilisateurs & Rôles** : Admin, Éditeur, Utilisateur. Système de bannissement (IP, Email).
* **Marketing & Légal** :
    * Gestionnaire de Publicités (Ads).
    * Gestionnaire de Popups.
    * Newsletter (Export CSV, Envoi d'emails HTML).
    * Conformité RGPD (Bannière Cookie bloquante).

---

## ⚙️ Pré-requis Techniques

* **PHP :** 7.4 ou supérieur (8.0+ recommandé).
* **Base de données :** MySQL 5.7+ ou MariaDB.
* **Extensions PHP :** `mysqli`, `gd` (pour les images), `mbstring`, `curl`.
* **Serveur Web :** Apache (avec `mod_rewrite` pour les URLs propres) ou Nginx.

---

## 📥 Installation Rapide

1.  **Fichiers :**
    * Déposez tous les fichiers sur votre serveur.
    * Dossier `vendor/` requis (pour PHPMailer).
2.  **Base de données :**
    * Créez une base de données vide.
    * Importez le fichier `database.sql`.
3.  **Configuration :**
    * Renommez `includes/db.php.example` en `includes/db.php` et entrez vos identifiants BDD.
    * Assurez-vous que le dossier `uploads/` et ses sous-dossiers (`posts`, `avatars`, `files`, `projects`, `status`) sont en écriture (CHMOD 755/777).
4.  **Accès Admin :**
    * URL : `/admin`
    * Login : `admin@admin.com` / `password`
    * **Action immédiate :** Changez votre mot de passe et configurez l'URL du site dans *Settings > General*.

---

## 🤝 Contribuer

Ce projet est Open Source. Les contributions, signalements de bugs et suggestions sont les bienvenus pour continuer à faire évoluer ce CMS léger et puissant.

# Mon CMS - v3.2.0 (Multi-User & Secure Edition)

Ce projet est un Content Management System (CMS) développé en PHP procédural, moderne et performant.
Il a évolué vers une solution collaborative robuste (v3.2.0) intégrant une gestion fine des droits (RBAC), un tchat temps réel et une sécurité renforcée.

---

## 🚀 Dernière version majeure (v3.2.0) - "Collaboration & Sécurité"

Cette version introduit la gestion multi-utilisateurs avancée. Le CMS permet désormais de gérer une équipe de rédaction en toute sécurité.

* **Rôles & Permissions (RBAC) :** Distinction stricte entre **Administrateurs** (contrôle total) et **Éditeurs** (confinés à leurs propres contenus).
* **Communication Temps Réel :** Un module de Tchat privé complet avec notifications sonores globales, envoi d'images et indicateurs de présence.
* **Audit & Logs :** Traçabilité complète des actions utilisateurs (Connexions, suppressions, modifications) via un journal d'activité.
* **Emailing Fiable :** Intégration native de PHPMailer pour les notifications SMTP.

---

## ✨ Fonctionnalités Principales

### Front-office (Partie visible)
* **Espace Membre & Social :**
    * Profils publics auteurs (`author.php`) avec biographie et liste d'articles.
    * **Tchat Privé** type "Messagerie Instantanée" avec notifications live.
* Affichage des articles de blog avec pagination et système de **Tags** complet.
* Système de commentaires avec réponses imbriquées et badges de rôle (Admin/Editor).
* **Module de Quiz** interactif et **Sondages**.
* Design responsive (Bootstrap 5) avec Mode Sombre (Dark Mode).

### Back-office (Administration)
* **Tableau de bord (Dashboard) Adaptatif** :
    * **Admin :** Vue globale du site (Santé système, Stats globales).
    * **Éditeur :** Vue personnelle (Mes articles, Mes vues, Mes commentaires).
* **Gestion de Contenu Sécurisée** :
    * Les éditeurs ne peuvent modifier/supprimer que **leurs** propres articles, pages, albums et fichiers.
* **Outils Système (Admin Only)** :
    * **Activity Logs** : Surveillance de l'activité du site.
    * **Settings** : Configuration complète (SEO, SMTP, Réseaux sociaux).
    * **Menu Editor** : Gestion du menu en Drag & Drop.
    * **Maintenance** : Mode maintenance avec contournement pour l'admin.

---

## 🛠️ Installation

1.  **Fichiers :**
    * Déposez tous les fichiers sur votre serveur.
    * **IMPORTANT :** Assurez-vous que le dossier `vendor/` est présent à la racine (requis pour les emails).
2.  **Base de données :**
    * Importez le fichier `database.sql` dans votre base MySQL.
3.  **Configuration :**
    * Renommez `includes/db.php.example` en `includes/db.php` et configurez vos accès BDD.
4.  **Permissions :**
    * Donnez les droits d'écriture (CHMOD 755 ou 777) au dossier `uploads/` et ses sous-dossiers.
5.  **Premier accès :**
    * Admin par défaut : `admin@admin.com` / `password` (À changer immédiatement !).

---

# Mon CMS - v3.1.0

Ce projet est un Content Management System (CMS) développé en PHP procédural, moderne et performant.

Initialement basé sur une structure simple, il a évolué vers une solution robuste (v3.1.0) intégrant une administration professionnelle basée sur **AdminLTE 3**, un éditeur **Summernote**, et une gestion avancée des médias.

---

## 🚀 Dernière version majeure (v3.1.0) - "Admin Pro Update"

Cette version transforme radicalement l'expérience d'administration en adoptant des standards professionnels d'interface et d'architecture.

* **Interface Ergonomique :** Tous les formulaires d'administration (Articles, Pages, Quiz, etc.) adoptent désormais une disposition "Pro" en deux colonnes (Contenu vs Paramètres), inspirée des grands CMS du marché.
* **Gestion des Médias :** Ajout de la prévisualisation instantanée des images avant upload sur l'ensemble du back-office.
* **Architecture Propre :** Séparation stricte entre les vues "Liste" et les vues "Édition" pour une meilleure maintenabilité du code.
* **Module Quiz Avancé :** Interface de gestion des questions/réponses améliorée, statistiques détaillées et choix visuel de la difficulté.
* **Stabilité :** Correction de la gestion des Tags (doublons) et optimisation des requêtes SQL.

---

## ✨ Fonctionnalités Principales

### Front-office (Partie visible)
* Affichage des articles de blog avec pagination et système de **Tags** complet.
* Affichage des pages statiques et formulaires dynamiques.
* Système de commentaires avec réponses imbriquées (threading).
* **Module de Quiz** interactif pour les visiteurs.
* Design responsive (Bootstrap) avec Mode Sombre (Dark Mode).
* Connexion sociale (Google) et profils utilisateurs avancés.

### Back-office (Administration)
* **Tableau de bord (Dashboard)** : Statistiques en temps réel et graphiques.
* **Gestion de Contenu "Pro"** :
    * **Articles** : Éditeur riche, gestion avancée des tags, image à la une, publication planifiée.
    * **Pages & Catégories** : Gestion complète avec slugs automatiques.
* **Modules Spéciaux** :
    * **Quiz Manager** : Création de quiz, gestion des questions/réponses, statistiques des tentatives joueurs.
    * **Slider** : Gestionnaire de diapositives avec ordre par glisser-déposer (via ordre numérique).
    * **Galerie** : Gestion d'albums photos.
* **Outils Techniques** :
    * **Maintenance** : Mode maintenance avec page personnalisable et contournement admin.
    * **Popups** : Gestionnaire de popups marketing.
    * **RSS** : Importateur de flux automatique.

---

## 🛠️ Installation

*(Voir la documentation complète pour les détails serveur)*

1.  **Base de données :** Importez `database.sql` dans votre base MySQL.
2.  **Configuration :** Renommez `includes/db.php.example` en `includes/db.php` et configurez vos accès.
3.  **Dossiers :** Assurez-vous que le dossier `uploads/` et ses sous-dossiers (`posts`, `gallery`, `quiz`, etc.) sont accessibles en écriture (CHMOD 755 ou 777 selon l'hébergeur).
4.  **Accès Admin :** `/admin` (Compte par défaut : `admin@admin.com` / `password`)

---

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
