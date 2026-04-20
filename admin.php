<?php require_once __DIR__ . '/includes/header.php'; ?>
<section class="space-y-6 reveal">
  <article class="glass-card neo-card p-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
      <div>
        <h2 class="section-title">Admin Operations Center</h2>
        <p class="section-sub">Import, audit, edit, delete, and export water records.</p>
      </div>
      <div class="flex gap-2 flex-wrap">
        <button id="refreshStatus" class="btn-soft ripple px-4 py-2">Refresh Status</button>
        <button id="runImport" class="btn-primary ripple px-4 py-2">Import CSV</button>
        <a href="api/export_water_data.php" class="btn-soft ripple px-4 py-2">Export CSV</a>
      </div>
    </div>

    <div class="grid md:grid-cols-4 gap-3 mt-4" id="statusCards">
      <article class="glass-card p-4"><p class="text-sm opacity-75">Dataset Rows</p><p id="datasetRows" class="text-2xl font-bold">-</p></article>
      <article class="glass-card p-4"><p class="text-sm opacity-75">Water Data Rows</p><p id="waterRows" class="text-2xl font-bold">-</p></article>
      <article class="glass-card p-4"><p class="text-sm opacity-75">Dataset Avg WQI</p><p id="datasetAvg" class="text-2xl font-bold">-</p></article>
      <article class="glass-card p-4"><p class="text-sm opacity-75">CSV File Size</p><p id="csvSize" class="text-2xl font-bold">-</p></article>
    </div>

    <div id="adminLog" class="mt-4 rounded-xl bg-slate-900/75 text-slate-100 p-3 text-sm min-h-[96px] whitespace-pre-wrap"></div>
  </article>

  <article class="glass-card p-5">
    <div class="flex flex-wrap gap-2 mb-4 items-center">
      <input id="searchInput" class="input max-w-[260px]" placeholder="Search id / lat / lng" />
      <select id="riskFilter" class="select max-w-[180px]">
        <option value="all">All Risk Levels</option>
        <option value="safe">Safe (<=100)</option>
        <option value="unsafe">Unsafe (>200)</option>
      </select>
      <button id="loadEntries" class="btn-primary ripple px-4">Load Data</button>
      <span class="badge-chip">Clean View: Core Parameters</span>
    </div>

    <div class="overflow-auto max-h-[560px] rounded-xl border border-slate-300/35 dark:border-slate-700/35">
      <table class="w-full text-sm" id="adminTable">
        <thead class="sticky top-0 bg-slate-200/90 dark:bg-slate-900/90 backdrop-blur">
          <tr class="text-left">
            <th class="px-3 py-2">ID</th>
            <th class="px-3 py-2">Temp</th>
            <th class="px-3 py-2">DO</th>
            <th class="px-3 py-2">pH</th>
            <th class="px-3 py-2">BOD</th>
            <th class="px-3 py-2">Cond</th>
            <th class="px-3 py-2">WQI</th>
            <th class="px-3 py-2">Lat</th>
            <th class="px-3 py-2">Lon</th>
            <th class="px-3 py-2">Actions</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </article>
</section>
<script src="assets/js/admin.js"></script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
