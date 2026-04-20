const lazyStart = window.lazyInit || ((selector, cb) => { const el = document.querySelector(selector); if (el) cb(el); });
const trendCanvas = document.getElementById('trendChart');
const datasetTrendCanvas = document.getElementById('datasetTrendChart');
const filterForm = document.getElementById('filterForm');
const activityFeed = document.getElementById('activityFeed');
const leaderboardEl = document.getElementById('leaderboard');
const datasetSummary = document.getElementById('datasetSummary');
let trendChart;
let datasetChart;

function animateCounter(el, end) {
  const start = 0;
  const duration = 700;
  const startTime = performance.now();
  function step(now) {
    const p = Math.min((now - startTime) / duration, 1);
    const val = start + (end - start) * p;
    el.textContent = Number(val).toFixed(end % 1 ? 2 : 0);
    if (p < 1) requestAnimationFrame(step);
  }
  requestAnimationFrame(step);
}

function setBadge(stats) {
  const badge = document.getElementById('badgeZone');
  if (!badge) return;
  const safePct = Number(stats.safe_pct || 0);
  if (safePct >= 70) {
    badge.textContent = 'Safe Water Zone';
    badge.style.background = 'rgba(16,185,129,.18)';
  } else if (Number(stats.unsafe_count || 0) > 0) {
    badge.textContent = 'High Risk Area';
    badge.style.background = 'rgba(225,29,72,.18)';
  } else {
    badge.textContent = 'Monitoring Zone';
    badge.style.background = 'rgba(37,99,235,.18)';
  }
}

function renderTable(rows) {
  const tbody = document.querySelector('#latestTable tbody');
  if (!tbody) return;
  tbody.innerHTML = rows.map(r => `<tr class="border-b border-slate-700/20"><td>${r.id}</td><td>${Number(r.wqi).toFixed(2)}</td><td>${r.ph}</td><td>${r.tds}</td><td>${r.created_at}</td></tr>`).join('');
}

function renderFeed(rows) {
  if (!activityFeed) return;
  activityFeed.innerHTML = rows.slice(0, 8).map(r => `<div class="glass-card p-2">#${r.id} submitted WQI ${Number(r.wqi).toFixed(2)} at ${r.created_at}</div>`).join('');
}

function renderLeaderboard(rows) {
  if (!leaderboardEl) return;
  leaderboardEl.innerHTML = rows.map((r, i) => `<div class="glass-card p-2">#${i + 1} - (${r.lat_key}, ${r.lon_key}) | Avg WQI ${Number(r.avg_wqi).toFixed(2)} | Samples ${r.points}</div>`).join('') || '<div class="section-sub">No location data yet.</div>';
}

function renderTrend(rows) {
  if (!trendCanvas) return;
  const ctx = trendCanvas.getContext('2d');
  const grad1 = ctx.createLinearGradient(0, 0, 0, 320);
  grad1.addColorStop(0, 'rgba(37,99,235,.45)');
  grad1.addColorStop(1, 'rgba(37,99,235,.03)');

  if (trendChart) trendChart.destroy();
  trendChart = new Chart(trendCanvas, {
    type: 'line',
    data: {
      labels: rows.map(r => r.created_at),
      datasets: [
        { label: 'WQI', data: rows.map(r => Number(r.wqi)), borderColor: '#2563eb', backgroundColor: grad1, fill: true, tension: .35 },
        { label: 'pH', data: rows.map(r => Number(r.ph)), borderColor: '#0d9488', tension: .35 },
        { label: 'TDS', data: rows.map(r => Number(r.tds)), borderColor: '#0284c7', tension: .35 }
      ]
    },
    options: { responsive: true, maintainAspectRatio: false, animation: { duration: 900 } }
  });
}

function renderDatasetTrend(rows) {
  if (!datasetTrendCanvas) return;
  if (datasetChart) datasetChart.destroy();
  datasetChart = new Chart(datasetTrendCanvas, {
    type: 'line',
    data: {
      labels: rows.map(r => r.id),
      datasets: [
        { label: 'Dataset WQI', data: rows.map(r => Number(r.wqi)), borderColor: '#2563eb', tension: .3 },
        { label: 'Dataset pH', data: rows.map(r => Number(r.ph)), borderColor: '#0d9488', tension: .3 },
        { label: 'Dataset Conductivity', data: rows.map(r => Number(r.conductivity)), borderColor: '#0284c7', tension: .3 },
        { label: 'Dataset BOD', data: rows.map(r => Number(r.bio_chemical_oxygen_demand)), borderColor: '#f59e0b', tension: .3 }
      ]
    },
    options: { responsive: true, maintainAspectRatio: false, animation: { duration: 900 } }
  });
}

function renderDatasetSummary(stats) {
  if (!datasetSummary) return;
  datasetSummary.innerHTML = `
    <div class="glass-card p-2">Rows: ${Number(stats.total || 0)}</div>
    <div class="glass-card p-2">Avg WQI: ${Number(stats.avg_wqi || 0).toFixed(2)}</div>
    <div class="glass-card p-2">Min/Max WQI: ${Number(stats.min_wqi || 0).toFixed(2)} / ${Number(stats.max_wqi || 0).toFixed(2)}</div>
    <div class="glass-card p-2">Avg pH: ${Number(stats.avg_ph || 0).toFixed(2)}</div>
    <div class="glass-card p-2">Avg TDS: ${Number(stats.avg_tds || 0).toFixed(2)}</div>`;
}

async function loadDatasetGraphs() {
  const [trendRes, summaryRes] = await Promise.all([
    fetch('api/get_data.php?mode=dataset_trends'),
    fetch('api/get_data.php?mode=dataset_summary')
  ]);
  const trend = await trendRes.json();
  const summary = await summaryRes.json();
  renderDatasetTrend(trend.data || []);
  renderDatasetSummary(summary.stats || {});
}

async function loadDashboard(params = {}) {
  const query = new URLSearchParams(params).toString();
  const [dashRes, trendRes] = await Promise.all([
    fetch(`api/get_data.php?mode=dashboard&${query}`),
    fetch(`api/get_data.php?mode=trends&${query}`)
  ]);

  const dash = await dashRes.json();
  const trend = await trendRes.json();
  const s = dash.stats || {};

  const kpiAvg = document.getElementById('kpiAvg');
  const kpiSafe = document.getElementById('kpiSafePct');
  const kpiUnsafe = document.getElementById('kpiUnsafe');
  const kpiTotal = document.getElementById('kpiTotal');

  [kpiAvg, kpiSafe, kpiUnsafe, kpiTotal].forEach(el => el?.classList.remove('skeleton'));
  animateCounter(kpiAvg, Number(s.avg_wqi || 0));
  animateCounter(kpiSafe, Number(s.safe_pct || 0));
  animateCounter(kpiUnsafe, Number(s.unsafe_count || 0));
  animateCounter(kpiTotal, Number(s.total || 0));
  setBadge(s);

  renderTable(dash.recent || []);
  renderFeed(dash.recent || []);
  renderLeaderboard(dash.leaderboard || []);
  renderTrend(trend.data || []);
}

if (trendCanvas || datasetTrendCanvas) {
  lazyStart('#trendChart', () => loadDashboard());
  lazyStart('#datasetTrendChart', () => loadDatasetGraphs());

  filterForm?.addEventListener('submit', (e) => {
    e.preventDefault();
    const params = Object.fromEntries(new FormData(filterForm).entries());
    loadDashboard(params);
  });

  setInterval(async () => {
    const res = await fetch('api/get_data.php?mode=activity');
    const out = await res.json();
    renderFeed(out.data || []);
  }, 10000);
}

