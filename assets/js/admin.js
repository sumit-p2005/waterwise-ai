const debounced = window.debounce || ((fn, d = 250) => { let t; return (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), d); }; });
const logEl = document.getElementById('adminLog');
const btnRefresh = document.getElementById('refreshStatus');
const btnImport = document.getElementById('runImport');
const btnLoad = document.getElementById('loadEntries');
const searchInput = document.getElementById('searchInput');
const riskFilter = document.getElementById('riskFilter');
const tbody = document.querySelector('#adminTable tbody');

function log(msg) {
  if (!logEl) return;
  logEl.textContent += `[${new Date().toLocaleTimeString()}] ${msg}\n`;
  logEl.scrollTop = logEl.scrollHeight;
}

function setText(id, value) {
  const el = document.getElementById(id);
  if (el) el.textContent = value;
}

async function loadStatus() {
  const res = await fetch('api/admin_status.php');
  const out = await res.json();
  if (!out.ok) return showToast('Failed to load status', 'error');

  setText('datasetRows', Number(out.dataset.total || 0));
  setText('waterRows', Number(out.water_data.total || 0));
  setText('datasetAvg', Number(out.dataset.avg_wqi || 0).toFixed(2));
  setText('csvSize', `${Number(out.csv.size_kb || 0).toFixed(2)} KB`);
  log('Status refreshed.');
}

async function runImport() {
  btnImport.disabled = true;
  btnImport.textContent = 'Importing...';
  try {
    const res = await fetch('api/admin_import.php', { method: 'POST' });
    const out = await res.json();
    if (!out.ok) throw new Error(out.message || 'Import failed');

    showToast('Dataset import successful', 'success');
    log(out.message || 'Import complete.');
    await loadStatus();
  } catch (err) {
    showToast('Dataset import failed', 'error');
    log(`Error: ${err.message}`);
  } finally {
    btnImport.disabled = false;
    btnImport.textContent = 'Import CSV';
  }
}

function cellInput(name, value, width = '120px') {
  return `<input class="input" style="min-width:${width};padding:.45rem .55rem" name="${name}" value="${value ?? ''}">`;
}

function rowTemplate(r) {
  return `<tr class="border-b border-slate-700/20" data-id="${r.id}">
    <td class="px-3 py-2">${r.id}</td>
    <td class="px-3 py-2">${cellInput('temperature', r.temperature, '110px')}</td>
    <td class="px-3 py-2">${cellInput('do_level', r.do_level, '110px')}</td>
    <td class="px-3 py-2">${cellInput('ph', r.ph, '90px')}</td>
    <td class="px-3 py-2">${cellInput('bio_chemical_oxygen_demand', r.bio_chemical_oxygen_demand, '110px')}</td>
    <td class="px-3 py-2">${cellInput('conductivity', r.conductivity, '110px')}</td>
    <td class="px-3 py-2 font-semibold">${Number(r.wqi).toFixed(2)}</td>
    <td class="px-3 py-2">${cellInput('latitude', r.latitude, '130px')}</td>
    <td class="px-3 py-2">${cellInput('longitude', r.longitude, '130px')}</td>
    <td class="px-3 py-2 whitespace-nowrap">
      <button class="btn-soft ripple px-2 py-1" data-action="save">Save</button>
      <button class="btn-soft ripple px-2 py-1" data-action="delete">Delete</button>
    </td>
  </tr>`;
}

async function loadEntries() {
  const query = new URLSearchParams({
    search: searchInput.value.trim(),
    risk: riskFilter.value
  }).toString();

  const res = await fetch(`api/admin_entries.php?${query}`);
  const out = await res.json();
  if (!out.ok) return showToast('Failed to load entries', 'error');

  tbody.innerHTML = (out.data || []).map(rowTemplate).join('');
  log(`Loaded ${out.data.length} entries.`);
}

async function saveRow(tr) {
  const id = Number(tr.dataset.id);
  const payload = { id };
  tr.querySelectorAll('input').forEach((input) => {
    payload[input.name] = input.value === '' ? '' : Number(input.value);
  });

  const res = await fetch('api/admin_entries.php', {
    method: 'PUT',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload)
  });
  const out = await res.json();
  if (!out.ok) return showToast('Update failed', 'error');
  showToast('Entry updated', 'success');
  log(`Updated row #${id}.`);
  loadEntries();
}

async function deleteRow(tr) {
  const id = Number(tr.dataset.id);
  const res = await fetch('api/admin_entries.php', {
    method: 'DELETE',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ id })
  });
  const out = await res.json();
  if (!out.ok) return showToast('Delete failed', 'error');
  showToast('Entry deleted', 'success');
  log(`Deleted row #${id}.`);
  tr.remove();
}

tbody?.addEventListener('click', (e) => {
  const btn = e.target.closest('button[data-action]');
  if (!btn) return;
  const tr = btn.closest('tr');
  const action = btn.getAttribute('data-action');
  if (action === 'save') saveRow(tr);
  if (action === 'delete') deleteRow(tr);
});

btnRefresh?.addEventListener('click', loadStatus);
btnImport?.addEventListener('click', runImport);
btnLoad?.addEventListener('click', loadEntries);
searchInput?.addEventListener('input', debounced(loadEntries, 350));
riskFilter?.addEventListener('change', loadEntries);

loadStatus();
loadEntries();
