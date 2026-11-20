@extends('layouts.app')

@section('title', 'Category 2: Geographic & Community Management (GIS Map) — Kogi State REMP')

@section('content')
<!-- Header & Filters -->
<div class="card">
  <div class="card-body py-3">
    <div class="row align-items-center">
      <div class="col-lg-5 mb-3 mb-lg-0">
        {{-- <span class="badge badge-primary mb-1">Category 2 &bull; Geographic &amp; Community GIS Management</span> --}}
        <h4 class="mb-0 font-weight-bold text-dark">Interactive Kogi State Electrification Map</h4>
      </div>
      <div class="col-lg-7">
        <form method="GET" action="{{ route('geography.index') }}" class="form-row justify-content-lg-end">
          <div class="col-sm-4 mb-2 mb-sm-0">
            <select name="lga_id" onchange="this.form.submit()" class="form-control form-control-sm">
              <option value="">All 3 Selected LGAs</option>
              @foreach($lgas as $lga)
                <option value="{{ $lga->id }}" @selected($lgaId == $lga->id)>{{ $lga->name }} ({{ $lga->communities_count }})</option>
              @endforeach
            </select>
          </div>
          <div class="col-sm-4 mb-2 mb-sm-0">
            <select name="status" onchange="this.form.submit()" class="form-control form-control-sm">
              <option value="">All Electricity Statuses</option>
              <option value="no_electricity" @selected($status === 'no_electricity')>Red: No Electricity</option>
              <option value="unreliable" @selected($status === 'unreliable')>Orange: Unreliable Supply</option>
              <option value="inadequate" @selected($status === 'inadequate')>Orange: Inadequate Supply</option>
              <option value="reliable" @selected($status === 'reliable')>Green: Relatively Reliable</option>
            </select>
          </div>
          <div class="col-sm-4 d-flex">
            <select name="verification" onchange="this.form.submit()" class="form-control form-control-sm">
              <option value="">All (Verified &amp; Reported)</option>
              <option value="verified" @selected($verificationFilter === 'verified')>Verified Locations Only</option>
              <option value="unverified" @selected($verificationFilter === 'unverified')>Reported / Unverified Only</option>
            </select>
            @if($lgaId || $status || $verificationFilter)
              <a href="{{ route('geography.index') }}" class="btn btn-secondary btn-sm ml-1"><i class="fas fa-redo"></i></a>
            @endif
          </div>
        </form>
      </div>
    </div>

    <hr class="my-2">
    <div class="d-flex flex-wrap justify-content-between align-items-center small">
      <div class="d-flex flex-wrap align-items-center">
        <strong class="mr-3">FRD Map Legend:</strong>
        <span class="mr-3"><span class="badge badge-danger">&nbsp;</span> No Electricity</span>
        <span class="mr-3"><span class="badge badge-warning">&nbsp;</span> Unreliable / Inadequate</span>
        <span class="mr-3"><span class="badge badge-success">&nbsp;</span> Relatively Reliable</span>
        <span class="mr-3"><span class="badge badge-primary">&nbsp;</span> Proposed Intervention Ring</span>
        <span><span class="badge badge-light border border-danger text-danger">Dashed</span> Unverified Public Report</span>
      </div>
      <div class="text-muted mt-1 mt-md-0">
        Showing <strong>{{ $communities->count() }}</strong> communities &amp; <strong>{{ $unverifiedReports->count() }}</strong> unverified public leads
      </div>
    </div>
  </div>
</div>

<!-- Interactive Leaflet GIS Map -->
<div class="card">
  <div class="card-body p-0">
    <div id="kogiGisMap" style="width: 100%; height: 480px;"></div>
  </div>
</div>

