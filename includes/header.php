<?php
declare(strict_types=1);
$current = basename($_SERVER['PHP_SELF']);
$pageKey = pathinfo($current, PATHINFO_FILENAME);
$nav = [
    ['key' => 'index', 'label' => 'Home', 'href' => 'index.php', 'icon' => 'house'],
    ['key' => 'calculator', 'label' => 'Lab', 'href' => 'calculator.php', 'icon' => 'flask-conical'],
    ['key' => 'map', 'label' => 'Map', 'href' => 'map.php', 'icon' => 'map-pinned'],
    ['key' => 'dashboard', 'label' => 'Insights', 'href' => 'dashboard.php', 'icon' => 'bar-chart-3'],
    ['key' => 'admin', 'label' => 'Admin', 'href' => 'admin.php', 'icon' => 'shield-check'],
];
?>
<!doctype html>
<html lang="en" class="scroll-smooth">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>WaterWise AI</title>
  <script>
    (function() {
      const mode = localStorage.getItem('ww-theme') || 'dark';
      if (mode === 'dark') document.documentElement.classList.add('dark');
      const collapsed = localStorage.getItem('ww-sidebar') === 'collapsed';
      if (collapsed) document.documentElement.classList.add('sidebar-collapsed');
    })();
  </script>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>tailwind.config = { darkMode: 'class' };</script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" />
  <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" />
  <link rel="stylesheet" href="assets/css/style.css" />
</head>
<body data-page="<?php echo htmlspecialchars($pageKey); ?>" class="theme-transition">
<div class="bg-layer" aria-hidden="true">
  <div class="gradient-blob blob-a"></div>
  <div class="gradient-blob blob-b"></div>
  <div class="gradient-blob blob-c"></div>
</div>
<div class="app-shell">
  <aside id="sidebar" class="sidebar glass-card neo-card">
    <div class="sidebar-head">
      <div class="brand-wrap"><span class="brand-dot"></span><span class="brand-text">WaterWise AI</span></div>
      <button id="sidebarToggle" class="icon-btn ripple" aria-label="Toggle sidebar"><i data-lucide="panel-left-close"></i></button>
    </div>
    <nav class="sidebar-nav">
      <?php foreach ($nav as $item): ?>
        <a href="<?php echo $item['href']; ?>" class="nav-item <?php echo $item['key'] === $pageKey ? 'active' : ''; ?> ripple">
          <i data-lucide="<?php echo $item['icon']; ?>"></i>
          <span><?php echo $item['label']; ?></span>
        </a>
      <?php endforeach; ?>
    </nav>
  </aside>
  <div class="content-shell">
    <header class="topbar glass-card">
      <button id="mobileSidebarToggle" class="icon-btn ripple lg:hidden" aria-label="Open menu"><i data-lucide="menu"></i></button>
      <div class="topbar-title">
        <h1 class="title-text"><?php echo ucfirst($pageKey); ?></h1>
        <p class="title-sub">Premium Water Intelligence Suite</p>
      </div>
      <div class="topbar-actions">
        <button id="themeToggle" class="theme-toggle ripple" aria-label="Toggle theme">
          <span class="theme-track"><i data-lucide="moon-star" class="moon"></i><i data-lucide="sun" class="sun"></i><span class="theme-knob"></span></span>
        </button>
      </div>
    </header>
    <main id="pageRoot" class="page-root">
