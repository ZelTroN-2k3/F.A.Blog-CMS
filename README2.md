# phpBlog v2.9.1 (Édition Mega Menu)
phpBlog - News, Blog & Magazine CMS

## Améliorations (Mise à jour v2.9.1)

Cette version est l'aboutissement de nombreuses optimisations visant à rendre le CMS plus professionnel, tant sur l'interface publique que dans l'administration.

---

### 🌟 Nouveautés Exclusives (Derniers Ajouts)

#### 🎨 Mega Menu "Next-Gen" (Frontend)
Le système de navigation a été entièrement repensé pour offrir une expérience utilisateur moderne :
* **Structure Avancée :** Un menu déroulant large (900px) centré, structuré en 3 colonnes stratégiques.
    1.  **Explore :** Liens rapides vers tous les articles et le flux RSS.
    2.  **Catégories :** Liste complète des catégories sur deux colonnes.
    3.  **Nouveautés (Visuel) :** Affichage dynamique des **4 derniers articles avec images miniatures** et dates directement dans le menu.
* **100% Responsive :** Grâce à une gestion CSS intelligente, le "Mega Menu" se transforme en accordéon fluide sur mobile, tandis qu'il reste en mode "carte flottante" sur PC.

#### 🚀 Administration Intelligente (UX/UI)
L'expérience administrateur a été fluidifiée pour gagner du temps :
* **Tableau de Bord Interactif :** Les cartes de statistiques (Brouillons, Commentaires en attente, etc.) sont désormais cliquables.
* **Flux de Travail Optimisé :** Le widget "Aperçu" redirige intelligemment. Si vous avez des commentaires en attente, le lien "Commentaires" vous y emmène directement. Sinon, il ouvre la liste complète.
* **Filtres Actifs :** Les pages `posts.php` et `comments.php` gèrent désormais nativement les filtres d'URL (ex: afficher uniquement les brouillons ou les articles en attente de validation).

#### 🛠️ Installation & Core
* **Gestion des Emails :** L'installateur a été mis à jour pour distinguer l'email du **Site** (utilisé pour les configurations) de l'email du **Compte Admin** (personnel).
* **Correctif Émoticônes :** Correction du bug qui empêchait le remplacement des codes (ex: `:)`) par leurs émojis graphiques (🙂) dans les commentaires.

---

### 🚀 Modules Majeurs (v2.9)

Ces modules transforment le blog en un véritable CMS professionnel :

* **Importateur de Flux RSS (Auto-Blogging) :**
    * Agrégation de contenu externe manuel ou automatique (CRON).
    * **Détection d'Images IA :** Algorithme capable de trouver l'image principale via `<media:content>`, `<enclosure>` ou par analyse du contenu HTML.
    * **Anti-Doublons :** Vérification des GUID pour ne jamais importer deux fois le même article.

* **Gestionnaire de Popups (Marketing) :**
    * Création de fenêtres modales avec éditeur visuel (Summernote).
    * **Ciblage Précis :** Choix des pages (Accueil vs Tout le site), délai d'apparition (en secondes) et fréquence (une fois par session ou à chaque chargement).
    * **Toggle Admin :** Activation/Désactivation rapide depuis la liste des popups.

* **Mode Maintenance Avancé :**
    * Page d'attente personnalisable pour les visiteurs.
    * **Accès Admin Préservé :** Les administrateurs peuvent se connecter et voir le site normalement même quand la maintenance est active.
    * Indicateur visuel d'état (ON/OFF) dans le menu admin.

---

### 🔧 Optimisations Techniques & Sécurité

* **Sécurité Renforcée :**
    * Migration complète vers `MySQLi` avec **requêtes préparées** (Protection SQL Injection).
    * Système Anti-CSRF sur tous les formulaires (GET et POST).
    * Intégration de **HTMLPurifier** pour sécuriser le contenu HTML utilisateur (Commentaires, RSS).
    * Hachage des mots de passe via `password_hash()` (Bcrypt).

* **Performance & SEO :**
    * Refonte de la table `settings` pour une lecture en une seule requête.
    * Gestion fine des méta-tags (OpenGraph, Twitter Cards) et URL Canoniques.
    * Chargement conditionnel des scripts JS pour alléger les pages.

---

### ✨ Fonctionnalités de Base (Héritage v2.8)

* **Engagement :** Système de "J'aime", "Favoris" et Badges de commentateurs (Vétéran, Actif...).
* **Social :** Connexion via Google (OAuth) et partage social intégré.
* **Contenu :** Système de Tags (mots-clés), recherche avancée, et temps de lecture estimé.
* **Interface :** Mode Sombre/Clair (Dark Mode) respectant les préférences système.

---

### 📋 Prérequis
* PHP 7.4 ou supérieur
* Extension PHP `mysqli` activée
* Extension PHP `mbstring` activée
* Permissions d'écriture sur `config.php` et le dossier `uploads/`

### 💿 Installation
1.  Uploadez les fichiers sur votre serveur.
2.  Créez une base de données MySQL vide.
3.  Rendez-vous sur `votre-site.com/install/` et suivez l'assistant.
4.  **Sécurité :** Supprimez le dossier `/install/` une fois terminé.