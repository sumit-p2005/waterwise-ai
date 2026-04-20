<?php require_once __DIR__ . '/includes/header.php'; ?>
<section class="grid xl:grid-cols-[1.2fr_.8fr] gap-6">
  <article class="glass-card neo-card p-6 tilt-card reveal">
    <h2 class="section-title">Water Quality Lab</h2>
    <p class="section-sub">WQI now uses full dataset terms from Results_MADE.csv.</p>
    <form id="calcForm" class="grid md:grid-cols-2 gap-3 mt-4">
      <input name="temperature" type="number" step="0.01" required placeholder="Temperature" class="input" />
      <input name="do_level" type="number" step="0.01" required placeholder="Dissolved Oxygen" class="input" />
      <input name="ph" type="number" step="0.01" required placeholder="pH" class="input" />
      <input name="bio_chemical_oxygen_demand" type="number" step="0.01" required placeholder="Bio-Chemical Oxygen Demand" class="input" />
      <input name="faecal_streptococci" type="number" step="0.01" required placeholder="Faecal Streptococci" class="input" />
      <input name="nitrate" type="number" step="0.01" required placeholder="Nitrate" class="input" />
      <input name="faecal_coliform" type="number" step="0.01" required placeholder="Faecal Coliform" class="input" />
      <input name="total_coliform" type="number" step="0.01" required placeholder="Total Coliform" class="input" />
      <input name="conductivity" type="number" step="0.01" required placeholder="Conductivity" class="input" />
      <input name="latitude" type="number" step="0.000001" placeholder="Latitude (optional)" class="input" />
      <input name="longitude" type="number" step="0.000001" placeholder="Longitude (optional)" class="input" />
      <div class="md:col-span-2 flex gap-2 flex-wrap mt-2">
        <button class="btn-primary ripple px-4 py-2">Save + Predict</button>
        <a id="reportLink" href="#" target="_blank" class="btn-soft ripple px-4 py-2">Generate PDF</a>
      </div>
    </form>

    <div class="grid md:grid-cols-2 gap-4 mt-6">
      <div class="glass-card p-4">
        <h3 class="font-semibold mb-2">Prediction Comparison</h3>
        <canvas id="compareChart" height="180"></canvas>
      </div>
      <div class="glass-card p-4">
        <h3 class="font-semibold mb-2">Nearest Neighbors (KNN)</h3>
        <div id="neighborsWrap" class="space-y-2 text-sm"></div>
      </div>
    </div>

    <div class="glass-card p-5 mt-4">
      <div class="flex items-center justify-between mb-2">
        <h3 class="font-semibold">Gemini AI Suggestions</h3>
        <span class="badge-chip" id="geminiMeta">Waiting for prediction</span>
      </div>
      <div id="geminiSuggestOutput" class="text-sm section-sub">Suggestions will appear automatically after Save + Predict.</div>
    </div>
  </article>

  <aside class="space-y-4 reveal">
    <div class="glass-card p-5 text-center">
      <div id="gauge" class="gauge mx-auto"><span>0</span></div>
      <p id="calcResult" class="text-lg font-semibold mt-3">Waiting for input</p>
      <p class="section-sub">Predicted WQI: <b id="predictedValue">-</b></p>
    </div>
    <div class="glass-card p-5">
      <h3 class="font-semibold mb-3">Water Quality Scorecard</h3>
      <div class="flex items-center gap-4">
        <svg class="progress-ring" viewBox="0 0 140 140">
          <circle class="bg" cx="70" cy="70" r="60"></circle>
          <circle id="scoreRing" class="fg" cx="70" cy="70" r="60"></circle>
        </svg>
        <div>
          <div class="text-3xl font-bold" id="scorePct">0%</div>
          <div class="section-sub" id="scoreLabel">Quality Confidence</div>
        </div>
      </div>
    </div>
    <div class="glass-card p-5">
      <h3 class="font-semibold mb-2">Smart Suggestions</h3>
      <div id="recWrap" class="grid gap-2"></div>
    </div>
  </aside>
</section>
<script src="assets/js/calculator.js"></script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
