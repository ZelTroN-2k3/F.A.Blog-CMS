<?php
// -------------------------------------------------------------------------
// dashboard.php
// Point d'entrée principal du tableau de bord
// -------------------------------------------------------------------------

include "header.php";

// 1. Charger toute la logique (Traitements actions + Récupération données)
// Cela permet de garder ce fichier propre et lisible.
include "includes/dashboard_logic.php";
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Dashboard</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                    <li class="breadcrumb-item active">Dashboard</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <?php 
        // 2. Affichage conditionnel de la vue selon le rôle
        if ($user['role'] == "Admin") {
            include "includes/dashboard_admin.php";
        } elseif ($user['role'] == "Editor") {
            include "includes/dashboard_editor.php";
        } else {
            // Vue par défaut pour les utilisateurs simples (si nécessaire)
            echo "<div class='alert alert-info'>Welcome to your dashboard. Select an option from the menu.</div>";
        }
        ?>
    </div>
</section>

<?php
// 3. Inclure les scripts JS spécifiques au dashboard (Graphiques Admin uniquement)
if ($user['role'] == "Admin") {
    include "includes/dashboard_charts.js.php";
}

include "footer.php";
?>