<?php require_once __DIR__ . '/includes/header.php'; ?>
<section class="grid xl:grid-cols-[1.15fr_.85fr] gap-6 reveal">
  <div class="space-y-5">
    <article class="glass-card p-5">
      <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
          <h2 class="section-title">Executive Dashboard</h2>
          <p class="section-sub">Live sample trends + imported dataset analytics</p>
        </div>
        <form id="filterForm" class="flex flex-wrap gap-2">
          <input class="input" type="date" name="date_from" />
          <input class="input" type="date" name="date_to" />
          <button class="btn-primary ripple px-4">Apply</button>
        </form>
      </div>

      <div class="grid md:grid-cols-4 gap-3 mt-4" id="kpiGrid">
        <div class="glass-card kpi"><div class="kpi-label">Avg WQI</div><div id="kpiAvg" class="kpi-value skeleton">0</div></div>
        <div class="glass-card kpi"><div class="kpi-label">Safe %</div><div id="kpiSafePct" class="kpi-value skeleton">0</div></div>
        <div class="glass-card kpi"><div class="kpi-label">Unsafe Count</div><div id="kpiUnsafe" class="kpi-value skeleton">0</div></div>
        <div class="glass-card kpi"><div class="kpi-label">Total Samples</div><div id="kpiTotal" class="kpi-value skeleton">0</div></div>
      </div>

      <div class="mt-4 flex flex-wrap gap-2">
        <span id="badgeZone" class="badge-chip">Badge pending</span>
        <span class="badge-chip">Water Quality Scorecard Active</span>
      </div>
    </article>

    <article class="glass-card p-5" style="height:420px;">
      <h3 class="font-semibold mb-2">Live Data Trends (WQI / pH / TDS)</h3>
      <canvas id="trendChart"></canvas>
    </article>

    <article class="glass-card p-5" style="height:420px;">
      <h3 class="font-semibold mb-2">Imported Dataset Trends (WQI / pH / Conductivity / BOD)</h3>
      <canvas id="datasetTrendChart"></canvas>
    </article>

    <article class="glass-card p-5">
      <h3 class="font-semibold mb-3">Prediction Activity Feed</h3>
      <div id="activityFeed" class="grid gap-2 text-sm"></div>
    </article>
  </div>

  <aside class="space-y-5">
    <article class="glass-card p-5">
      <h3 class="font-semibold mb-3">Imported Dataset Summary</h3>
      <div id="datasetSummary" class="grid gap-2 text-sm"></div>
    </article>

    <article class="glass-card p-5">
      <h3 class="font-semibold mb-3">Top Clean Locations</h3>
      <div id="leaderboard" class="grid gap-2 text-sm"></div>
    </article>

    <article class="glass-card p-5">
      <h3 class="font-semibold mb-3">Recent Submissions</h3>
      <div class="overflow-auto max-h-[420px]">
        <table class="w-full text-sm" id="latestTable">
          <thead><tr class="text-left"><th>ID</th><th>WQI</th><th>pH</th><th>TDS</th><th>Time</th></tr></thead>
          <tbody></tbody>
        </table>
      </div>
    </article>
  </aside>
</section>
<script src="assets/js/dashboard.js"></script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>

