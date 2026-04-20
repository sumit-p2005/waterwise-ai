<?php require_once __DIR__ . '/includes/header.php'; ?>
<section class="grid xl:grid-cols-[1.15fr_.85fr] gap-6">
  <div class="space-y-6">
    <article class="glass-card neo-card p-7 tilt-card reveal">
      <p class="badge-chip inline-block">Offline AI + KNN</p>
      <h2 class="section-title mt-3">Futuristic Water Intelligence Dashboard</h2>
      <p class="section-sub">Predict water quality, map risk hotspots, and take action with real-time recommendations. Fully local. Fully private.</p>
      <div class="grid sm:grid-cols-3 gap-3 mt-5">
        <a href="calculator.php" class="glass-card p-4 glow-hover ripple">
          <div class="font-semibold">Water Lab</div>
          <p class="text-sm opacity-80">Calculate, compare and save</p>
        </a>
        <a href="map.php" class="glass-card p-4 glow-hover ripple">
          <div class="font-semibold">Geo Intelligence</div>
          <p class="text-sm opacity-80">Clustered hotspots + insights</p>
        </a>
        <a href="dashboard.php" class="glass-card p-4 glow-hover ripple">
          <div class="font-semibold">Executive KPIs</div>
          <p class="text-sm opacity-80">Trends, badges, leaderboard</p>
        </a>
      </div>
    </article>

    <div class="grid md:grid-cols-3 gap-4 reveal">
      <article class="glass-card p-4 floaty"><h3 class="font-semibold">Smart Suggestions</h3><p class="text-sm section-sub">Dynamic recommendations based on predicted category.</p></article>
      <article class="glass-card p-4 floaty" style="animation-delay:.2s"><h3 class="font-semibold">Recent Activity Feed</h3><p class="text-sm section-sub">Latest submissions pushed into analytics pipeline.</p></article>
      <article class="glass-card p-4 floaty" style="animation-delay:.4s"><h3 class="font-semibold">Gamified Insights</h3><p class="text-sm section-sub">Safe Water Zone and High Risk detection.</p></article>
    </div>
  </div>

  <aside class="glass-card neo-card p-4 chat-panel reveal">
    <div>
      <h3 class="section-title">AI Copilot</h3>
      <p class="section-sub">ChatGPT-style local assistant</p>
      <div class="quick-chips mt-3">
        <button class="chip ripple" data-chat-chip="Predict water quality">Predict water quality</button>
        <button class="chip ripple" data-chat-chip="Is this safe to drink?">Is this safe to drink?</button>
      </div>
    </div>
    <div id="chatMessages" class="chat-messages"></div>
    <form id="chatForm" class="grid gap-2">
      <input id="chatInput" class="input" placeholder="Enter 9 values: Temp DO pH BOD FS Nitrate FC TC Conductivity" />
      <button class="btn-primary ripple px-4 py-2">Send Message</button>
    </form>
  </aside>
</section>
<script src="assets/js/chat.js"></script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
