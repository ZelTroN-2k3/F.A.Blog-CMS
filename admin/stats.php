<?php
include "header.php";

// SÉCURITÉ : Admin Uniquement
if ($user['role'] != "Admin") {
    echo '<meta http-equiv="refresh" content="0; url=dashboard.php">'; exit;
}

// --- CALCULS STATISTIQUES (Derniers 30 jours) ---

// 1. Total Visites & Visiteurs Uniques
$q_total = mysqli_query($connect, "SELECT COUNT(*) as total, COUNT(DISTINCT ip_address) as unique_visitors FROM visitor_analytics WHERE visit_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
$stats_overview = mysqli_fetch_assoc($q_total);

// 2. Visites par jour (Graphique)
$q_daily = mysqli_query($connect, "SELECT DATE_FORMAT(visit_date, '%Y-%m-%d') as day, COUNT(*) as count FROM visitor_analytics WHERE visit_date >= DATE_SUB(NOW(), INTERVAL 30 DAY) GROUP BY day ORDER BY day ASC");
$labels_days = [];
$data_days = [];
while ($r = mysqli_fetch_assoc($q_daily)) {
    $labels_days[] = date('d M', strtotime($r['day']));
    $data_days[] = $r['count'];
}

// 3. Navigateurs & OS (Analyse simplifiée)
$q_agents = mysqli_query($connect, "SELECT user_agent FROM visitor_analytics WHERE visit_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
$browsers = ['Chrome' => 0, 'Firefox' => 0, 'Safari' => 0, 'Edge' => 0, 'Other' => 0];
$os_list = ['Windows' => 0, 'Mac' => 0, 'Linux' => 0, 'Android' => 0, 'iOS' => 0, 'Other' => 0];

while ($r = mysqli_fetch_assoc($q_agents)) {
    $ua = $r['user_agent'];
    // Browser
    if (strpos($ua, 'Chrome') !== false) $browsers['Chrome']++;
    elseif (strpos($ua, 'Firefox') !== false) $browsers['Firefox']++;
    elseif (strpos($ua, 'Safari') !== false) $browsers['Safari']++;
    elseif (strpos($ua, 'Edg') !== false) $browsers['Edge']++;
    else $browsers['Other']++;
    
    // OS
    if (strpos($ua, 'Windows') !== false) $os_list['Windows']++;
    elseif (strpos($ua, 'Macintosh') !== false) $os_list['Mac']++;
    elseif (strpos($ua, 'Linux') !== false) $os_list['Linux']++;
    elseif (strpos($ua, 'Android') !== false) $os_list['Android']++;
    elseif (strpos($ua, 'iPhone') !== false || strpos($ua, 'iPad') !== false) $os_list['iOS']++;
    else $os_list['Other']++;
}

// 4. Top Pages
$q_pages = mysqli_query($connect, "SELECT page_url, COUNT(*) as count FROM visitor_analytics WHERE visit_date >= DATE_SUB(NOW(), INTERVAL 30 DAY) GROUP BY page_url ORDER BY count DESC LIMIT 10");

// 5. Top Referrers (D'où ils viennent)
$q_ref = mysqli_query($connect, "SELECT referrer, COUNT(*) as count FROM visitor_analytics WHERE visit_date >= DATE_SUB(NOW(), INTERVAL 30 DAY) AND referrer != 'Direct' AND referrer NOT LIKE '%" . $_SERVER['HTTP_HOST'] . "%' GROUP BY referrer ORDER BY count DESC LIMIT 10");

?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0"><i class="fas fa-chart-pie"></i> Traffic Analytics <small>(Last 30 Days)</small></h1></div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                    <li class="breadcrumb-item active">Stats</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        
        <div class="row">
            <div class="col-md-6 col-sm-6 col-12">
                <div class="info-box">
                    <span class="info-box-icon bg-info"><i class="fas fa-eye"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Page Views</span>
                        <span class="info-box-number"><?php echo number_format($stats_overview['total']); ?></span>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-sm-6 col-12">
                <div class="info-box">
                    <span class="info-box-icon bg-success"><i class="fas fa-users"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Unique Visitors</span>
                        <span class="info-box-number"><?php echo number_format($stats_overview['unique_visitors']); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card card-primary card-outline">
            <div class="card-header"><h3 class="card-title">Traffic Evolution</h3></div>
            <div class="card-body">
                <canvas id="trafficChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card card-info card-outline">
                    <div class="card-header"><h3 class="card-title">Top Viewed Pages</h3></div>
                    <div class="card-body p-0">
                        <table class="table table-striped table-sm">
                            <thead><tr><th>Page</th><th style="width: 60px">Views</th></tr></thead>
                            <tbody>
                                <?php while($p = mysqli_fetch_assoc($q_pages)): ?>
                                <tr>
                                    <td class="text-truncate" style="max-width: 300px;">
                                        <a href="<?php echo htmlspecialchars($p['page_url']); ?>" target="_blank" class="text-dark">
                                            <?php echo htmlspecialchars($p['page_url']); ?>
                                        </a>
                                    </td>
                                    <td><span class="badge bg-info"><?php echo $p['count']; ?></span></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card card-warning card-outline">
                    <div class="card-header"><h3 class="card-title">Top Referrers</h3></div>
                    <div class="card-body p-0">
                        <table class="table table-striped table-sm">
                            <thead><tr><th>Source</th><th style="width: 60px">Count</th></tr></thead>
                            <tbody>
                                <?php 
                                if(mysqli_num_rows($q_ref) == 0) echo '<tr><td colspan="2" class="text-center text-muted">No external referrers yet.</td></tr>';
                                while($r = mysqli_fetch_assoc($q_ref)): ?>
                                <tr>
                                    <td class="text-truncate" style="max-width: 300px;"><?php echo htmlspecialchars($r['referrer']); ?></td>
                                    <td><span class="badge bg-warning"><?php echo $r['count']; ?></span></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card card-danger card-outline">
                    <div class="card-header"><h3 class="card-title">Browsers</h3></div>
                    <div class="card-body"><canvas id="browserChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas></div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card card-success card-outline">
                    <div class="card-header"><h3 class="card-title">Operating Systems</h3></div>
                    <div class="card-body"><canvas id="osChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas></div>
                </div>
            </div>
        </div>

    </div>
</section>

<?php include "footer.php"; ?>

<script>
$(function () {
    // 1. Traffic Chart
    new Chart($('#trafficChart').get(0).getContext('2d'), {
        type: 'line',
        data: {
            labels: <?php echo json_encode($labels_days); ?>,
            datasets: [{
                label: 'Visits',
                backgroundColor: 'rgba(60,141,188,0.9)',
                borderColor: 'rgba(60,141,188,0.8)',
                pointRadius: 4,
                pointColor: '#3b8bba',
                pointStrokeColor: 'rgba(60,141,188,1)',
                pointHighlightFill: '#fff',
                pointHighlightStroke: 'rgba(60,141,188,1)',
                data: <?php echo json_encode($data_days); ?>,
                fill: false
            }]
        },
        options: {
            maintainAspectRatio: false,
            responsive: true,
            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
            plugins: { legend: { display: false } }
        }
    });

    // 2. Browser Chart
    new Chart($('#browserChart').get(0).getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode(array_keys($browsers)); ?>,
            datasets: [{
                data: <?php echo json_encode(array_values($browsers)); ?>,
                backgroundColor: ['#f56954', '#00a65a', '#f39c12', '#00c0ef', '#3c8dbc'],
            }]
        },
        options: { maintainAspectRatio: false, responsive: true }
    });

    // 3. OS Chart
    new Chart($('#osChart').get(0).getContext('2d'), {
        type: 'pie',
        data: {
            labels: <?php echo json_encode(array_keys($os_list)); ?>,
            datasets: [{
                data: <?php echo json_encode(array_values($os_list)); ?>,
                backgroundColor: ['#f56954', '#00a65a', '#f39c12', '#00c0ef', '#3c8dbc', '#d2d6de'],
            }]
        },
        options: { maintainAspectRatio: false, responsive: true }
    });
});
</script>