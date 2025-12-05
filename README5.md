# phpBlog v2.9.3 (Édition Complète)
phpBlog - News, Blog & Magazine CMS

## Vue d'ensemble

Cette version **v2.9.3** complète le cycle de développement du CMS en ajoutant une fonctionnalité de **Contenu Généré par l'Utilisateur (UGC)**. Les utilisateurs enregistrés peuvent désormais soumettre leurs propres témoignages, qui attendent une approbation de l'administrateur avant d'être publiés.

Ceci s'ajoute à la suite complète de modules interactifs (Sondages, FAQ) et d'outils système (Sauvegarde BDD) développés dans la v2.9.2.

---

### 🌟 Nouveautés Exclusives (v2.9.3)

#### 💬 Gestionnaire de Témoignages (Amélioré)
Le module a été étendu pour permettre la soumission publique :
* **Soumission Frontend :** Les utilisateurs connectés ont un nouveau lien dans leur menu profil pour accéder à un formulaire de soumission (`submit_testimonial.php`).
* **Flux de Modération Admin :**
    * Les témoignages soumis par les utilisateurs reçoivent le statut **"Pending"** (En attente) par défaut.
    * L'administrateur voit un badge de notification dans le menu pour les témoignages en attente.
    * L'admin peut **approuver** (passer à "Active") ou rejeter les soumissions.
* **Correction Émojis :** Les émojis (ex: `:)`) sont désormais correctement affichés (🙂) dans le slider des témoignages.

#### 🛠️ Outils Système & Maintenance
* **💾 Gestionnaire de Sauvegarde (Backup) :**
    * **1-Click Backup :** Génère et sauvegarde un fichier `.sql` complet dans un dossier sécurisé (`/backup-database/`) sur le serveur.
    * **Gestionnaire d'Historique :** Affiche la liste de toutes les sauvegardes (date, taille) avec des options pour **Télécharger** ou **Supprimer** d'anciens fichiers.

#### 📊 Modules d'Interaction (v2.9.2)
* **🗳️ Système de Sondages (Polls) :**
    * Gestion CRUD complète des sondages.
    * Widget Sidebar avec vote AJAX et affichage des résultats.
    * Protection anti-spam par IP et Cookies.
* **❓ Foire Aux Questions (FAQ) :**
    * Page publique dédiée (`faq.php`) avec interface en accordéon.
    * Gestion de l'ordre d'affichage.

---

### 🚀 Fonctionnalités Majeures (v2.9.1)

* **🎨 Mega Menu "Next-Gen" :** Navigation 100% responsive avec 3 colonnes et affichage des derniers articles.
* **📢 Importateur RSS Auto :** Auto-blogging avec détection d'images et anti-doublons.
* **🔔 Gestionnaire de Popups :** Fenêtres modales marketing ciblées.
* **🚧 Mode Maintenance :** Page d'attente personnalisable.

---

### 🛡️ Sécurité Renforcée

* **Protection Totale :** Anti-CSRF, Anti-XSS (HTMLPurifier), Anti-SQLi (Requêtes préparées).
* **Authentification :** Protection Brute Force et hachage `password_hash()`.
* **Installation :** Séparation des e-mails (Site vs Admin).

---

### 📋 Prérequis
* PHP 7.4 ou supérieur (8.0+ recommandé)
* Extension PHP `mysqli`, `mbstring`, `curl`, `gd`
* Apache avec `mod_rewrite` activé

---

### 🔄 Instructions de Mise à Jour (SQL)

Si vous mettez à jour depuis la v2.9.1 vers la **v2.9.3**, exécutez ces requêtes :

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

-- 6. MODIFICATION POUR LA V2.9.3 (Modération des témoignages)
ALTER TABLE `testimonials` MODIFY COLUMN `active` ENUM('Yes', 'No', 'Pending') NOT NULL DEFAULT 'Pending';