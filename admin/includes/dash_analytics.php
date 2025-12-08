<div class="row">
    <div class="col-md-12">
        <div class="card card-dark">
            <div class="card-header border-0">
                <div class="d-flex justify-content-between">
                    <h3 class="card-title"><i class="fas fa-chart-area mr-1"></i> Traffic Overview</h3>
                    <a href="stats.php" class="text-white small">View Full Report</a>
                </div>
            </div>
            <div class="card-body">
                <canvas id="trafficChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card card-outline card-primary">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-globe mr-1"></i> Top Referrers</h3></div>
            <div class="card-body">
                <canvas id="referrerChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card card-outline card-warning">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-fire mr-1"></i> Most Viewed Pages</h3></div>
            <div class="card-body">
                <canvas id="topPagesChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="card collapsed-card">
    <div class="card-header bg-light">
        <h3 class="card-title text-muted"><i class="fas fa-chart-pie mr-1"></i> Content Statistics (Posts & Projects)</h3>
        <div class="card-tools"><button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button></div>
    </div>
    <div class="card-body" style="display: none;">
        <div class="row">
            <div class="col-md-4">
                <p class="text-center"><strong>Posts per Category</strong></p>
                <canvas id="postsPerCategoryChart" style="min-height: 200px; height: 200px; max-height: 200px; max-width: 100%;"></canvas>
            </div>
            <div class="col-md-4">
                <p class="text-center"><strong>Projects Overview</strong></p>
                <canvas id="projectsCategoryChart" style="min-height: 200px; height: 200px; max-height: 200px; max-width: 100%;"></canvas>
            </div>
            <div class="col-md-4">
                <p class="text-center"><strong>Publishing Trend</strong></p>
                <canvas id="postsPerMonthChart" style="min-height: 200px; height: 200px; max-height: 200px; max-width: 100%;"></canvas>
            </div>
        </div>
    </div>
</div>