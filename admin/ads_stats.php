<?php
include "header.php";

// --- 1. RÉCUPÉRATION DES DONNÉES (PHP/SQL) ---

// A. Évolution sur les 7 derniers jours
$days_labels = [];
$days_data = [];

// On pré-remplit les 7 derniers jours à 0 pour ne pas avoir de trous dans le graphique
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $days_labels[] = date('d/m', strtotime($date)); // Format jour/mois
    $days_data_map[$date] = 0;
}

$q_week = mysqli_query($connect, "
    SELECT DATE(clicked_at) as day_date, COUNT(id) as total 
    FROM ad_clicks 
    WHERE clicked_at >= DATE(NOW()) - INTERVAL 7 DAY
    GROUP BY day_date
");

while ($row = mysqli_fetch_assoc($q_week)) {
    if (isset($days_data_map[$row['day_date']])) {
        $days_data_map[$row['day_date']] = (int)$row['total'];
    }
}
$days_data = array_values($days_data_map); // Convertir en tableau indexé pour JS


// B. Évolution sur les 12 derniers mois
$months_labels = [];
$months_data = [];

$q_year = mysqli_query($connect, "
    SELECT DATE_FORMAT(clicked_at, '%Y-%m') as month_date, COUNT(id) as total 
    FROM ad_clicks 
    WHERE clicked_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
    GROUP BY month_date
    ORDER BY month_date ASC
");

while ($row = mysqli_fetch_assoc($q_year)) {
    // Convertir '2025-11' en 'Nov 2025'
    $months_labels[] = date('M Y', strtotime($row['month_date'] . '-01'));
    $months_data[] = (int)$row['total'];
}


// C. Top des Publicités (Répartition)
$ads_labels = [];
$ads_data = [];

$q_top = mysqli_query($connect, "
    SELECT a.name, COUNT(ac.id) as total 
    FROM ads a
    JOIN ad_clicks ac ON a.id = ac.ad_id
    GROUP BY a.id
    ORDER BY total DESC
    LIMIT 5
");

while ($row = mysqli_fetch_assoc($q_top)) {
    $ads_labels[] = htmlspecialchars($row['name']);
    $ads_data[] = (int)$row['total'];
}

// Encodage pour Javascript
$json_days_labels = json_encode($days_labels);
$json_days_data   = json_encode($days_data);
$json_months_labels = json_encode($months_labels);
$json_months_data   = json_encode($months_data);
$json_ads_labels    = json_encode($ads_labels);
$json_ads_data      = json_encode($ads_data);
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0"><i class="fas fa-chart-line"></i> Advertising Statistics</h1></div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="ads.php">Ads</a></li>
                    <li class="breadcrumb-item active">Stats</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        
        <div class="row">
            <div class="col-md-6">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">Clicks (Last 7 Days)</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="weekChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card card-info card-outline">
                    <div class="card-header">
                        <h3 class="card-title">Distribution by Ad (Top 5)</h3>
                    </div>
                    <div class="card-body">
                         <canvas id="pieChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card card-success card-outline">
                    <div class="card-header">
                        <h3 class="card-title">Monthly Evolution (Last 12 Months)</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="yearChart" style="min-height: 300px; height: 300px; max-height: 300px; max-width: 100%;"></canvas>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>

<?php include "footer.php"; ?>

<script>
// --- GRAPHIQUE SEMAINE (Ligne) ---
const ctxWeek = document.getElementById('weekChart').getContext('2d');
new Chart(ctxWeek, {
    type: 'line',
    data: {
        labels: <?php echo $json_days_labels; ?>,
        datasets: [{
            label: 'Number of Clicks',
            data: <?php echo $json_days_data; ?>,
            borderColor: '#007bff',
            backgroundColor: 'rgba(0, 123, 255, 0.1)',
            borderWidth: 2,
            fill: true,
            tension: 0.3 // Pour courber la ligne
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
    }
});

// --- GRAPHIQUE TOP PUBS (Camembert) ---
const ctxPie = document.getElementById('pieChart').getContext('2d');
new Chart(ctxPie, {
    type: 'doughnut',
    data: {
        labels: <?php echo $json_ads_labels; ?>,
        datasets: [{
            data: <?php echo $json_ads_data; ?>,
            backgroundColor: ['#f56954', '#00a65a', '#f39c12', '#00c0ef', '#3c8dbc'],
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});

// --- GRAPHIQUE ANNUEL (Barres) ---
const ctxYear = document.getElementById('yearChart').getContext('2d');
new Chart(ctxYear, {
    type: 'bar',
    data: {
        labels: <?php echo $json_months_labels; ?>,
        datasets: [{
            label: 'Monthly Clicks',
            data: <?php echo $json_months_data; ?>,
            backgroundColor: '#28a745',
            borderColor: '#28a745',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: { y: { beginAtZero: true } }
    }
});
</script>