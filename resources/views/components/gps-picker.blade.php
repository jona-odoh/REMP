@props([
    'latitude' => null,
    'longitude' => null,
    'lgas' => collect(),
    'latInputId' => 'latitude',
    'lngInputId' => 'longitude',
    'lgaSelectId' => 'lga_id',
])

@php
    $mapId = 'gps-picker-map-'.uniqid();
    $tileUrl = config('services.map.tile_url', 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png');
    $lgaCentroids = $lgas->mapWithKeys(fn ($lga) => [
        (string) $lga->id => [
            'name' => $lga->name,
            'lat' => $lga->centroid_lat !== null ? (float) $lga->centroid_lat : null,
            'lng' => $lga->centroid_lng !== null ? (float) $lga->centroid_lng : null,
        ],
    ]);
@endphp

<div class="sm:col-span-2 rounded-lg border border-slate-200 bg-slate-50 p-3 space-y-2">
    <div class="flex flex-wrap items-center justify-between gap-2">
        <div>
            <p class="text-xs font-semibold text-slate-800">Interactive Kogi State GPS Pin-Picker</p>
            <p class="text-[11px] text-slate-500">Click anywhere on the map (or drag the pin) to auto-fill Latitude &amp; Longitude.</p>
        </div>
        <div class="flex flex-wrap items-center gap-1.5">
            <button type="button" id="{{ $mapId }}-locate"
                    class="rounded border border-emerald-300 bg-emerald-50 px-2.5 py-1 text-[11px] font-semibold text-emerald-800 hover:bg-emerald-100">
                Use Device GPS
            </button>
            <button type="button" id="{{ $mapId }}-lga-btn"
                    class="rounded border border-slate-300 bg-white px-2.5 py-1 text-[11px] font-medium text-slate-700 hover:bg-slate-100">
                Center on Selected LGA
            </button>
            <button type="button" id="{{ $mapId }}-clear"
                    class="rounded border border-slate-300 bg-white px-2.5 py-1 text-[11px] font-medium text-slate-600 hover:bg-slate-100">
                Clear Pin
            </button>
        </div>
    </div>

    <div id="{{ $mapId }}" class="w-full h-64 rounded-md border border-slate-300 z-0" aria-label="Interactive GPS coordinate picker map"></div>
    <p id="{{ $mapId }}-status" class="text-[11px] text-slate-500"></p>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const latInput = document.getElementById(@json($latInputId));
    const lngInput = document.getElementById(@json($lngInputId));
    const lgaSelect = document.getElementById(@json($lgaSelectId));
    const statusEl = document.getElementById(@json($mapId.'-status'));
    const lgaCentroids = @json($lgaCentroids);
    const tileUrl = @json($tileUrl);

    if (!window.L || !latInput || !lngInput) {
        return;
    }

    const defaultCenter = [7.7337, 6.6906]; // Kogi State centre
    const initLat = parseFloat(latInput.value);
    const initLng = parseFloat(lngInput.value);
    const hasInitial = !Number.isNaN(initLat) && !Number.isNaN(initLng);

    const map = window.L.map(@json($mapId)).setView(
        hasInitial ? [initLat, initLng] : defaultCenter,
        hasInitial ? 13 : 8
    );

    window.L.tileLayer(tileUrl, {
        maxZoom: 18,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    let marker = null;

    const updateInputs = (lat, lng, message) => {
        latInput.value = Number(lat).toFixed(7);
        lngInput.value = Number(lng).toFixed(7);
        latInput.dispatchEvent(new Event('input', { bubbles: true }));
        lngInput.dispatchEvent(new Event('input', { bubbles: true }));
        if (statusEl && message) {
            statusEl.textContent = message;
        }
    };

    const placeMarker = (lat, lng, shouldUpdateInputs = true, message = '') => {
        if (marker) {
            marker.setLatLng([lat, lng]);
        } else {
            marker = window.L.marker([lat, lng], { draggable: true }).addTo(map);
            marker.on('dragend', () => {
                const pos = marker.getLatLng();
                updateInputs(pos.lat, pos.lng, `Pin moved to ${pos.lat.toFixed(6)}, ${pos.lng.toFixed(6)}`);
            });
        }

        if (shouldUpdateInputs) {
            updateInputs(lat, lng, message || `Selected coordinates: ${Number(lat).toFixed(6)}, ${Number(lng).toFixed(6)}`);
        }
    };

    if (hasInitial) {
        placeMarker(initLat, initLng, false);
        statusEl.textContent = `Current coordinates: ${initLat.toFixed(6)}, ${initLng.toFixed(6)}`;
    }

    map.on('click', (e) => {
        placeMarker(e.latlng.lat, e.latlng.lng, true);
    });

    const syncFromInputs = () => {
        const lat = parseFloat(latInput.value);
        const lng = parseFloat(lngInput.value);
        if (!Number.isNaN(lat) && !Number.isNaN(lng)) {
            placeMarker(lat, lng, false);
            map.panTo([lat, lng]);
        }
    };

    latInput.addEventListener('change', syncFromInputs);
    lngInput.addEventListener('change', syncFromInputs);

    const panToSelectedLga = (dropPin) => {
        if (!lgaSelect || !lgaSelect.value) {
            statusEl.textContent = 'Select an LGA first to center the map.';
            return;
        }
        const info = lgaCentroids[String(lgaSelect.value)];
        if (info && info.lat !== null && info.lng !== null) {
            map.setView([info.lat, info.lng], 11);
            if (dropPin) {
                placeMarker(info.lat, info.lng, true, `Centered on ${info.name} LGA centroid. Click or drag pin for exact site.`);
            } else {
                statusEl.textContent = `Map centered on ${info.name} LGA. Click the exact location to drop a pin.`;
            }
        }
    };

    if (lgaSelect) {
        lgaSelect.addEventListener('change', () => {
            if (!marker) {
                panToSelectedLga(false);
            }
        });
    }

    document.getElementById(@json($mapId.'-lga-btn'))?.addEventListener('click', () => panToSelectedLga(true));

    document.getElementById(@json($mapId.'-clear'))?.addEventListener('click', () => {
        if (marker) {
            map.removeLayer(marker);
            marker = null;
        }
        latInput.value = '';
        lngInput.value = '';
        latInput.dispatchEvent(new Event('input', { bubbles: true }));
        lngInput.dispatchEvent(new Event('input', { bubbles: true }));
        statusEl.textContent = 'Pin cleared (will default to LGA centroid on save if left blank).';
    });

    document.getElementById(@json($mapId.'-locate'))?.addEventListener('click', () => {
        if (!navigator.geolocation) {
            statusEl.textContent = 'Device geolocation is not supported by this browser.';
            return;
        }
        statusEl.textContent = 'Reading device GPS...';
        navigator.geolocation.getCurrentPosition(
            (pos) => {
                const { latitude, longitude } = pos.coords;
                map.setView([latitude, longitude], 14);
                placeMarker(latitude, longitude, true, `Device GPS captured: ${latitude.toFixed(6)}, ${longitude.toFixed(6)}`);
            },
            () => {
                statusEl.textContent = 'Unable to retrieve device GPS. Please click directly on the map instead.';
            },
            { enableHighAccuracy: true, timeout: 8000 }
        );
    });
});
</script>
