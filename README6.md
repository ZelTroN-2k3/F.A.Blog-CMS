# phpBlog v2.9.4 (Édition Complète)
phpBlog - News, Blog & Magazine CMS

## Vue d'ensemble

Cette version **v2.9.4** est l'aboutissement de la refonte du CMS. Elle transforme le blog en une plateforme professionnelle, riche, interactive et sécurisée, dotée d'outils d'engagement et de maintenance avancés.

L'ajout final est un **Gestionnaire de Slider Personnalisé**, permettant à l'administrateur de basculer entre un slider marketing (diapositives personnalisées) et le slider dynamique des articles en vedette.

---

### 🌟 Nouveautés Exclusives (v2.9.4)

#### 🎨 Mega Menu "Next-Gen" (Frontend)
Le système de navigation a été entièrement repensé pour offrir une expérience utilisateur moderne et **100% responsive** :
* **Structure Avancée :** Un menu déroulant large centré, structuré en 3 colonnes stratégiques.
* **Contenu Riche :**
    1.  **Explore :** Liens rapides et flux RSS.
    2.  **Catégories :** Liste complète sur deux colonnes.
    3.  **Nouveautés (Visuel) :** Affichage dynamique des **4 derniers articles avec images miniatures** et dates.
* **Mobile-First :** Le menu se transforme intelligemment en accordéon fluide sur mobile, et en "carte flottante" sur PC.

#### 🚀 Modules Professionnels

* **📢 Importateur de Flux RSS (Auto-Blogging) :**
    * Agrégation de contenu externe manuelle ou automatique (CRON).
    * **Intelligence Artificielle :** Détection automatique de l'image principale via les balises `<media:content>`, `<enclosure>` ou analyse du HTML.
    * **Anti-Doublons :** Vérification des GUID pour garantir un contenu unique.

* **💬 Gestionnaire de Popups (Marketing) :**
    * Création de fenêtres modales avec éditeur visuel (Summernote).
    * **Ciblage Précis :** Choix des pages (Accueil vs Tout le site), délai d'apparition, et fréquence (une fois par session ou à chaque chargement).
    * **Gestion Admin :** Activation/Désactivation rapide (Toggle) sans supprimer le popup.

* **🚧 Mode Maintenance Avancé :**
    * Page d'attente personnalisable pour les visiteurs.
    * **Accès Admin Préservé :** Les administrateurs connectés voient le site normalement.
    * **Indicateur Visuel :** Badge "Maintenance ON" visible dans le menu admin pour éviter les oublis.
    
#### 🖼️ Gestionnaire de Slider d'Accueil (Nouveau)
Flexibilité totale pour votre page d'accueil :
* **Double Mode :** L'administrateur peut choisir via les Réglages (`admin/settings.php`) quel slider afficher :
    1.  **Articles en Vedette (Défaut) :** Affiche automatiquement les articles marqués "featured".
    2.  **Slider Personnalisé (Nouveau) :** Affiche des diapositives marketing créées manuellement.
* **Gestion Admin :** Un module complet (`admin/slides.php`) permet de créer, modifier, ordonner et supprimer des diapositives personnalisées (Image, Titre, Description HTML, Lien).

#### 🛠️ Outils Système & Maintenance
* **💾 Gestionnaire de Sauvegarde (Backup) :**
    * **1-Click Backup :** Génère et sauvegarde un fichier `.sql` complet dans un dossier sécurisé (`/backup-database/`) sur le serveur.
    * **Gestionnaire d'Historique :** Affiche la liste de toutes les sauvegardes (date, taille) avec des options pour **Télécharger** ou **Supprimer**.

#### 📊 Modules d'Interaction & UGC
* **💬 Gestionnaire de Témoignages (Testimonials) :**
    * **Soumission Frontend :** Les utilisateurs connectés peuvent soumettre leurs propres témoignages via leur menu profil.
    * **Flux de Modération Admin :** Les soumissions reçoivent le statut **"Pending"** et peuvent être approuvées en 1 clic.
    * **Affichage :** Slider Bootstrap sur la page d'accueil.
* **🗳️ Système de Sondages (Polls) :**
    * Widget Sidebar avec vote AJAX et affichage des résultats.
    * Protection anti-spam par IP et Cookies.
* **❓ Foire Aux Questions (FAQ) :**
    * Page publique dédiée (`faq.php`) avec interface en accordéon.

---

### 🚀 Fonctionnalités Majeures (v2.9.1)

* **🎨 Mega Menu "Next-Gen" :** Navigation 100% responsive avec 3 colonnes et affichage des derniers articles.
* **📢 Importateur RSS Auto :** Auto-blogging avec détection d'images et anti-doublons.
* **🔔 Gestionnaire de Popups :** Fenêtres modales marketing ciblées.
* **🚧 Mode Maintenance :** Page d'attente personnalisable avec accès administrateur préservé.

---

### 🛡️ Sécurité Renforcée

* **Protection Totale :** Anti-CSRF (Tous formulaires), Anti-XSS (HTMLPurifier), Anti-SQLi (Requêtes préparées).
* **Authentification :** Protection Brute Force (Blocage temporaire) et hachage `password_hash()`.
* **Installation :** Séparation des e-mails (Site vs Admin).

---

### 🛡️ Sécurité & Technique (Renforcée)

Cette version corrige de nombreuses failles de l'ancienne version :

* **Protection Totale :**
    * **Anti-CSRF :** Protection sur tous les formulaires (GET et POST) avec jetons de session.
    * **Anti-XSS :** Intégration de **HTMLPurifier** pour nettoyer tout le contenu utilisateur (Commentaires, RSS).
    * **SQL Injection :** Migration complète vers `MySQLi` avec **requêtes préparées**.
