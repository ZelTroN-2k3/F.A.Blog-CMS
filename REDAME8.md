-----

# phpBlog v2.9.6 (Édition Complète)

phpBlog - News, Blog & Magazine CMS

## Vue d'ensemble

Cette version **v2.9.6** est l'aboutissement de la refonte du CMS. Elle transforme le blog en une plateforme professionnelle, riche, interactive et sécurisée, dotée d'outils d'engagement et de maintenance avancés.

Les ajouts finaux incluent un système de **Quiz Avancé** avec classement et un **Footer entièrement dynamique** géré depuis l'administration.

-----

### 🌟 Nouveautés Exclusives (v2.9.6)

#### 🎓 Gestionnaire de Quiz Avancé (Refonte majeure)

Le système de quiz a été entièrement reconstruit pour devenir un module d'engagement majeur :

  * **Système Multi-Quiz :** L'administrateur peut créer un nombre illimité de "conteneurs" de quiz (table `quizzes`).
  * **Propriétés du Quiz :** Chaque quiz possède son propre titre, une description (HTML), une image d'en-tête et un niveau de difficulté (Facile, Normal, Difficile, Expert).
  * **Frontend Dynamique :** La page `quiz.php` est désormais un portail :
      * **Galerie des Quiz :** Affiche tous les quiz actifs sous forme de cartes.
      * **Page de Quiz :** `quiz.php?id=X` affiche l'en-tête du quiz, la description, et lance le formulaire de questions.
  * **Système de Classement (Leaderboard) :**
      * Les utilisateurs doivent être connectés pour jouer.
      * Enregistre les tentatives, le score en pourcentage (%) et le temps en secondes dans la table `quiz_attempts`.
      * La page de quiz affiche le meilleur score personnel de l'utilisateur, la moyenne globale, et le **Top 9 des joueurs** pour ce quiz spécifique.
  * **Widget "Hall of Fame" :**
      * Un nouveau widget "intelligent" peut être placé dans la sidebar.
      * **Sur une page de quiz :** Affiche le classement et les statistiques *spécifiques* à ce quiz (style capture d'écran).
      * **Partout ailleurs (Accueil, Blog...) :** Affiche un "Hall of Fame" *global* des 10 meilleurs joueurs (basé sur le score moyen de tous les quiz), incluant leur avatar et le dernier quiz joué.

#### 📄 Gestionnaire de Pages de Footer (Nouveau)

  * **Contrôle Total :** Un nouveau menu "Pages Footer" dans l'admin permet de gérer 5 blocs de contenu fixes.
  * **Gestion de Contenu :** L'administrateur peut modifier le titre et le contenu (via l'éditeur Summernote) et activer/désactiver chaque page (Infos Légales, Moyens de Contact, Pages Consultées, Call-to-Action, Gages de Confiance).
  * **Affichage Dynamique :** La fonction `footer()` dans `core.php` récupère et affiche dynamiquement ces blocs dans le pied de page public, en s'intégrant à la structure existante.

#### 🎨 Interface d'Administration Améliorée

  * **Tableau de Bord :** Les "Info-Box" pour "Quiz" et "FAQ" affichent désormais un badge rouge "new" (identique à "Testimonials") pour signaler les éléments inactifs ou en attente.

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

#### 🖼️ Gestionnaire de Slider d'Accueil

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

-----

### 🛡️ Sécurité Renforcée

  * **Protection Totale :** Anti-CSRF (Tous formulaires), Anti-XSS (HTMLPurifier), Anti-SQLi (Requêtes préparées).
  * **Authentification :** Protection Brute Force (Blocage temporaire) et hachage `password_hash()`.
  * **Installation :** Séparation des e-mails (Site vs Admin).

-----

### 📋 Prérequis

  * PHP 7.4 ou supérieur (8.0+ recommandé)
  * Extension PHP `mysqli` & `mbstring`
  * Extension PHP `curl` (pour RSS) et `gd` (pour les images)
  * Apache avec `mod_rewrite` activé

-----

### 💿 Installation

