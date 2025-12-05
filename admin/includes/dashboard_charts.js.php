<script>
document.addEventListener('DOMContentLoaded', (event) => {
    
    // --- 1. GRAPHIQUE BARRES (Top 5 Articles) ---
    const ctxBar = document.getElementById('popularPostsChart');
    if (ctxBar) {
        const postLabels = <?php echo $chart_top_posts_labels_json; ?>;
        const postData = <?php echo $chart_top_posts_data_json; ?>;

        new Chart(ctxBar.getContext('2d'), {
            type: 'bar',
            data: {
                labels: postLabels,
                datasets: [{
                    label: 'Views',
                    data: postData,
                    backgroundColor: [
                        'rgba(40, 167, 69, 0.7)', // success
                        'rgba(0, 123, 255, 0.7)', // primary
                        'rgba(23, 162, 184, 0.7)', // info
                        'rgba(255, 193, 7, 0.7)',  // warning
                        'rgba(220, 53, 69, 0.7)'   // danger
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
                plugins: { legend: { display: false } }
            }
        });
    }

    // --- 2. GRAPHIQUE LIGNES (Publications par mois) ---
    const ctxLine = document.getElementById('postsPerMonthChart');
    if (ctxLine) {
        const monthLabels = <?php echo $chart_months_labels_json; ?>;
        const monthData = <?php echo $chart_months_data_json; ?>;
        
        new Chart(ctxLine.getContext('2d'), {
            type: 'line',
            data: {
                labels: monthLabels,
                datasets: [{
                    label: 'Posts',
                    data: monthData,
                    fill: true,
                    backgroundColor: 'rgba(0, 123, 255, 0.2)',
                    borderColor: 'rgba(0, 123, 255, 1)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
                plugins: { legend: { display: false } }
            }
        });
    }

    // --- 3. GRAPHIQUE PIE (Catégories) ---
    const ctxPie = document.getElementById('postsPerCategoryChart');
    if (ctxPie) {
        const catLabels = <?php echo $chart_cat_labels_json; ?>;
        const catData = <?php echo $chart_cat_data_json; ?>;
        
        new Chart(ctxPie.getContext('2d'), {
            type: 'pie',
            data: {
                labels: catLabels,
                datasets: [{
                    label: 'Posts',
                    data: catData,
                    backgroundColor: [ 
                        'rgba(0, 123, 255, 0.7)', // primary
                        'rgba(40, 167, 69, 0.7)',  // success
                        'rgba(255, 193, 7, 0.7)',  // warning
                        'rgba(220, 53, 69, 0.7)',  // danger
                        'rgba(23, 162, 184, 0.7)', // info
                        'rgba(108, 117, 125, 0.7)' // secondary
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    }

    // --- 4. GRAPHIQUE BARRES (Auteurs Actifs) ---
    const ctxBarAuthors = document.getElementById('activeAuthorsChart');
    if (ctxBarAuthors) {
        const authorLabels = <?php echo $chart_authors_labels_json; ?>;
        const authorData = <?php echo $chart_authors_data_json; ?>;

        new Chart(ctxBarAuthors.getContext('2d'), {
            type: 'bar', 
            data: {
                labels: authorLabels,
                datasets: [{
                    label: 'Published Articles',
                    data: authorData,
                    backgroundColor: [ 
                        'rgba(102, 51, 153, 0.7)',
                        'rgba(111, 66, 193, 0.7)',
                        'rgba(120, 81, 233, 0.7)',
                        'rgba(130, 97, 255, 0.7)',
                        'rgba(140, 112, 255, 0.7)'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
                plugins: { legend: { display: false } } 
            }
        });
    }
});
</script>