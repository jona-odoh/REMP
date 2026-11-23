@extends('layouts.app')

@section('title', 'Cultural & Tourism Map')
@section('heading', 'Interactive Kogi State Asset Map (SRID 4326)')

@section('content')
<div class="flex flex-wrap items-center justify-between gap-3">
    <form method="GET" action="{{ route('culture.assets.map') }}" class="flex flex-wrap items-center gap-2">
        <select name="category_id" aria-label="Filter by category" class="rounded-md border border-slate-300 bg-white px-3 py-1.5 text-sm">
            <option value="">All Categories</option>
            @foreach($categories as $cat)
                <option value="{{ $cat->id }}" @selected((string) ($filters['category_id'] ?? '') === (string) $cat->id)>{{ $cat->name }}</option>
            @endforeach
        </select>
        <select name="lga_id" aria-label="Filter by LGA" class="rounded-md border border-slate-300 bg-white px-3 py-1.5 text-sm">
            <option value="">All 21 LGAs</option>
            @foreach($lgas as $lga)
                <option value="{{ $lga->id }}" @selected((string) ($filters['lga_id'] ?? '') === (string) $lga->id)>{{ $lga->name }}</option>
            @endforeach
        </select>
        <select name="condition" aria-label="Filter by condition" class="rounded-md border border-slate-300 bg-white px-3 py-1.5 text-sm">
            <option value="">All Conditions</option>
            @foreach($conditions as $cond)
                <option value="{{ $cond->value }}" @selected(($filters['condition'] ?? '') === $cond->value)>{{ $cond->label() }}</option>
            @endforeach
        </select>
        <button type="submit" class="rounded-md bg-slate-800 px-3 py-1.5 text-sm font-medium text-white hover:bg-slate-700">Apply Filters</button>
        <a href="{{ route('culture.assets.map') }}" class="rounded-md border border-slate-300 bg-white px-3 py-1.5 text-sm text-slate-700 hover:bg-slate-50">Reset</a>
    </form>

    <span class="text-xs font-medium text-slate-600">Showing {{ $markers->count() }} mapped asset(s)</span>
</div>

<div class="rounded-lg bg-white border border-slate-200 shadow-sm overflow-hidden">
    <div id="ksmct-asset-map" class="w-full h-[560px] z-0" aria-label="Interactive map of Kogi State cultural and tourism assets"></div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const markersData = @json($markers);
    const tileUrl = @json($tileUrl);

    const map = window.L.map('ksmct-asset-map').setView([7.7337, 6.6906], 8);
    window.L.tileLayer(tileUrl, {
        maxZoom: 18,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    const bounds = [];
    markersData.forEach((asset) => {
        const marker = window.L.marker([asset.latitude, asset.longitude]).addTo(map);
        const approxBadge = asset.is_approximate ? '<div style="color:#b45309;font-size:11px;">Approximate (LGA centroid)</div>' : '';
        marker.bindPopup(`
            <div style="min-width:200px;font-family:sans-serif;">
                <div style="font-weight:700;font-size:13px;color:#0f172a;">${asset.name}</div>
                <div style="font-size:12px;color:#475569;margin-top:2px;">${asset.category} · ${asset.lga} LGA</div>
                <div style="font-size:12px;margin-top:4px;">Condition: <strong>${asset.condition}</strong></div>
                ${approxBadge}
                <div style="margin-top:8px;">
                    <a href="${asset.url}" style="color:#047857;font-weight:600;text-decoration:underline;font-size:12px;">Open Asset Record →</a>
                </div>
            </div>
        `);
        bounds.push([asset.latitude, asset.longitude]);
    });

    if (bounds.length > 0) {
        map.fitBounds(bounds, { padding: [40, 40], maxZoom: 12 });
    }
});
</script>
@endsection
