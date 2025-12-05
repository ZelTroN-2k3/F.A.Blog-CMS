# phpBlog v2.9.1 (Édition Mega Menu & Sécurité)
phpBlog - News, Blog & Magazine CMS

## Vue d'ensemble

Cette version **v2.9.1** est une refonte majeure du CMS original (v2.4). Elle transforme le blog en une plateforme professionnelle, sécurisée et automatisée, avec une interface utilisateur moderne et une administration puissante.

---

### 🌟 Nouveautés Exclusives (v2.9.1)

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