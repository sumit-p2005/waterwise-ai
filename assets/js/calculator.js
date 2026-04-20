const debounced = window.debounce || ((fn, d = 250) => { let t; return (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), d); }; });
const calcForm = document.getElementById('calcForm');
const calcResult = document.getElementById('calcResult');
const gauge = document.getElementById('gauge');
const recWrap = document.getElementById('recWrap');
const scoreRing = document.getElementById('scoreRing');
const scorePct = document.getElementById('scorePct');
const scoreLabel = document.getElementById('scoreLabel');
const neighborsWrap = document.getElementById('neighborsWrap');
const compareCanvas = document.getElementById('compareChart');
const geminiSuggestOutput = document.getElementById('geminiSuggestOutput');
const geminiMeta = document.getElementById('geminiMeta');
let compareChart;

function clamp(v, min, max) { return Math.max(min, Math.min(max, v)); }

function localWqi(sample) {
  const qTemperature = clamp((Math.abs(sample.temperature - 25) / 15) * 100, 0, 300);
  const qDo = clamp(((14.6 - sample.do_level) / 14.6) * 100, 0, 300);
  const qPh = clamp((Math.abs(sample.ph - 7) / 1.5) * 100, 0, 300);
  const qBod = clamp((sample.bio_chemical_oxygen_demand / 6) * 100, 0, 300);
  const qFs = clamp((sample.faecal_streptococci / 500) * 100, 0, 300);
  const qNitrate = clamp((sample.nitrate / 45) * 100, 0, 300);
  const qFc = clamp((sample.faecal_coliform / 500) * 100, 0, 300);
  const qTc = clamp((sample.total_coliform / 1000) * 100, 0, 300);
  const qConductivity = clamp((sample.conductivity / 1500) * 100, 0, 300);

  const wqi = (
    0.06 * qTemperature +
    0.20 * qDo +
    0.12 * qPh +
    0.12 * qBod +
    0.10 * qFs +
    0.10 * qNitrate +
    0.12 * qFc +
    0.10 * qTc +
    0.08 * qConductivity
  );
  return Number(wqi.toFixed(2));
}

function classify(wqi) {
  if (wqi <= 50) return { label: 'Excellent', color: '#10b981' };
  if (wqi <= 100) return { label: 'Good', color: '#2563eb' };
  if (wqi <= 200) return { label: 'Poor', color: '#f59e0b' };
  return { label: 'Unsafe', color: '#e11d48' };
}

function renderGauge(value, color) {
  if (!gauge) return;
  gauge.style.background = `conic-gradient(${color} ${Math.min(value, 300) / 300 * 360}deg, rgba(100,116,139,.25) 0)`;
  gauge.querySelector('span').textContent = value;
}

function renderScorecard(wqi, label) {
  const pct = Math.max(0, Math.min(100, Math.round((300 - wqi) / 3)));
  const dash = 377 - ((377 * pct) / 100);
  if (scoreRing) scoreRing.style.strokeDashoffset = `${dash}`;
  if (scorePct) scorePct.textContent = `${pct}%`;
  if (scoreLabel) scoreLabel.textContent = `${label} quality confidence`;
}

function renderComparison(calcWqi, predWqi) {
  if (!compareCanvas) return;
  const diff = Number((predWqi - calcWqi).toFixed(2));
  if (compareChart) compareChart.destroy();
  compareChart = new Chart(compareCanvas, {
    type: 'bar',
    data: {
      labels: ['Calculated', 'Predicted', 'Difference'],
      datasets: [{
        label: 'WQI Score',
        data: [calcWqi, predWqi, diff],
        borderRadius: 10,
        backgroundColor: ['rgba(37,99,235,.7)', 'rgba(13,148,136,.7)', 'rgba(244,63,94,.7)']
      }]
    },
    options: { responsive: true, plugins: { legend: { display: false } }, animation: { duration: 900 } }
  });
}

function renderNeighbors(neighbors = []) {
  if (!neighborsWrap) return;
  neighborsWrap.innerHTML = neighbors.map((n, i) => (`
    <div class="glass-card p-2">#${i + 1} | WQI ${Number(n.wqi).toFixed(2)} | Distance ${Number(n.distance).toFixed(2)}</div>
  `)).join('') || '<div class="section-sub">No neighbors available yet.</div>';
}

