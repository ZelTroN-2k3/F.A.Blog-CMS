---

# 📂 Documentation : Outil de Synchronisation des Fichiers

**Fichier :** `/admin/sync_files.php`  
**Accès :** Administrateur uniquement  
**Cible :** Dossier `/uploads/files/`

## 1. À quoi sert cet outil ?

Le gestionnaire de fichiers de **phpBlog** repose sur une base de données (table `files`). Si vous ajoutez ou supprimez des fichiers manuellement via FTP ou le gestionnaire de fichiers de votre hébergeur, la base de données ne le sait pas.

Cet outil sert à **réconcilier** la réalité du disque dur avec la base de données.

**Cas d'usage typiques :**
* Vous avez uploadé 50 images via FTP et vous voulez les voir dans la bibliothèque du CMS.
* Vous avez supprimé des vieux fichiers via FTP pour faire de la place, mais ils apparaissent toujours (en image brisée) dans l'admin.
* Vous avez migré le site et certains fichiers ont été corrompus ou perdus.

---

## 2. Fonctionnalités Principales

### A. Importation (Disque vers BDD)
Le script scanne le dossier cible (`../uploads/files/`). Pour chaque fichier trouvé :
1.  Il vérifie son extension (Sécurité).
2.  Il vérifie s'il existe déjà dans la table `files`.
3.  **Si non :** Il l'ajoute automatiquement dans la base de données avec la date actuelle et l'attribue à l'administrateur connecté.

### B. Nettoyage des Orphelins (BDD vers Disque)
*Option activable via la case à cocher "Clean Database Orphans".*

Le script parcourt toutes les entrées de la table `files` qui pointent vers le dossier cible. Pour chaque entrée :
1.  Il vérifie si le fichier physique existe réellement sur le serveur.
2.  **Si non :** Il supprime la ligne de la base de données pour éviter l'affichage d'images brisées.

---

## 3. Sécurité et Restrictions

Pour éviter de corrompre le site ou d'importer des fichiers dangereux, des sécurités strictes sont en place :

1.  **Dossier Cible Unique :**
    Le script ne scanne **QUE** le dossier `/uploads/files/`. Il ne touchera jamais aux avatars (`/uploads/avatars/`) ni aux images des articles (`/uploads/posts/`).

2.  **Extensions Autorisées (Whitelist) :**
    Le script ignorera tout fichier qui n'est pas dans cette liste :
    * **Images :** `jpg`, `jpeg`, `png`, `gif`, `webp`
    * **Documents :** `pdf`, `doc`, `docx`, `txt`
    * **Archives :** `zip`, `rar`
    * **Médias :** `mp3`, `mp4`
    * *Note : Les fichiers `.php`, `.exe`, `.html` ou `.htaccess` sont strictement ignorés.*

3.  **Protection Système :**
    Les dossiers système (`.` et `..`) ainsi que les fichiers d'index (`index.html`) sont ignorés.

---

## 4. Guide d'Utilisation

1.  Connectez-vous à l'administration et allez dans **Files > Sync**.
2.  Observez le tableau de bord :
    * **In Database :** Nombre total de fichiers enregistrés.
    * **Valid Files on Disk :** Nombre réel de fichiers valides trouvés dans le dossier.
3.  **Pour importer des fichiers FTP :**
    * Laissez la case "Clean Database Orphans" cochée (recommandé) ou décochez-la.
    * Cliquez sur le bouton vert **Run Synchronization**.
4.  **Résultat :**
    * Une alerte **Verte** listera les fichiers ajoutés.
    * Une alerte **Jaune** listera les entrées supprimées (si le nettoyage était activé).

---

## 5. Dépannage

* **Le nombre de fichiers sur le disque est plus grand que dans la BDD après synchro :**
    C'est normal si votre dossier contient des fichiers non autorisés (ex: des backups `.sql` ou des scripts `.php`). Ils sont ignorés par sécurité.
* **Le script tourne en boucle ou plante (Timeout) :**
    Si vous avez des milliers de fichiers (ex: 10 000+), le script peut dépasser le temps d'exécution PHP. Dans ce cas, essayez d'augmenter `max_execution_time` dans votre `php.ini` ou faites le ménage par petits lots.