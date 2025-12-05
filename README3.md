# phpBlog v2.9.2 (Édition Interactive)
phpBlog - News, Blog & Magazine CMS

## Vue d'ensemble

Cette version **v2.9.2** marque une étape majeure dans l'évolution du CMS. Elle transforme le blog en une plateforme riche et interactive grâce à l'ajout de modules d'engagement (Sondages), de preuve sociale (Témoignages) et de support (FAQ), tout en conservant les optimisations de la v2.9.1.

---

### 🌟 Nouveautés Exclusives (v2.9.2)

#### 📊 Modules d'Interaction & Marketing
De nouveaux outils ont été intégrés pour dynamiser votre site et engager votre audience :

* **🗳️ Système de Sondages (Polls) :**
    * **Création Dynamique :** Ajoutez des questions et des options de réponse à la volée depuis l'admin.
    * **Widget Sidebar :** Affichage automatique du dernier sondage actif.
    * **Vote AJAX :** Prise en compte du vote instantanée sans rechargement de page.
    * **Sécurité Anti-Spam :** Vérification par IP et Cookies pour limiter les votes multiples.

* **💬 Gestionnaire de Témoignages (Testimonials) :**
    * **Preuve Sociale :** Affichez les avis de vos clients ou lecteurs.
    * **Slider Homepage :** Intégration élégante d'un carrousel défilant sur la page d'accueil.
    * **Gestion Complète :** Administration des noms, postes, avatars et contenus.

* **❓ Foire Aux Questions (FAQ) :**
    * **Page Dédiée :** Une nouvelle page publique (`faq.php`) référencée pour le SEO.
    * **Accordéon Moderne :** Interface fluide permettant de dérouler les réponses au clic.
    * **Ordonnancement :** Gérez l'ordre d'affichage des questions depuis l'admin.

---

### 🚀 Rappel des Fonctionnalités v2.9.1

* **🎨 Mega Menu "Next-Gen" :** Navigation 100% responsive avec 3 colonnes (Explore, Catégories, Derniers Articles avec images).
* **📢 Importateur RSS Auto :** Auto-blogging avec détection d'images par IA et anti-doublons (GUID).
* **🔔 Gestionnaire de Popups :** Fenêtres modales marketing ciblées (Délai, Page, Fréquence).
* **🚧 Mode Maintenance :** Page d'attente personnalisable avec accès administrateur préservé.

---

### 🛡️ Sécurité & Technique (Renforcée)

* **Protection Totale :** Anti-CSRF sur tous les formulaires, nettoyage HTMLPurifier (Anti-XSS), et requêtes préparées (Anti-SQLi).
* **Authentification Blindée :** Blocage temporaire après échecs répétés (Brute Force) et hachage de mots de passe moderne.
* **Installation Sécurisée :** Séparation stricte entre l'Email du Site et l'Email de l'Admin.

---

### 📋 Prérequis
* PHP 7.4 ou supérieur (8.0+ recommandé)
* Extension PHP `mysqli` & `mbstring`
* Extension PHP `curl` (pour RSS) et `gd` (pour les images)
* Apache avec `mod_rewrite` activé

---

### 🔄 Instructions de Mise à Jour (SQL)

Si vous mettez à jour depuis la version v2.9.1 vers la **v2.9.2**, exécutez ces requêtes dans PHPMyAdmin pour créer les tables des nouveaux modules :

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