* **Authentification Blindée :**
    * **Anti-Brute Force :** Blocage temporaire (5 min) après 5 tentatives de connexion échouées.
    * **Mots de Passe :** Hachage moderne via `password_hash()` (Bcrypt) au lieu de SHA256.
* **Installation :** Séparation stricte entre l'Email du Site (Notifications) et l'Email de l'Admin (Compte personnel).

---

### 🐞 Correctifs de Bugs (Héritage v2.8+)

* **Correction Émoticônes :** Les smileys (ex: `:)`) s'affichent désormais correctement en émojis (🙂) dans les commentaires.
* **Correction Menu Public :** Le menu n'affiche que les éléments "Publiés".
* **Correction Avatars :** Gestion des avatars Google (URL externes) et redimensionnement correct dans l'admin.
* **Correction Marquee :** Réparation de la barre "Derniers articles" qui contenait des erreurs de syntaxe.
* **Correction Recherche :** Affichage sécurisé des noms d'auteurs dans les résultats.
* **Correction Layout Admin :** Réparation des balises manquantes dans `users.php` et du footer qui remontait.

---

### ✨ Fonctionnalités de Base

* **Engagement :** Système de "J'aime", "Favoris" et Badges de commentateurs (Vétéran, Actif...).
* **Social :** Connexion via Google (OAuth) et partage social intégré.
* **Contenu :** Système de Tags (mots-clés), recherche avancée, et temps de lecture estimé.
* **Interface :** Mode Sombre/Clair (Dark Mode) respectant les préférences système.

---

### 📋 Prérequis
* PHP 7.4 ou supérieur (8.0+ recommandé)
* Extension PHP `mysqli` & `mbstring`
* Extension PHP `curl` (pour RSS) et `gd` (pour les images)
* Apache avec `mod_rewrite` activé

---

### 💿 Installation
1.  Uploadez les fichiers sur votre serveur.
2.  Créez une base de données MySQL vide.
3.  Rendez-vous sur `votre-site.com/install/` et suivez l'assistant.
4.  **Sécurité :** Supprimez le dossier `/install/` une fois terminé.

---

### 🔄 Instructions de Mise à Jour (SQL)

Si vous mettez à jour depuis une version précédente, exécutez ces requêtes dans PHPMyAdmin :

```sql
-- 1. Paramètres Maintenance & Header
ALTER TABLE `settings` ADD `sticky_header` varchar(10) NOT NULL DEFAULT 'Off';
ALTER TABLE `settings` ADD `maintenance_mode` varchar(10) NOT NULL DEFAULT 'Off';
ALTER TABLE `settings` ADD `maintenance_title` varchar(255) NOT NULL DEFAULT 'Site Under Maintenance';
ALTER TABLE `settings` ADD `maintenance_message` LONGTEXT NULL;

-- 2. Table Flux RSS
CREATE TABLE `rss_imports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `feed_url` varchar(255) NOT NULL,
  `import_as_user_id` int(11) NOT NULL,
  `import_as_category_id` int(11) NOT NULL,
  `last_import_time` datetime DEFAULT NULL,
  `is_active` int(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
);

-- 3. Table Popups
CREATE TABLE `popups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `content` LONGTEXT NOT NULL,
  `active` varchar(3) NOT NULL DEFAULT 'No',
  `display_pages` varchar(255) NOT NULL DEFAULT 'home',
  `show_once_per_session` varchar(3) NOT NULL DEFAULT 'Yes',
  `delay_seconds` int(3) NOT NULL DEFAULT 2,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
);

-- 4. Support RSS (GUID unique)
ALTER TABLE `posts` ADD `imported_guid` varchar(255) DEFAULT NULL;
ALTER TABLE `posts` ADD UNIQUE KEY `imported_guid_unique` (`imported_guid`);

Si vous mettez à jour depuis la v2.9.1 vers la **v2.9.4**, exécutez ces requêtes :

```sql
-- 1. Table des Sondages (Polls)
CREATE TABLE `polls` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `question` varchar(255) NOT NULL,
  `active` enum('Yes','No') NOT NULL DEFAULT 'Yes',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
);

-- 2. Options des Sondages
CREATE TABLE `poll_options` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `poll_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `votes` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `poll_id` (`poll_id`)
);

-- 3. Votants (Anti-Doublon)
CREATE TABLE `poll_voters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `poll_id` int(11) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `voted_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `poll_ip` (`poll_id`, `ip_address`)
);

-- 4. Témoignages (Testimonials)
CREATE TABLE `testimonials` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `position` varchar(255) DEFAULT NULL,
  `content` TEXT NOT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `active` enum('Yes','No') NOT NULL DEFAULT 'Yes',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
);

-- 5. FAQ
CREATE TABLE `faqs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `question` varchar(255) NOT NULL,
  `answer` LONGTEXT NOT NULL,
  `active` enum('Yes','No') NOT NULL DEFAULT 'Yes',
  `position_order` int(11) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
);

-- 6. MODIFICATION V2.9.3 (Modération des témoignages)
ALTER TABLE `testimonials` MODIFY COLUMN `active` ENUM('Yes', 'No', 'Pending') NOT NULL DEFAULT 'Pending';

-- 7. NOUVEAUTÉS V2.9.4 (Slider Personnalisé)
CREATE TABLE `slides` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `image_url` varchar(255) NOT NULL,
  `link_url` varchar(255) DEFAULT '#',
  `position_order` int(11) NOT NULL DEFAULT 0,
  `active` enum('Yes','No') NOT NULL DEFAULT 'Yes',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
);

ALTER TABLE `settings`
ADD COLUMN `homepage_slider` ENUM('Featured', 'Custom') NOT NULL DEFAULT 'Featured' COMMENT 'Choix entre articles (Featured) ou slider perso (Custom)';