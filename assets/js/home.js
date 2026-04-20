window.addEventListener('load', async () => {
  const geoMapEl = document.getElementById('geoIntelMap');
  if (!geoMapEl || typeof L === 'undefined') return;

  const wqiColor = (wqi) => {
    if (wqi <= 50) return '#10b981';
    if (wqi <= 100) return '#2563eb';
    if (wqi <= 200) return '#f59e0b';
    return '#e11d48';
  };

  const statusLabel = (wqi) => {
    if (wqi <= 50) return 'Excellent';
    if (wqi <= 100) return 'Good';
    if (wqi <= 200) return 'Poor';
    return 'Unsafe';
  };

  const map = L.map('geoIntelMap', { zoomControl: true, dragging: true }).setView([22.9734, 78.6569], 5);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; OpenStreetMap'
  }).addTo(map);

  geoMapEl.classList.remove('skeleton');
  map.whenReady(() => setTimeout(() => map.invalidateSize(), 150));
  window.addEventListener('resize', () => map.invalidateSize());

  const markerLayer = (typeof L.markerClusterGroup === 'function') ? L.markerClusterGroup() : L.layerGroup();

  try {
    const dataRes = await fetch('api/get_data.php?mode=markers');
    if (!dataRes.ok) throw new Error('API request failed');
    const dataOut = await dataRes.json();
    const rows = Array.isArray(dataOut.data) ? dataOut.data : [];

    if (!rows.length) {
      L.marker([22.9734, 78.6569]).addTo(map)
        .bindPopup('<b>No location data yet</b><br>Add map submissions to see live markers.')
        .openPopup();
      return;
    }

    const bounds = [];

    rows.forEach((row) => {
      const lat = Number(row.latitude);
      const lon = Number(row.longitude);
      const wqi = Number(row.wqi || 0);
      if (!Number.isFinite(lat) || !Number.isFinite(lon)) return;

      bounds.push([lat, lon]);
      const color = wqiColor(wqi);

      const marker = L.circleMarker([lat, lon], {
        radius: 8,
        color,
        fillColor: color,
        fillOpacity: 0.95,
        weight: 1
      });

      marker.bindPopup(`
        <b>${row.city ?? 'Location'}, ${row.state ?? ''}</b><br>
        <b>WQI:</b> ${wqi.toFixed(2)}<br>
        <b>Status:</b> ${statusLabel(wqi)}<br>
        <b>pH:</b> ${row.ph ?? '-'}<br>
        <b>DO:</b> ${row.do_level ?? '-'}<br>
        <b>Conductivity:</b> ${row.conductivity ?? row.tds ?? '-'}
      `);

      markerLayer.addLayer(marker);

      L.circle([lat, lon], {
        radius: Math.max(80, wqi * 4),
        color,
        fillColor: color,
        fillOpacity: 0.08,
        weight: 1,
        interactive: false
      }).addTo(map);
    });

    map.addLayer(markerLayer);
    if (bounds.length) {
      map.fitBounds(bounds, { padding: [20, 20], maxZoom: 8 });
    }
  } catch (err) {
    console.error(err);
    L.marker([22.9734, 78.6569]).addTo(map)
      .bindPopup('<b>Map loading error</b><br>Could not fetch location markers.')
      .openPopup();
  }
});
