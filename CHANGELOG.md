## [v3.4.4] - 2025-12-08
### 🔒 Nouveautés Majeures : Security & Performance
Cette version renforce drastiquement la sécurité et la vitesse de chargement du site.

#### 🛡️ Authentification & Accès
* **Double Facteur (2FA) :** Intégration complète de l'algorithme TOTP (Google Authenticator).
* **Protection Admin :** Sécurisation de `admin.php` et `login.php` avec vérification 2FA.
* **Anti-Bruteforce :** Verrouillage temporaire (5 minutes) après 5 tentatives échouées.

#### 👁️ Surveillance
* **Activity Logger :** Nouveau système de logs enregistrant les actions critiques.
* **Viewer Admin :** Page dédiée `/admin/logs.php` pour consulter l'historique.

#### ⚡ Performance (Cache)
* **Fragment Caching :** Système de cache fichier pour le Menu principal et la Sidebar.
* **Optimisation SQL :** Réduction drastique des requêtes en base de données au chargement des pages.
* **Auto-Flush :** Nettoyage intelligent du cache lors de la modification des paramètres.

---

## [v3.4.3] - 2025-12-08
### 🔒 Nouveautés Majeures : Security Fortress
Cette version renforce drastiquement la sécurité de l'administration et du compte utilisateur.

#### 🛡️ Authentification & Accès
* **Double Facteur (2FA) :** Intégration complète de l'algorithme TOTP (Google Authenticator).
* **Protection Admin :** Sécurisation de `admin.php` et `login.php` avec vérification 2FA conditionnelle.
* **Anti-Bruteforce :** Verrouillage temporaire (5 minutes) après 5 tentatives de connexion échouées.

#### 👁️ Surveillance
* **Activity Logger :** Nouveau système de logs enregistrant les actions critiques (Connexion, Modification Réglages, Suppression).
* **Viewer Admin :** Page dédiée `/admin/logs.php` pour consulter l'historique des activités.
* **Mouchards :** Intégration de traceurs dans les fonctions clés du coeur.

---

## [v3.4.2] - 2025-12-07
### 🏆 Nouveautés Majeures : Gamification
Cette version transforme le site en une plateforme communautaire interactive avec système de progression.

#### 🎮 Moteur de Jeu & Scores
* **API de Score :** Nouveau endpoint `ajax_submit_score.php` sécurisé pour recevoir les résultats des jeux.
* **Intégration JS :** Les jeux (Snake, Tetris, Space Invaders) envoient désormais les scores à la base de données en fin de partie ("Game Over").
* **Anti-triche basique :** Vérification de la session utilisateur avant l'enregistrement.

#### 🏅 Badges & Récompenses
* **Système de Badges :** Attribution automatique de badges (SQL) selon des déclencheurs (Score > X, Inscription, etc.).
* **Table SQL :** Nouvelles tables `game_scores`, `badges`, et `user_badges`.
* **Notifications :** Alerte visuelle immédiate en fin de partie lorsqu'un badge est débloqué.

#### 📊 Leaderboard
* **Page Hall of Fame :** Nouvelle page `/leaderboard.php` affichant le Top 10 pour chaque jeu.
* **Profil Joueur :** Affichage de la collection de badges et du rang sur le leaderboard.
* **Design :** Tableaux stylisés avec médailles (🥇, 🥈, 🥉) pour le podium.

---

## [v3.4.1] - 2025-12-07
### 🛒 Nouveautés Majeures : E-commerce Lite
Cette version introduit la monétisation du contenu via un module de boutique simplifié (Drop-shipping / Liens directs).

#### 🛍️ Module Boutique
* **Architecture Produit :** Conversion possible de tout "Projet" en "Produit" via l'admin.
* **Champs E-commerce :** Ajout de Prix, État du stock (En stock, Précommande, Rupture) et Lien d'achat externe (PayPal, Stripe).
* **Page Shop :** Nouvelle page `/shop.php` dédiée exclusivement aux produits avec une grille visuelle distincte.
* **Intégration Accueil :** Les produits apparaissent sur la page d'accueil avec une étiquette de prix verte et un bouton "Acheter".

#### 🧩 Widgets & Admin
* **Nouveau Widget :** "Shop / Featured Products" ajouté au gestionnaire de widgets.
* **Configuration Widget :** Possibilité de choisir le nombre de produits à afficher aléatoirement dans la sidebar.
* **Interface Admin :** Nouvel onglet "Shop" dans l'éditeur de projets (Add/Edit Project).

#### 🛠️ Améliorations
* **Core :** Optimisation de la fonction `render_widget()` pour supporter des types personnalisés complexes.
* **Navigation :** Séparation logique stricte : les Produits ne polluent plus la liste des Tutoriels/Projets.

---

