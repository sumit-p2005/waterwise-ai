window.addEventListener('load', () => {
  const mapEl = document.getElementById('mapView');
  const insightPanel = document.getElementById('insightPanel');
  const mapForm = document.getElementById('mapForm');
  const wqiUpdateForm = document.getElementById('wqiUpdateForm');
  const selectedLocationId = document.getElementById('selectedLocationId');
  const newWqiInput = document.getElementById('newWqi');
  const locationSearchForm = document.getElementById('locationSearchForm');
  const locationSearchInput = document.getElementById('locationSearchInput');

  if (!mapEl || typeof L === 'undefined') {
    console.error('Map init skipped: map element or Leaflet missing.');
    return;
  }

  let markerRows = [];
  let geoLayer;
  let searchMarker;

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

  const setLatLng = (lat, lng) => {
    const latInput = document.getElementById('latitude');
    const lonInput = document.getElementById('longitude');
    if (latInput) latInput.value = Number(lat).toFixed(6);
    if (lonInput) lonInput.value = Number(lng).toFixed(6);
  };

  const setInsights = (data) => {
    if (!insightPanel) return;
    if (!data) {
      insightPanel.innerHTML = 'Click marker or map point to view full analysis.';
      return;
    }

    if (selectedLocationId) selectedLocationId.value = data.id || '';
    if (newWqiInput && data.wqi != null) newWqiInput.value = Number(data.wqi).toFixed(2);

    insightPanel.innerHTML = `
      <div class="grid gap-1">
        <div><b>WQI:</b> ${Number(data.wqi || 0).toFixed(2)} (${statusLabel(Number(data.wqi || 0))})</div>
        <div><b>Temp:</b> ${data.temperature ?? '-'} | <b>DO:</b> ${data.do_level ?? '-'}</div>
        <div><b>pH:</b> ${data.ph ?? '-'} | <b>BOD:</b> ${data.bio_chemical_oxygen_demand ?? '-'}</div>
        <div><b>FS:</b> ${data.faecal_streptococci ?? '-'} | <b>Nitrate:</b> ${data.nitrate ?? '-'}</div>
        <div><b>FC:</b> ${data.faecal_coliform ?? '-'} | <b>TC:</b> ${data.total_coliform ?? '-'}</div>
        <div><b>Conductivity:</b> ${data.conductivity ?? '-'}</div>
        <div><b>City/State:</b> ${data.city ?? '-'}, ${data.state ?? '-'}</div>
        <div><b>Coordinates:</b> ${Number(data.latitude || 0).toFixed(4)}, ${Number(data.longitude || 0).toFixed(4)}</div>
      </div>`;
  };

  const geoMap = L.map('mapView').setView([22.9734, 78.6569], 5);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; OpenStreetMap'
  }).addTo(geoMap);

  mapEl.classList.remove('skeleton');
  geoMap.whenReady(() => setTimeout(() => geoMap.invalidateSize(), 120));
  window.addEventListener('resize', () => geoMap.invalidateSize());

  geoMap.on('click', (e) => {
    setLatLng(e.latlng.lat, e.latlng.lng);
    if (selectedLocationId) selectedLocationId.value = '';
    setInsights({ latitude: e.latlng.lat, longitude: e.latlng.lng, wqi: 0 });
  });

  const renderGeoMarkers = (rows) => {
    if (geoLayer) geoMap.removeLayer(geoLayer);
    geoLayer = (typeof L.markerClusterGroup === 'function') ? L.markerClusterGroup() : L.layerGroup();

    const bounds = [];
    rows.forEach((row) => {
      const lat = Number(row.latitude);
      const lon = Number(row.longitude);
      if (!Number.isFinite(lat) || !Number.isFinite(lon)) return;

      bounds.push([lat, lon]);
      const marker = L.circleMarker([lat, lon], {
        radius: 8,
        color: wqiColor(Number(row.wqi || 0)),
        fillColor: wqiColor(Number(row.wqi || 0)),
        fillOpacity: 0.9,
        weight: 1
      });

      marker.bindPopup(`<b>${row.city ?? 'Location'}, ${row.state ?? ''}</b><br><b>WQI:</b> ${row.wqi}<br><b>Status:</b> ${statusLabel(Number(row.wqi))}<br><b>pH:</b> ${row.ph}<br><b>Conductivity:</b> ${row.conductivity}`);
      marker.on('click', () => {
        setLatLng(lat, lon);
        setInsights(row);
      });

      geoLayer.addLayer(marker);
    });

    geoMap.addLayer(geoLayer);
    if (bounds.length) geoMap.fitBounds(bounds, { padding: [20, 20], maxZoom: 8 });
  };

  const loadMarkerData = async () => {
    const res = await fetch('api/get_data.php?mode=markers');
    if (!res.ok) throw new Error('Failed to fetch markers');
    const payload = await res.json();
    markerRows = Array.isArray(payload.data) ? payload.data : [];
    renderGeoMarkers(markerRows);
  };

  const searchLocation = async (query) => {
    const q = (query || '').trim().toLowerCase();
    if (!q) return;

    const localMatch = markerRows.find((r) => {
      const city = String(r.city || '').toLowerCase();
      const state = String(r.state || '').toLowerCase();
      return city.includes(q) || state.includes(q);
    });

    if (localMatch) {
      const lat = Number(localMatch.latitude);
      const lon = Number(localMatch.longitude);
      if (Number.isFinite(lat) && Number.isFinite(lon)) {
        geoMap.setView([lat, lon], 11);
        setLatLng(lat, lon);
        setInsights(localMatch);
        if (searchMarker) geoMap.removeLayer(searchMarker);
        searchMarker = L.marker([lat, lon]).addTo(geoMap).bindPopup(`Matched: ${localMatch.city || 'Location'}`).openPopup();
        showToast('Location matched from local map data.', 'success');
        return;
      }
    }

    try {
      const url = `https://nominatim.openstreetmap.org/search?format=json&limit=1&q=${encodeURIComponent(query)}`;
      const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
      const out = await res.json();
      if (!Array.isArray(out) || !out.length) {
        showToast('Location not found.', 'error');
        return;
      }

      const lat = Number(out[0].lat);
      const lon = Number(out[0].lon);
      if (!Number.isFinite(lat) || !Number.isFinite(lon)) return;

      geoMap.setView([lat, lon], 12);
      setLatLng(lat, lon);
      if (searchMarker) geoMap.removeLayer(searchMarker);
      searchMarker = L.marker([lat, lon]).addTo(geoMap).bindPopup(out[0].display_name || 'Search result').openPopup();
      if (selectedLocationId) selectedLocationId.value = '';
      showToast('Location found. Coordinates auto-filled.', 'success');
    } catch {
      showToast('Search failed. Try another location.', 'error');
    }
  };

  locationSearchForm?.addEventListener('submit', (e) => {
    e.preventDefault();
    searchLocation(locationSearchInput?.value || '');
  });

  wqiUpdateForm?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const id = Number(selectedLocationId?.value || 0);
    const wqi = Number(newWqiInput?.value || -1);

    if (!id) {
      showToast('Select a location marker first.', 'error');
      return;
    }
    if (wqi < 0) {
      showToast('Enter a valid WQI value.', 'error');
      return;
    }

    const res = await fetch('api/update_location_wqi.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id, wqi })
    });
    const out = await res.json();
    if (out.ok) {
      showToast('Location WQI updated.', 'success');
      await loadMarkerData();
    } else {
      showToast(out.message || 'WQI update failed.', 'error');
    }
  });

  mapForm?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const data = Object.fromEntries(new FormData(mapForm).entries());
    for (const k in data) {
      if (k === 'city' || k === 'state') continue;
      data[k] = Number(data[k]);
    }

    const res = await fetch('api/save_data.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(data)
    });
    const out = await res.json();
    if (out.ok) {
      showToast('Location data saved.', 'success');
      await loadMarkerData();
    } else {
      showToast('Save failed.', 'error');
    }
  });

  loadMarkerData().catch((err) => {
    console.error(err);
    L.marker([22.9734, 78.6569]).addTo(geoMap)
      .bindPopup('<b>Map loading error</b><br>Could not fetch location markers.')
      .openPopup();
    showToast('Unable to load map markers.', 'error');
  });
});