1.  Uploadez les fichiers sur votre serveur.
2.  Créez une base de données MySQL vide.
3.  Rendez-vous sur `votre-site.com/install/` et suivez l'assistant.
4.  **Sécurité :** Supprimez le dossier `/install/` une fois terminé.

-----

### 🔄 Instructions de Mise à Jour (SQL)

Si vous mettez à jour depuis une version ancienne, appliquez les blocs SQL pertinents.

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

-- 5. Sondages (Polls)
CREATE TABLE `polls` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `question` varchar(255) NOT NULL,
  `active` enum('Yes','No') NOT NULL DEFAULT 'Yes',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
);

CREATE TABLE `poll_options` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `poll_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `votes` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `poll_id` (`poll_id`)
);

CREATE TABLE `poll_voters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `poll_id` int(11) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `voted_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `poll_ip` (`poll_id`, `ip_address`)
);

-- 6. Témoignages (Testimonials)
CREATE TABLE `testimonials` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `position` varchar(255) DEFAULT NULL,
  `content` TEXT NOT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `active` enum('Yes','No','Pending') NOT NULL DEFAULT 'Pending',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
);

-- 7. Slider Personnalisé
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
ADD COLUMN `homepage_slider` ENUM('Featured', 'Custom') NOT NULL DEFAULT 'Featured';

-- 8. NOUVEAU v2.9.6 : Gestionnaire de Quiz Avancé
-- (Supprimez l'ancienne table 'faqs' si elle n'est plus utilisée)
-- DROP TABLE IF EXISTS `faqs`;

CREATE TABLE `quizzes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` TEXT NULL,
  `image` varchar(255) NULL,
  `difficulty` ENUM('FACILE','NORMAL','DIFFICILE','EXPERT') NOT NULL DEFAULT 'NORMAL',
  `active` enum('Yes','No') NOT NULL DEFAULT 'Yes',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `quiz_questions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `quiz_id` int(11) DEFAULT NULL,
  `question` varchar(255) NOT NULL,
  `explanation` LONGTEXT NULL,
  `active` enum('Yes','No') NOT NULL DEFAULT 'Yes',
  `position_order` int(11) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `quiz_id` (`quiz_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `quiz_options` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `question_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `is_correct` enum('Yes','No') NOT NULL DEFAULT 'No',
  PRIMARY KEY (`id`),
  KEY `question_id` (`question_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. NOUVEAU v2.9.6 : Système de classement des Quiz
CREATE TABLE `quiz_attempts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `quiz_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `score` int(11) NOT NULL COMMENT 'Score en pourcentage (ex: 80)',
  `time_seconds` int(11) NOT NULL COMMENT 'Temps total en secondes',
  `attempt_date` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `quiz_id` (`quiz_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. NOUVEAU v2.9.6 : Pages de Footer
CREATE TABLE `footer_pages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `page_key` varchar(50) NOT NULL COMMENT 'Clé unique (ex: legal, contact)',
  `title` varchar(255) NOT NULL,
  `content` LONGTEXT NULL,
  `active` enum('Yes','No') NOT NULL DEFAULT 'Yes',
  PRIMARY KEY (`id`),
  UNIQUE KEY `page_key` (`page_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `footer_pages` (`id`, `page_key`, `title`, `content`, `active`) VALUES
(1, 'legal', 'Informations Légales', '<p>Veuillez rédiger vos informations légales ici...</p>', 'Yes'),
(2, 'contact_methods', 'Moyens de Contact', '<p>Veuillez rédiger vos moyens de contact ici...</p>', 'Yes'),
(3, 'most_viewed', 'Pages les plus Consultées', '<p>Rédigez ici un texte ou des liens vers vos pages populaires...</p>', 'No'),
(4, 'cta_buttons', 'Call-to-Action', '<p>Rédigez ici vos boutons d''action (ex: Newsletter, Contact)...</p>', 'No'),
(5, 'trust_badges', 'Gages de Confiance', '<p>Insérez ici vos images de gages de confiance...</p>', 'No');

```