## [v3.4.0] - 2025-12-07
### 🎮 Nouveautés Majeures : Arcade & Engagement
Cette version transforme le site en un véritable hub de divertissement et maximise le SEO.

#### 🕹️ Salle d'Arcade (Games Hub)
* **Nouveau Module :** `games/` avec une page d'accueil dédiée "Arcade Room".
* **3 Jeux Complets :**
    * **Space Invaders Deluxe :** Sprites, Sons, Score, Vagues d'ennemis.
    * **Snake Deluxe :** Graphismes (Tête, Corps, Pomme), Accélération progressive.
    * **Tetris :** Moteur complet avec rotation, niveaux et score.
* **Intégration :** Les jeux sont isolés dans un sous-dossier mais conservent le Header/Footer du site grâce aux chemins absolus.

#### 🎉 Gestionnaire d'Événements (Marketing)
* **Modes Saisonniers :** Activation en 1 clic de thèmes (Noël/Neige, Confettis, Noir & Blanc).
* **Top Banner :** Bannière d'annonce promotionnelle (Black Friday, Soldes) personnalisable (Couleur, HTML).
* **Administration :** Nouvel onglet dédié dans les Réglages Généraux.

#### 🚀 SEO & Technique
* **Sitemap & RSS Unifiés :** Les flux XML incluent désormais les **Projets** (Portfolio) en plus des Articles de Blog.
* **Flux RSS 2.0 :** Ajout des images (`<enclosure>`) pour les lecteurs de flux modernes.
* **Architecture :** Passage complet aux URLs absolues (`$settings['site_url']`) dans le Header/Footer pour éviter les bugs de liens relatifs (404) dans les sous-dossiers.
* **Footer Pro :** Liens sociaux aux couleurs officielles, badges de confiance (SSL, Responsive) sécurisés.

---

# Journal des modifications (Changelog)

Tous les changements notables apportés à ce projet seront documentés dans ce fichier.

