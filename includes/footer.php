    </main>
  </div>
</div>
<nav class="bottom-nav glass-card lg:hidden">
  <a href="index.php" class="bottom-item <?php echo $pageKey === 'index' ? 'active' : ''; ?>"><i data-lucide="house"></i><span>Home</span></a>
  <a href="calculator.php" class="bottom-item <?php echo $pageKey === 'calculator' ? 'active' : ''; ?>"><i data-lucide="flask-conical"></i><span>Lab</span></a>
  <a href="map.php" class="bottom-item <?php echo $pageKey === 'map' ? 'active' : ''; ?>"><i data-lucide="map-pinned"></i><span>Map</span></a>
  <a href="dashboard.php" class="bottom-item <?php echo $pageKey === 'dashboard' ? 'active' : ''; ?>"><i data-lucide="bar-chart-3"></i><span>Insights</span></a>
  <a href="admin.php" class="bottom-item <?php echo $pageKey === 'admin' ? 'active' : ''; ?>"><i data-lucide="shield-check"></i><span>Admin</span></a>
</nav>
<div id="toastContainer" class="toast-wrap"></div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
<script src="https://unpkg.com/lucide@latest"></script>
<script src="assets/js/main.js"></script>
</body>
</html>
