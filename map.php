<?php require_once __DIR__ . '/includes/header.php'; ?>
<section class="grid xl:grid-cols-[1.2fr_.8fr] gap-6">
  <article class="glass-card p-4 reveal map-frame">
    <div class="flex items-start justify-between mb-3">
      <div>
        <h2 class="section-title">Geo Intelligence Map</h2>
        <p class="section-sub">Clustered markers with full 9-parameter water analysis.</p>
      </div>
      <div class="legend">
        <div class="legend-item"><span class="legend-dot" style="background:#10b981"></span>Excellent</div>
        <div class="legend-item"><span class="legend-dot" style="background:#2563eb"></span>Good</div>
        <div class="legend-item"><span class="legend-dot" style="background:#f59e0b"></span>Poor</div>
        <div class="legend-item"><span class="legend-dot" style="background:#e11d48"></span>Unsafe</div>
      </div>
    </div>

    <form id="locationSearchForm" class="flex gap-2 mb-3">
      <input id="locationSearchInput" class="input" placeholder="Search city / place" />
      <button class="btn-primary ripple px-4" type="submit">Search</button>
    </form>

    <div id="mapView" class="skeleton"></div>
  </article>

  <aside class="space-y-4 reveal">
    <div class="glass-card p-5">
      <h3 class="font-semibold mb-2">Location Insights</h3>
      <div id="insightPanel" class="text-sm section-sub">Click marker or map point to view full analysis.</div>
      <form id="wqiUpdateForm" class="grid gap-2 mt-4">
        <input id="selectedLocationId" type="hidden" />
        <input id="newWqi" class="input" type="number" step="0.01" min="0" placeholder="Update WQI for selected location" />
        <button class="btn-primary ripple px-4 py-2" type="submit">Update WQI</button>
      </form>
    </div>

    <div class="glass-card p-5">
      <h3 class="font-semibold mb-3">Submit Geo Sample</h3>
      <form id="mapForm" class="grid gap-2">
        <input class="input" name="city" type="text" placeholder="City (optional)" />
        <input class="input" name="state" type="text" placeholder="State (optional)" />
        <input class="input" name="temperature" type="number" step="0.01" required placeholder="Temperature" />
        <input class="input" name="do_level" type="number" step="0.01" required placeholder="Dissolved Oxygen" />
        <input class="input" name="ph" type="number" step="0.01" required placeholder="pH" />
        <input class="input" name="bio_chemical_oxygen_demand" type="number" step="0.01" required placeholder="Bio-Chemical Oxygen Demand" />
        <input class="input" name="faecal_streptococci" type="number" step="0.01" required placeholder="Faecal Streptococci" />
        <input class="input" name="nitrate" type="number" step="0.01" required placeholder="Nitrate" />
        <input class="input" name="faecal_coliform" type="number" step="0.01" required placeholder="Faecal Coliform" />
        <input class="input" name="total_coliform" type="number" step="0.01" required placeholder="Total Coliform" />
        <input class="input" name="conductivity" type="number" step="0.01" required placeholder="Conductivity" />
        <input id="latitude" class="input" name="latitude" type="number" step="0.000001" required placeholder="Latitude" />
        <input id="longitude" class="input" name="longitude" type="number" step="0.000001" required placeholder="Longitude" />
        <button class="btn-primary ripple px-4 py-2 mt-2">Save Point</button>
      </form>
    </div>
  </aside>
</section>
<script src="assets/js/map.js"></script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