Le format est basé sur [Keep a Changelog](https://keepachangelog.com/fr/1.0.0/).

## [v3.3.2] - 2025-12-01
### 🚀 Nouveautés Majeures : Portfolio & Social Plus

Cette version transforme le CMS en une plateforme hybride (Blog + Portfolio + Réseau Social) avec des fonctionnalités professionnelles.

#### 🛠️ Module "Projects" (Portfolio style Hackster.io)
* **Nouveau Type de Contenu :** Gestion complète de projets techniques ou créatifs.
* **Structure Détaillée :** Champs spécifiques (Difficulté, Durée, Matériel, Logiciels, Histoire, Fichiers joints).
* **Administration :** Formulaire d'ajout/édition par onglets (Basics, Team, Things, Story, Attachments).
* **Affichage :** * Galerie publique avec filtres par difficulté (Easy, Intermediate, Advanced, Expert).
    * Page de détail immersive sans sidebar latérale.
    * Widget Admin "Latest Projects".

#### 💬 Tchat "WhatsApp-like" (Social)
* **Refonte UX/UI Totale :**
    * Sidebar avec onglets (Discussions, Appels, Statut, En ligne).
    * Design des bulles de messages style messagerie mobile.
    * **Statuts (Stories) :** Possibilité de poster des statuts éphémères (Texte/Image) visibles 24h.
* **Fonctionnalités Avancées :**
    * **Drag & Drop :** Envoi d'images par glisser-déposer dans la zone de chat.
    * **Emojis :** Sélecteur d'emojis moderne intégré.
    * **Indicateurs :** "En train d'écrire...", "Vu à...", Accusés de lecture (Coches bleues).
    * **Archives & Favoris :** Possibilité d'archiver des conversations et de mettre des messages en favoris.

#### ⚙️ Administration & Système
* **Gestionnaire de Fichiers 2.0 :**
    * Outil de **Synchronisation (Sync)** : Importation automatique des fichiers FTP vers la base de données.
    * Nettoyage des orphelins (Fichiers BDD sans fichier physique).
    * Sélecteur visuel "Select from Library" intégré partout (Articles, Pages, Projets, Avatars).
* **Gestion des Pages :** Ajout des champs SEO (Meta Title/Desc), Image à la une et Slugs personnalisables.
* **Sécurité Renforcée :**
    * Nettoyage systématique des chemins d'images (`../`) pour éviter les liens brisés.
    * Système de fallback "3 niveaux" pour les images (Image réelle > Image par défaut > SVG).
* **Ergonomie :** * Actions en masse (Bulk Actions) sur les Articles, Pages, Pubs, Menus.
    * Filtres rapides (Published / Draft) sur toutes les listes.

---

## [v3.2.0] - 2025-11-28
### 🚀 Nouveautés Majeures : Gestion Multi-Utilisateurs & Communication
Cette version transforme le CMS en une véritable plateforme collaborative sécurisée.

#### 🛡️ Sécurité & Rôles (RBAC)
* **Système de Rôles :** Admin (Total) vs Éditeur (Limité à ses contenus).
* **Protection "Anti-Hack" :** Sécurisation des URL d'action (`delete-id`).
* **Logs d'Activité :** Traçabilité complète des actions utilisateurs.

#### 📧 Communication
* **SMTP Natif :** Intégration de PHPMailer.
* **Tchat v1 :** Première version du tchat temps réel.
* **Bannière RGPD :** Gestion du consentement cookies bloquant les scripts tiers.

---
*(Voir l'historique complet pour les versions antérieures)*

Tous les changements notables apportés à ce projet seront documentés dans ce fichier.

Le format est basé sur [Keep a Changelog](https://keepachangelog.com/fr/1.0.0/).

## [v3.2.0] - 2025-11-28
### 🚀 Nouveautés Majeures : Gestion Multi-Utilisateurs & Communication
Cette version transforme le CMS en une véritable plateforme collaborative sécurisée avec des rôles distincts et des outils de communication temps réel.

#### 🛡️ Sécurité & Rôles (RBAC)
* **Système de Rôles (Admin vs Editor) :**
    * **Admin :** Accès total à toutes les fonctionnalités et tous les contenus.
    * **Éditeur :** Accès restreint. Ne peut créer, modifier ou supprimer **que** ses propres contenus (Articles, Pages, Images, Albums, Catégories).
* **Protection "Anti-Hack" :** Sécurisation côté serveur de tous les scripts de suppression et d'édition (`delete-id`) pour empêcher la modification de contenus ne nous appartenant pas via l'URL.
* **Base de données :** Ajout de la colonne `author_id` sur toutes les tables de contenu (`posts`, `pages`, `categories`, `files`, `albums`, `gallery`) pour lier chaque élément à son créateur.

#### 💬 Nouveau Module de Tchat (Hub Social)
* **Refonte complète de l'interface :** Design moderne type "Messagerie mobile" (WhatsApp-like).
* **Onglets Intelligents :** Navigation fluide entre "Discussions en cours" et "Utilisateurs en ligne".
* **Fonctionnalités Temps Réel :**
    * Indicateur de présence (Pastille verte).
    * Accusés de réception (Double coche bleue quand lu).
    * Notifications globales : Son ("Pop") et Bulle visuelle (Toast) persistante sur tout le site lors de la réception d'un message.
* **Partage de Médias :** Possibilité d'envoyer des images directement dans le tchat.
* **Sécurité & Confidentialité :** Cloisonnement total des conversations. Les éditeurs ne peuvent pas voir les chats des autres. Suppression physique des images lors de la suppression d'une conversation.

#### 🕵️‍♂️ Logs d'Activité & Surveillance
* **Système de Logs :** Enregistrement automatique de toutes les actions critiques (Connexion, Création, Modification, Suppression) dans une nouvelle table `activity_logs`.
* **Visualiseur de Logs :** Nouvelle page `admin/logs.php` (réservée aux Admins) pour auditer l'activité du site (Qui a fait quoi, quand et depuis quelle IP).

### ✨ Améliorations
* **Tableau de Bord (Dashboard) :**
    * **Vue Éditeur :** Affichage personnalisé des statistiques personnelles (Mes articles, Mes vues, Mes commentaires) et de la liste "Mes derniers articles".
    * **Vue Admin :** Vue globale inchangée.
* **Mailing (SMTP) :**
    * Intégration de **PHPMailer** (via Composer/Vendor) pour l'envoi d'emails fiables.
    * Nouvelle page de configuration SMTP dans les paramètres.
    * Outil de test d'envoi d'email (`admin/test_email.php`).
* **Interface (UI) :**
    * Harmonisation des badges de rôles sur tout le site (Vert = Admin, Bleu = Éditeur).
    * Menu latéral Admin en accordéon pour une meilleure lisibilité ("Manage", "System", "Create New").
    * Page de profil auteur publique (`author.php`) améliorée avec statistiques et bio sécurisée.

### 🔧 Technique
* **Refactoring :** Nettoyage et sécurisation des fichiers `login.php`, `ajax_chat.php` et `core.php`.
* **Dépendances :** Ajout du dossier `vendor/` pour gérer les librairies tierces (PHPMailer).

---

## [3.1.0] - 2025-11-22
### 🚀 Refonte Majeure de l'Administration (UI/UX & Architecture)
Cette version introduit une interface professionnelle standardisée "2 colonnes" et sépare la logique de liste et d'édition pour une meilleure maintenabilité.

### ✨ Nouveautés & Améliorations
* **Architecture Global Admin :** Séparation systématique des fichiers de "Liste" et d'"Édition" pour les modules principaux.
    * Création de `admin/edit_post.php`, `admin/edit_page.php`, `admin/edit_category.php`, `admin/edit_gallery.php`, `admin/edit_slide.php`, `admin/edit_quiz.php`.
* **Design "Pro" (2 Colonnes) :** Refonte de tous les formulaires d'ajout et d'édition (Articles, Pages, Catégories, Quiz, Slider, Galerie) avec :
    * Colonne Gauche (75%) : Contenu principal (Titre, Éditeur, Images).
    * Colonne Droite (25%) : Barre latérale de métadonnées (Publication, Date, Catégories, Options).
* **Interface Utilisateur (UI) :**
    * Harmonisation des tableaux de liste avec boutons d'actions compacts (Icônes uniquement) et espacés.
    * Correction des marges (Grid Bootstrap) sur toutes les pages de liste pour éviter l'effet "collé aux bords".
    * Ajout de **prévisualisation d'image en temps réel** (JS) sur tous les formulaires d'upload.
* **Module Quiz :**
    * Remplacement du menu déroulant "Difficulté" par des **boutons radio colorés** (Vert/Bleu/Jaune/Rouge) pour une meilleure ergonomie.
    * Réintégration complète des widgets de statistiques et des tableaux de bord dans `quiz_stats.php`.
    * Conservation de la logique complexe de suppression en cascade (Options > Questions > Quiz).

### 🐛 Corrections de Bugs
* **Tags (Articles) :** Correction critique de la duplication des tags lors de l'édition d'un article. Nettoyage automatique des tags orphelins en base de données.
* **Quiz :** Correction des champs manquants (Points) et sécurisation de la création des dossiers d'upload (`mkdir`).
* **Mise en page :** Correction des structures HTML invalides (balises `<td>` imbriquées) dans les tableaux d'administration.

---

## [v3.0.1] - Version actuelle
Cette version se concentre sur la stabilité, la sécurité du processus de déconnexion et des améliorations de l'interface d'administration.

### Ajouté
- Nouvelle interface dans l'administration pour personnaliser l'image d'arrière-plan de la page publique "Banni".

### Modifié
- Optimisation du tableau de bord (Dashboard) : le widget "Raccourcis" est désormais replié par défaut pour un affichage initial plus épuré.

### Corrigé
- **Critique** : Refonte complète du système de déconnexion (`logout.php`). Correction des problèmes de redirection (pages blanches ou noires) survenant sur certains serveurs de production en raison de l'envoi prématuré d'en-têtes.

## [v3.0.0]
Introduction d'un système d'installation automatisé pour faciliter le déploiement du CMS.

### Ajouté
- Nouvel assistant d'installation (Wizard) situé dans le dossier `/install`, permettant une configuration graphique de la base de données et du compte administrateur initial.

## [v2.5.0]
Ajout de fonctionnalités de modération des utilisateurs.

### Ajouté
- Système de bannissement des utilisateurs. Les administrateurs peuvent désormais bannir un utilisateur, l'empêchant de se connecter.
- Page publique spécifique pour les utilisateurs bannis.

## [v2.2.0]
Amélioration de la gestion des médias.

### Ajouté
- Nouvelle "Médiathèque" dans l'administration pour visualiser et gérer tous les fichiers uploadés sur le serveur.

## [v2.1.1]
Correctifs mineurs d'interface.

### Corrigé
- Ajustements divers sur les liens du tableau de bord et le bouton "Voir le site".

## [v2.1.0]
Extension des capacités de personnalisation du site.

### Ajouté
- **Gestionnaire de Menu** : Outil en "drag-and-drop" pour organiser facilement le menu de navigation principal du site.
- **Gestionnaire de Widgets** : Interface permettant d'activer ou de désactiver les éléments affichés dans la barre latérale (sidebar).

## [v2.0.0] - Refonte Majeure
Cette version marque une rupture importante avec le code initial du tutoriel, introduisant une interface moderne et une sécurité renforcée.

### Modifié
- **Interface Admin** : Remplacement complet de l'ancienne interface par le template **AdminLTE 3**, offrant un design responsive et professionnel.
- **Éditeur de texte** : Remplacement de CKEditor par **Summernote** pour une édition de contenu plus fluide.
- **Tableaux de données** : Intégration de **DataTables** pour améliorer l'affichage, le tri et la recherche dans toutes les listes (articles, utilisateurs, etc.).

### Sécurité
- Refonte significative de la sécurité globale :
    * Mise en place du hachage sécurisé des mots de passe.
    * Protection systématique contre les injections SQL (utilisation de requêtes préparées).
    * Protection contre les failles XSS.

## [v1.0.0] - Version Initiale
Version stable issue du tutoriel Udemy de base.

### Ajouté
- Fonctionnalités CRUD (Créer, Lire, Mettre à jour, Supprimer) de base pour :
    * Les articles de blog.
    * Les catégories.
    * Les utilisateurs.
- Système de commentaires simple.
- Partie front-office basique pour afficher le blog.