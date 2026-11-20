@extends('layouts.app')

@section('title', 'Category 3: Public Electricity Reporting Portal — Kogi State REMP')

@section('content')
<div class="row justify-content-center">
  <div class="col-lg-10">
    <!-- Hero Banner Card -->
    <div class="card card-primary">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col-md-8 mb-3 mb-md-0">
            {{-- <span class="badge badge-warning mb-1">Category 3 &bull; Participatory Public Reporting (No Account Required)</span> --}}
            <h4 class="font-weight-bold text-dark mb-1">Report Community Electricity Challenges in Kogi State</h4>
            <p class="text-muted small mb-0">
              Help Kogi State Government identify underserved communities across Okene, Idah, and Kabba/Bunu LGAs. Every submission receives a unique tracking reference.
            </p>
          </div>
          <div class="col-md-4 text-md-right">
            <a href="{{ route('public.report.track') }}" class="btn btn-outline-primary">
              <i class="fas fa-search mr-1"></i> Track Existing Report
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- Public Reporting Form -->
    <form method="POST" action="{{ route('public.report.store') }}" enctype="multipart/form-data">
      @csrf
      <div class="card">
        <div class="card-header">
          <h4>1. Community Location Details</h4>
        </div>
        <div class="card-body">
          <div class="form-row">
            <div class="form-group col-md-4">
              <label class="font-weight-bold">Select LGA *</label>
              <select id="rep_lga_id" name="lga_id" required class="form-control">
                <option value="">Choose LGA...</option>
                @foreach($lgas as $lga)
                  <option value="{{ $lga->id }}" @selected(old('lga_id') == $lga->id)>{{ $lga->name }} LGA</option>
                @endforeach
              </select>
            </div>
            <div class="form-group col-md-4">
              <label class="font-weight-bold">Select Ward *</label>
              <select id="rep_ward_id" name="ward_id" required class="form-control">
                <option value="">Choose Ward...</option>
                @foreach($wards as $ward)
                  <option value="{{ $ward->id }}" data-lga="{{ $ward->lga_id }}" @selected(old('ward_id') == $ward->id)>{{ $ward->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="form-group col-md-4">
              <label class="font-weight-bold">Registered Community</label>
              <select id="rep_community_id" name="community_id" class="form-control">
                <option value="">-- Select or enter new below --</option>
                @foreach($communities as $comm)
                  <option value="{{ $comm->id }}" data-ward="{{ $comm->ward_id }}" data-lat="{{ $comm->latitude }}" data-lng="{{ $comm->longitude }}" @selected(old('community_id') == $comm->id)>
                    {{ $comm->name }}
                  </option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="form-group mb-0">
            <label class="font-weight-bold">Or Enter Community / Settlement Name (if not listed)</label>
            <input type="text" name="custom_community_name" value="{{ old('custom_community_name') }}" placeholder="e.g. Oke-Offin New Settlement" class="form-control">
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-header">
          <h4>2. Electricity Situation &amp; Impact Assessment</h4>
        </div>
        <div class="card-body">
          <label class="font-weight-bold d-block mb-2">Current Electricity Situation *</label>
          <div class="row mb-3">
            <div class="col-md-6 mb-2">
              <div class="custom-control custom-radio border rounded p-3 bg-light">
                <input type="radio" id="sit1" name="electricity_situation_enum" value="no_electricity" checked class="custom-control-input">
                <label class="custom-control-label font-weight-bold" for="sit1">No Electricity</label>
                <div class="small text-muted">No national grid connection or functional power supply</div>
              </div>
            </div>
            <div class="col-md-6 mb-2">
              <div class="custom-control custom-radio border rounded p-3 bg-light">
                <input type="radio" id="sit2" name="electricity_situation_enum" value="irregular" class="custom-control-input">
                <label class="custom-control-label font-weight-bold" for="sit2">Irregular Electricity Supply</label>
                <div class="small text-muted">Frequent blackouts, transformer fault, or erratic hours</div>
              </div>
            </div>
            <div class="col-md-6 mb-2">
              <div class="custom-control custom-radio border rounded p-3 bg-light">
                <input type="radio" id="sit3" name="electricity_situation_enum" value="inadequate" class="custom-control-input">
                <label class="custom-control-label font-weight-bold" for="sit3">Inadequate Electricity Supply</label>
                <div class="small text-muted">Severe low voltage / brownouts unable to power equipment</div>
              </div>
            </div>
            <div class="col-md-6 mb-2">
              <div class="custom-control custom-radio border rounded p-3 bg-light">
                <input type="radio" id="sit4" name="electricity_situation_enum" value="partial" class="custom-control-input">
                <label class="custom-control-label font-weight-bold" for="sit4">Electricity Only in Certain Parts</label>
                <div class="small text-muted">Partial coverage leaving major quarters unconnected</div>
              </div>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label class="font-weight-bold">How long has this problem existed? *</label>
              <select name="duration_of_problem" required class="form-control">
                <option value="Never Electrified (10+ Years)">Never Electrified (10+ Years)</option>
                <option value="Over 5 Years">Over 5 Years</option>
                <option value="1 to 5 Years">1 to 5 Years</option>
                <option value="6 to 12 Months">6 to 12 Months</option>
                <option value="Less than 6 Months">Less than 6 Months</option>
              </select>
            </div>
            <div class="form-group col-md-6">
              <label class="font-weight-bold">Estimated Affected Households (if known)</label>
              <input type="number" name="affected_households_estimate" value="{{ old('affected_households_estimate', 110) }}" min="1" class="form-control">
            </div>
          </div>

          <div class="form-group">
            <label class="font-weight-bold d-block">Affected Public &amp; Economic Facilities</label>
            <div class="d-flex flex-wrap">
              @foreach(['schools' => 'Schools', 'hospitals' => 'Hospitals / PHCs', 'businesses' => 'Businesses / Mills', 'water_supply' => 'Water Boreholes', 'public_lighting' => 'Street Lighting'] as $val => $lbl)
                <div class="custom-control custom-checkbox mr-4 mb-2">
                  <input type="checkbox" class="custom-control-input" id="fac_{{ $val }}" name="affected_facilities_json[]" value="{{ $val }}" @checked(in_array($val, ['schools', 'hospitals', 'businesses']))>
                  <label class="custom-control-label" for="fac_{{ $val }}">{{ $lbl }}</label>
                </div>
              @endforeach
            </div>
          </div>

          <div class="form-group mb-0">
            <label class="font-weight-bold">Detailed Description of the Challenge *</label>
            <textarea name="description" rows="3" required class="form-control" placeholder="Describe the electricity situation affecting homes, schools, health clinics, and businesses...">{{ old('description') }}</textarea>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-header">
          <h4>3. GPS Location Picker, Photo Evidence &amp; Optional Contact</h4>
          <div class="card-header-action">
            <button type="button" onclick="detectBrowserGps()" class="btn btn-info btn-sm">
              <i class="fas fa-crosshairs mr-1"></i> Auto-Detect My GPS
            </button>
          </div>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-lg-7 mb-3 mb-lg-0">
              <div id="publicPickerMap" class="rounded border" style="width:100%; height:240px;"></div>
              <small class="text-muted">Click on the map or drag the pin to mark your community in Kogi State.</small>
            </div>
            <div class="col-lg-5">
              <div class="form-row">
                <div class="form-group col-6">
                  <label class="small font-weight-bold">Latitude</label>
                  <input type="number" step="0.000001" id="rep_lat" name="latitude" value="{{ old('latitude', '7.554200') }}" class="form-control form-control-sm">
                </div>
                <div class="form-group col-6">
                  <label class="small font-weight-bold">Longitude</label>
                  <input type="number" step="0.000001" id="rep_lng" name="longitude" value="{{ old('longitude', '6.238500') }}" class="form-control form-control-sm">
                </div>
              </div>
              <div class="form-group">
                <label class="small font-weight-bold">Upload Supporting Photo (Optional, max 5MB)</label>
                <input type="file" name="photo" accept="image/*" class="form-control-file small">
              </div>
              <div class="form-row">
                <div class="form-group col-6 mb-0">
                  <label class="small font-weight-bold">Your Name (Optional)</label>
                  <input type="text" name="reporter_name" value="{{ old('reporter_name') }}" placeholder="Resident Name" class="form-control form-control-sm">
                </div>
                <div class="form-group col-6 mb-0">
                  <label class="small font-weight-bold">Phone (Optional)</label>
                  <input type="text" name="reporter_contact" value="{{ old('reporter_contact') }}" placeholder="0803 XXX XXXX" class="form-control form-control-sm">
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="card-footer bg-whitesmoke d-flex flex-wrap justify-content-between align-items-center">
          <small class="text-muted mb-2 mb-sm-0">Generates an instant <strong>KOGI-{{ date('Y') }}-XXXXX</strong> tracking reference.</small>
          <button type="submit" class="btn btn-primary btn-lg">
            <i class="fas fa-paper-plane mr-1"></i> Submit Electricity Challenge Report
          </button>
        </div>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
let pickerMap, pickerMarker;
document.addEventListener('DOMContentLoaded', function () {
  const lgaSelect = document.getElementById('rep_lga_id');
  const wardSelect = document.getElementById('rep_ward_id');
  const commSelect = document.getElementById('rep_community_id');
  const allWards = Array.from(wardSelect.options);
  const allComms = Array.from(commSelect.options);

  lgaSelect.addEventListener('change', function () {
    const lgaId = this.value;
    wardSelect.innerHTML = '<option value="">Choose Ward...</option>';
    allWards.forEach(opt => {
      if (opt.value && (!lgaId || opt.dataset.lga === lgaId)) wardSelect.appendChild(opt.cloneNode(true));
    });
    wardSelect.dispatchEvent(new Event('change'));
  });

  wardSelect.addEventListener('change', function () {
    const wardId = this.value;
    commSelect.innerHTML = '<option value="">-- Select or enter new below --</option>';
    allComms.forEach(opt => {
      if (opt.value && (!wardId || opt.dataset.ward === wardId)) commSelect.appendChild(opt.cloneNode(true));
    });
  });

  commSelect.addEventListener('change', function () {
    const selected = this.options[this.selectedIndex];
    if (selected && selected.dataset.lat && selected.dataset.lng) {
      setMapPin(parseFloat(selected.dataset.lat), parseFloat(selected.dataset.lng));
    }
  });

  pickerMap = L.map('publicPickerMap').setView([7.5542, 6.2385], 8);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 18, attribution: '&copy; OpenStreetMap' }).addTo(pickerMap);
  pickerMarker = L.marker([7.5542, 6.2385], { draggable: true }).addTo(pickerMap);
  pickerMap.on('click', (e) => setMapPin(e.latlng.lat, e.latlng.lng));
  pickerMarker.on('dragend', (e) => { const p = e.target.getLatLng(); setMapPin(p.lat, p.lng); });
});

function setMapPin(lat, lng) {
  document.getElementById('rep_lat').value = lat.toFixed(6);
  document.getElementById('rep_lng').value = lng.toFixed(6);
  if (pickerMarker && pickerMap) {
    pickerMarker.setLatLng([lat, lng]);
    pickerMap.panTo([lat, lng]);
  }
}

function detectBrowserGps() {
  if (!navigator.geolocation) { alert('Geolocation not supported.'); return; }
  navigator.geolocation.getCurrentPosition(
    (pos) => setMapPin(pos.coords.latitude, pos.coords.longitude),
    () => alert('Could not auto-detect GPS. Click your community on the map instead.')
  );
}
</script>
@endpush