async function fetchRecommendation(wqi) {
  const res = await fetch(`api/recommend.php?wqi=${encodeURIComponent(wqi)}`);
  return res.json();
}

function escapeHtml(text) {
  return String(text || '').replace(/[&<>]/g, (m) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;' }[m]));
}

async function fetchGeminiSuggestions({ wqi, status, notes = '' }) {
  if (!geminiSuggestOutput || !geminiMeta) return;

  geminiMeta.textContent = 'Generating...';
  geminiSuggestOutput.innerHTML = '<div class="skeleton" style="height:72px;border-radius:.75rem;"></div>';

  try {
    const res = await fetch('api/gemini_suggestions.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ wqi, status, notes })
    });
    const out = await res.json();

    if (!out.ok) {
      geminiMeta.textContent = 'AI unavailable';
      geminiSuggestOutput.innerHTML = `<div class="text-sm">${escapeHtml(out.message || 'Suggestion request failed.')}</div>`;
      return;
    }

    const lines = String(out.suggestion || '')
      .split('\n')
      .map((l) => l.trim())
      .filter(Boolean);

    geminiSuggestOutput.innerHTML = `<div class="grid gap-2 text-sm">${lines.map((line) => `<div class="glass-card p-2">${escapeHtml(line)}</div>`).join('')}</div>`;
    geminiMeta.textContent = `Model: ${out.model || 'Gemini'}`;
  } catch {
    geminiMeta.textContent = 'AI unavailable';
    geminiSuggestOutput.innerHTML = '<div class="text-sm">Network error while requesting Gemini suggestions.</div>';
  }
}

const keys = ['temperature', 'do_level', 'ph', 'bio_chemical_oxygen_demand', 'faecal_streptococci', 'nitrate', 'faecal_coliform', 'total_coliform', 'conductivity'];

const handleRealtime = debounced(async () => {
  if (!calcForm) return;
  const form = new FormData(calcForm);
  const sample = {};
  keys.forEach(k => sample[k] = Number(form.get(k) || 0));

  const wqi = localWqi(sample);
  const status = classify(wqi);

  if (calcResult) {
    calcResult.textContent = `${status.label} (${wqi})`;
    calcResult.style.color = status.color;
  }

  renderGauge(wqi, status.color);
  renderScorecard(wqi, status.label);

  const rec = await fetchRecommendation(wqi);
  recWrap.innerHTML = (rec.recommendation?.tips || []).map(t => `<div class="glass-card p-3 text-sm">${t}</div>`).join('');
}, 220);

if (calcForm) {
  calcForm.addEventListener('input', handleRealtime);

  calcForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const data = Object.fromEntries(new FormData(calcForm).entries());
    for (const k in data) {
      if (data[k] === '') continue;
      data[k] = Number(data[k]);
    }

    const calcWqi = localWqi(data);

    const [saveRes, predRes] = await Promise.all([
      fetch('api/save_data.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(data) }),
      fetch('api/predict.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(data) })
    ]);

    const saved = await saveRes.json();
    const pred = await predRes.json();
    if (!saved.ok || !pred.ok) {
      showToast('Unable to process right now', 'error');
      return;
    }

    const predWqi = Number(pred.predicted_wqi || 0);
    const predStatus = pred.status?.label || classify(predWqi).label;

    document.getElementById('predictedValue').textContent = predWqi.toFixed(2);
    renderComparison(calcWqi, predWqi);
    renderNeighbors(pred.neighbors || []);

    const reportQuery = new URLSearchParams(data).toString();
    document.getElementById('reportLink').href = `report.php?${reportQuery}`;

    await fetchGeminiSuggestions({
      wqi: predWqi,
      status: predStatus,
      notes: `Calculated WQI: ${calcWqi}. Predicted WQI: ${predWqi}.`
    });

    showToast(`Saved. Predicted WQI: ${predWqi.toFixed(2)}`, 'success');
  });
}