<!-- Admin / LGA Officer Management Forms -->
@if(auth()->user()->hasRoleOrEnum(['admin', 'lga_officer']))
  <div class="row">
    <div class="col-lg-4">
      @if(auth()->user()->hasRoleOrEnum(['admin']))
        <div class="card card-primary">
          <div class="card-header">
            <h4>Configure New LGA</h4>
          </div>
          <div class="card-body">
            <form method="POST" action="{{ route('geography.lgas.store') }}">
              @csrf
              <div class="form-group mb-2">
                <input type="text" name="name" placeholder="LGA Name (e.g. Lokoja)" required class="form-control form-control-sm">
              </div>
              <div class="form-group mb-3">
                <input type="text" name="code" placeholder="LGA Code (e.g. KOG-LKJ)" required class="form-control form-control-sm">
              </div>
              <button type="submit" class="btn btn-primary btn-sm btn-block"><i class="fas fa-plus mr-1"></i> Add LGA</button>
            </form>
          </div>
        </div>
      @endif

      <div class="card card-info">
        <div class="card-header">
          <h4>Register Ward under LGA</h4>
        </div>
        <div class="card-body">
          <form method="POST" action="{{ route('geography.wards.store') }}">
            @csrf
            <div class="form-group mb-2">
              <select name="lga_id" required class="form-control form-control-sm">
                <option value="">Select LGA...</option>
                @foreach($lgas as $lga)
                  <option value="{{ $lga->id }}">{{ $lga->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="form-group mb-3">
              <input type="text" name="name" placeholder="Ward Name (e.g. Bariki Ward)" required class="form-control form-control-sm">
            </div>
            <button type="submit" class="btn btn-info btn-sm btn-block"><i class="fas fa-plus mr-1"></i> Register Ward</button>
          </form>
        </div>
      </div>
    </div>

    <div class="col-lg-8">
      <div class="card card-success">
        <div class="card-header">
          <h4>Register New Community in Kogi State</h4>
          <div class="card-header-action">
            <span class="small text-muted">Click map above to auto-fill GPS</span>
          </div>
        </div>
        <div class="card-body">
          <form method="POST" action="{{ route('geography.communities.store') }}">
            @csrf
            <div class="form-row">
              <div class="form-group col-md-4">
                <label class="small font-weight-bold">Ward &amp; LGA *</label>
                <select name="ward_id" required class="form-control form-control-sm">
                  <option value="">Select Ward...</option>
                  @foreach($wards as $ward)
                    <option value="{{ $ward->id }}">{{ $ward->lga?->name }} — {{ $ward->name }}</option>
                  @endforeach
                </select>
              </div>
              <div class="form-group col-md-4">
                <label class="small font-weight-bold">Community Name *</label>
                <input type="text" name="name" required placeholder="e.g. Obehira-Eba" class="form-control form-control-sm">
              </div>
              <div class="form-group col-md-4">
                <label class="small font-weight-bold">Electricity Status *</label>
                <select name="electricity_status_enum" required class="form-control form-control-sm">
                  <option value="no_electricity">No Electricity (Red)</option>
                  <option value="unreliable">Unreliable Supply (Orange)</option>
                  <option value="inadequate">Inadequate Supply (Orange)</option>
                  <option value="reliable">Relatively Reliable (Green)</option>
                </select>
              </div>
              <div class="form-group col-md-3">
                <label class="small font-weight-bold">GPS Latitude *</label>
                <input type="number" step="0.000001" id="new_comm_lat" name="latitude" value="7.552000" required class="form-control form-control-sm">
              </div>
              <div class="form-group col-md-3">
                <label class="small font-weight-bold">GPS Longitude *</label>
                <input type="number" step="0.000001" id="new_comm_lng" name="longitude" value="6.235000" required class="form-control form-control-sm">
              </div>
              <div class="form-group col-md-3">
                <label class="small font-weight-bold">Est. Households</label>
                <input type="number" name="estimated_households" value="120" class="form-control form-control-sm">
              </div>
              <div class="form-group col-md-3">
                <label class="small font-weight-bold">Est. Population</label>
                <input type="number" name="estimated_population" value="720" class="form-control form-control-sm">
              </div>
              <div class="form-group col-md-9 mb-0">
                <label class="small font-weight-bold">Description &amp; Notes</label>
                <input type="text" name="description" placeholder="Rural agrarian settlement with grain mills and primary school..." class="form-control form-control-sm">
              </div>
              <div class="form-group col-md-3 d-flex align-items-end mb-0">
                <button type="submit" class="btn btn-success btn-sm btn-block"><i class="fas fa-save mr-1"></i> Register Community</button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
@endif

<!-- Registered Communities Directory Table -->
<div class="card">
  <div class="card-header">
    <h4>Registered Communities Directory ({{ $communities->count() }})</h4>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive" style="max-height: 450px;">
      <table class="table table-striped table-hover table-sm mb-0">
        <thead>
          <tr>
            <th>Community</th>
            <th>Ward / LGA</th>
            <th>GPS Coordinates</th>
            <th class="text-right">Households</th>
            <th>Status</th>
            <th>Verification</th>
            <th>Proposed Intervention</th>
            <th class="text-right">Actions</th>
          </tr>
        </thead>
        <tbody>
          @foreach($communities as $c)
            <tr>
              <td class="font-weight-bold">{{ $c->name }}</td>
              <td>{{ $c->ward?->name }} &bull; <strong>{{ $c->ward?->lga?->name }}</strong></td>
              <td class="small text-muted">{{ number_format($c->latitude, 4) }}, {{ number_format($c->longitude, 4) }}</td>
              <td class="text-right">{{ number_format($c->estimated_households) }}</td>
              <td>
                <span class="badge text-uppercase {{ $c->electricity_status_enum === 'no_electricity' ? 'badge-danger' : ($c->electricity_status_enum === 'reliable' ? 'badge-success' : 'badge-warning') }}">
                  {{ str_replace('_', ' ', $c->electricity_status_enum) }}
                </span>
              </td>
              <td>
                @if($c->is_verified)
                  <span class="text-success font-weight-bold"><i class="fas fa-check-circle"></i> Verified</span>
                @else
                  <span class="text-warning font-weight-bold">Reported</span>
                @endif
              </td>
              <td class="text-primary font-weight-bold small">{{ $c->latestRecommendation?->solution_label ?? '—' }}</td>
              <td class="text-right text-nowrap">
                <a href="{{ route('communities.show', $c) }}" class="btn btn-outline-primary btn-sm">Profile</a>
                @if(auth()->user()->hasRoleOrEnum(['admin', 'field_officer', 'lga_officer']))
                  <a href="{{ route('assessments.create', ['community_id' => $c->id]) }}" class="btn btn-outline-success btn-sm">Assess</a>
                @endif
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  const map = L.map('kogiGisMap').setView([7.65, 6.70], 8);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 18,
    attribution: '&copy; OpenStreetMap contributors | Kogi State REMP GIS'
  }).addTo(map);

  const markersData = @json($mapMarkers);
  const unverifiedReports = @json($unverifiedReports);
  const colorMap = {
    'no_electricity': '#fc544b',
    'unreliable': '#ffa426',
    'inadequate': '#f59e0b',
    'reliable': '#47c363'
  };

  const bounds = [];

  markersData.forEach(item => {
    if (!item.lat || !item.lng) return;
    const marker = L.circleMarker([item.lat, item.lng], {
      radius: item.is_verified ? 8 : 6,
      fillColor: colorMap[item.status] || '#fc544b',
      color: item.has_intervention ? '#6777ef' : '#ffffff',
      weight: item.has_intervention ? 3 : 2,
      dashArray: item.is_verified ? null : '3, 3',
      opacity: 1,
      fillOpacity: 0.9
    }).addTo(map);

    bounds.push([item.lat, item.lng]);

    marker.bindPopup(`
      <div style="min-width:210px; font-size:12px;">
        <div class="font-weight-bold text-dark" style="font-size:14px;">${item.name}</div>
        <div class="text-muted mb-2">${item.ward} Ward &bull; <strong>${item.lga} LGA</strong></div>
        <div><strong>Status:</strong> ${item.status_label}</div>
        <div><strong>Verification:</strong> ${item.is_verified ? '<span class="text-success">Verified</span>' : '<span class="text-warning">Reported Lead</span>'}</div>
        <div><strong>Households:</strong> ${item.households ?? 'N/A'}</div>
        ${item.daily_kwh ? `<div><strong>Daily Demand:</strong> ${item.daily_kwh} kWh/day</div>` : ''}
        ${item.recommended_solution ? `<div class="mt-2 p-2 bg-light border rounded"><strong>${item.recommended_solution}</strong><br><small>Indicative Cost: &#8358;${item.cost_ngn}</small></div>` : ''}
        <a href="${item.profile_url}" class="btn btn-primary btn-sm btn-block mt-2 text-white">View Community Profile</a>
      </div>
    `);
  });

  unverifiedReports.forEach(rep => {
    if (!rep.latitude || !rep.longitude) return;
    L.circleMarker([rep.latitude, rep.longitude], {
      radius: 7,
      fillColor: '#ffa426',
      color: '#fc544b',
      weight: 2,
      dashArray: '4, 4',
      fillOpacity: 0.7
    }).addTo(map).bindPopup(`
      <div style="font-size:12px;">
        <div class="font-weight-bold text-danger">Public Report: ${rep.reference_number}</div>
        <div>${rep.custom_community_name || (rep.community ? rep.community.name : 'Reported Location')}</div>
        <div>Situation: ${rep.electricity_situation_enum.replace('_', ' ')}</div>
        <div>Status: ${rep.status_enum.toUpperCase()}</div>
      </div>
    `);
  });

  if (bounds.length > 0) {
    map.fitBounds(bounds, { padding: [30, 30] });
  }

  map.on('click', function (e) {
    const latInput = document.getElementById('new_comm_lat');
    const lngInput = document.getElementById('new_comm_lng');
    if (latInput && lngInput) {
      latInput.value = e.latlng.lat.toFixed(6);
      lngInput.value = e.latlng.lng.toFixed(6);
    }
  });
});
</script>
@endpush
