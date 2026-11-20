@extends('layouts.app')

@section('title', 'Category 4: Multi-Step Field Assessment (Offline-Ready) — Kogi State REMP')

@section('content')
<div class="row justify-content-center">
  <div class="col-lg-11">
    <div class="card card-primary">
      <div class="card-body py-3 d-flex flex-wrap justify-content-between align-items-center">
        <div>
          <span class="badge badge-primary mb-1">Category 4A–G &bull; Digital Field Assessment &amp; Offline Draft Sync</span>
          <h4 class="mb-0 font-weight-bold text-dark">Multi-Step Community &amp; Facility Energy Assessment Form</h4>
        </div>
        <div class="mt-2 mt-md-0">
          <button type="button" onclick="saveOfflineDraft()" class="btn btn-warning btn-sm">
            <i class="fas fa-save mr-1"></i> Save Offline Draft
          </button>
          <button type="button" onclick="loadOfflineDraft()" class="btn btn-dark btn-sm">
            <i class="fas fa-folder-open mr-1"></i> Restore Draft
          </button>
          <div id="draft-status-text" class="small text-muted mt-1 text-right"></div>
        </div>
      </div>
    </div>

    <!-- Wizard Step Pills -->
    <ul class="nav nav-pills nav-fill mb-3 bg-white p-2 rounded shadow-sm">
      <li class="nav-item"><a href="javascript:void(0)" onclick="showStep(1)" id="tab-step-1" class="nav-link active font-weight-bold small">Step 1: Community &amp; Grid</a></li>
      <li class="nav-item"><a href="javascript:void(0)" onclick="showStep(2)" id="tab-step-2" class="nav-link font-weight-bold small">Step 2: Households</a></li>
      <li class="nav-item"><a href="javascript:void(0)" onclick="showStep(3)" id="tab-step-3" class="nav-link font-weight-bold small">Step 3: Schools &amp; Clinics</a></li>
      <li class="nav-item"><a href="javascript:void(0)" onclick="showStep(4)" id="tab-step-4" class="nav-link font-weight-bold small">Step 4: Businesses (SMEs)</a></li>
      <li class="nav-item"><a href="javascript:void(0)" onclick="showStep(5)" id="tab-step-5" class="nav-link font-weight-bold small">Step 5: Appliances &amp; Load</a></li>
    </ul>

    <form id="fieldAssessmentForm" method="POST" action="{{ route('assessments.store') }}" enctype="multipart/form-data">
      @csrf
      <div class="card">
        <div class="card-body">

          <!-- STEP 1 -->
          <div id="step-panel-1">
            <h5 class="font-weight-bold border-bottom pb-2 mb-3">Step 1: Community Electricity-Access &amp; Grid Verification</h5>
            <div class="form-row">
              <div class="form-group col-md-8">
                <label class="font-weight-bold">Select Verified Community *</label>
                <select name="community_id" id="ass_community_id" required class="form-control">
                  @foreach($communities as $c)
                    <option value="{{ $c->id }}" @selected($selectedCommunityId == $c->id)>
                      {{ $c->is_verified ? '✓ ' : '' }}{{ $c->name }} — {{ $c->ward?->name }} ({{ $c->ward?->lga?->name }} LGA)
                    </option>
                  @endforeach
                </select>
              </div>
              <div class="form-group col-md-4">
                <label class="font-weight-bold">Assessment Date *</label>
                <input type="date" name="assessment_date" value="{{ date('Y-m-d') }}" required class="form-control">
              </div>
              <div class="form-group col-md-4">
                <label class="font-weight-bold">Connected to National Grid? *</label>
                <select name="grid_connected_boolean" class="form-control">
                  <option value="0">No — Off-Grid Community</option>
                  <option value="1">Yes — Connected to Grid</option>
                </select>
              </div>
              <div class="form-group col-md-4">
                <label class="font-weight-bold">Average Supply Hours / Day *</label>
                <input type="number" step="0.5" name="average_supply_hours" value="0" min="0" max="24" required class="form-control">
              </div>
              <div class="form-group col-md-4">
                <label class="font-weight-bold">Power Interruption Frequency</label>
                <input type="text" name="interruption_frequency" value="Total Blackout / Daily Outages" class="form-control">
              </div>
              <div class="form-group col-md-4">
                <label class="font-weight-bold">Households WITH Electricity *</label>
                <input type="number" name="households_with_electricity" value="12" min="0" required class="form-control">
              </div>
              <div class="form-group col-md-4">
                <label class="font-weight-bold">Households WITHOUT Electricity *</label>
                <input type="number" name="households_without_electricity" value="145" min="0" required class="form-control">
              </div>
              <div class="form-group col-md-4">
                <label class="font-weight-bold">Alternative Energy Sources</label>
                <input type="text" name="alternative_energy_sources" value="Petrol Generators, Kerosene Lanterns" class="form-control">
              </div>
              <div class="form-group col-md-8">
                <label class="font-weight-bold">Field GPS Coordinates (Lat, Lng)</label>
                <input type="text" name="gps_coordinates" id="ass_gps" value="7.5542, 6.2385" class="form-control">
              </div>
              <div class="form-group col-md-4">
                <label class="font-weight-bold">Field Photo Evidence</label>
                <input type="file" name="photo" accept="image/*" class="form-control-file">
              </div>
            </div>
            <div class="text-right mt-3">
              <button type="button" onclick="showStep(2)" class="btn btn-primary">Next: Step 2 (Households) <i class="fas fa-arrow-right ml-1"></i></button>
            </div>
          </div>

          <!-- STEP 2 -->
          <div id="step-panel-2" class="d-none">
            <h5 class="font-weight-bold border-bottom pb-2 mb-3">Step 2: Household Energy Needs Sampling</h5>
            <div class="form-row">
              <div class="form-group col-md-6">
                <label class="font-weight-bold">Number of Households Sampled/Assessed</label>
                <input type="number" name="households_assessed_count" value="157" min="1" class="form-control">
              </div>
              <div class="form-group col-md-6">
                <label class="font-weight-bold">Primary Existing Household Energy Sources</label>
                <input type="text" name="household_existing_sources" value="Small Petrol Generators & Torchlights" class="form-control">
              </div>
            </div>
            <div class="d-flex justify-content-between mt-3">
              <button type="button" onclick="showStep(1)" class="btn btn-secondary"><i class="fas fa-arrow-left mr-1"></i> Back</button>
              <button type="button" onclick="showStep(3)" class="btn btn-primary">Next: Step 3 (Schools &amp; Clinics) <i class="fas fa-arrow-right ml-1"></i></button>
            </div>
          </div>

          <!-- STEP 3 -->
          <div id="step-panel-3" class="d-none">
            <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
              <h5 class="font-weight-bold mb-0">Step 3A: Schools Assessed in Community</h5>
              <button type="button" onclick="addSchoolRow()" class="btn btn-info btn-sm"><i class="fas fa-plus mr-1"></i> Add School</button>
            </div>
            <div id="schools-container" class="mb-4">
              <div class="form-row p-2 bg-light rounded border mb-2">
                <div class="col-md-5 mb-1"><input type="text" name="schools[0][name]" value="Community LGEA Primary School" placeholder="School Name" class="form-control form-control-sm"></div>
                <div class="col-md-3 mb-1"><input type="text" name="schools[0][school_type]" value="Public Primary" placeholder="School Type" class="form-control form-control-sm"></div>
                <div class="col-md-2 mb-1"><input type="number" name="schools[0][classrooms_count]" value="8" placeholder="Classrooms" class="form-control form-control-sm"></div>
                <div class="col-md-2 mb-1"><input type="number" name="schools[0][operating_hours]" value="7" placeholder="Hrs/Day" class="form-control form-control-sm"></div>
              </div>
            </div>

            <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
              <h5 class="font-weight-bold mb-0">Step 3B: Hospitals &amp; Primary Health Centres (PHCs)</h5>
              <button type="button" onclick="addHealthRow()" class="btn btn-danger btn-sm"><i class="fas fa-plus mr-1"></i> Add Health Facility</button>
            </div>
            <div id="health-container" class="mb-3">
              <div class="form-row p-2 bg-light rounded border mb-2">
                <div class="col-md-3 mb-1"><input type="text" name="health_facilities[0][name]" value="Community Primary Health Care Centre" placeholder="Facility Name" class="form-control form-control-sm"></div>
                <div class="col-md-3 mb-1"><input type="text" name="health_facilities[0][facility_type]" value="Primary Health Centre (PHC)" placeholder="Type" class="form-control form-control-sm"></div>
                <div class="col-md-3 mb-1"><input type="text" name="health_facilities[0][refrigeration_requirements]" value="24/7 Solar Vaccine Refrigerator" placeholder="Vaccine Cold Chain" class="form-control form-control-sm"></div>
                <div class="col-md-3 mb-1"><input type="text" name="health_facilities[0][lighting_needs]" value="Maternity & Night Lighting" placeholder="Lighting Needs" class="form-control form-control-sm"></div>
              </div>
            </div>

            <div class="d-flex justify-content-between mt-3">
              <button type="button" onclick="showStep(2)" class="btn btn-secondary"><i class="fas fa-arrow-left mr-1"></i> Back</button>
              <button type="button" onclick="showStep(4)" class="btn btn-primary">Next: Step 4 (Businesses) <i class="fas fa-arrow-right ml-1"></i></button>
            </div>
          </div>

          <!-- STEP 4 -->
          <div id="step-panel-4" class="d-none">
            <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
              <h5 class="font-weight-bold mb-0">Step 4: Commercial &amp; Productive-Use Businesses (SMEs)</h5>
              <button type="button" onclick="addBusinessRow()" class="btn btn-warning btn-sm"><i class="fas fa-plus mr-1"></i> Add Business</button>
            </div>
            <div id="businesses-container">
              <div class="form-row p-2 bg-light rounded border mb-2">
                <div class="col-md-4 mb-1"><input type="text" name="businesses[0][name]" value="Al-Baraka Cassava & Grain Mill" placeholder="Business Name" class="form-control form-control-sm"></div>
                <div class="col-md-3 mb-1"><input type="text" name="businesses[0][category]" value="Agricultural Processing" placeholder="Category" class="form-control form-control-sm"></div>
                <div class="col-md-3 mb-1"><input type="text" name="businesses[0][business_type]" value="Grain Mill" placeholder="Type" class="form-control form-control-sm"></div>
                <div class="col-md-2 mb-1"><input type="number" name="businesses[0][operating_hours]" value="6" placeholder="Hrs/Day" class="form-control form-control-sm"></div>
              </div>
              <div class="form-row p-2 bg-light rounded border mb-2">
                <div class="col-md-4 mb-1"><input type="text" name="businesses[1][name]" value="Unity Metal Welding Workshop" placeholder="Business Name" class="form-control form-control-sm"></div>
                <div class="col-md-3 mb-1"><input type="text" name="businesses[1][category]" value="Artisan / Fabrication" placeholder="Category" class="form-control form-control-sm"></div>
                <div class="col-md-3 mb-1"><input type="text" name="businesses[1][business_type]" value="Welding Workshop" placeholder="Type" class="form-control form-control-sm"></div>
                <div class="col-md-2 mb-1"><input type="number" name="businesses[1][operating_hours]" value="5" placeholder="Hrs/Day" class="form-control form-control-sm"></div>
              </div>
            </div>
            <div class="d-flex justify-content-between mt-3">
              <button type="button" onclick="showStep(3)" class="btn btn-secondary"><i class="fas fa-arrow-left mr-1"></i> Back</button>
              <button type="button" onclick="showStep(5)" class="btn btn-primary">Next: Step 5 (Appliances &amp; Load) <i class="fas fa-arrow-right ml-1"></i></button>
            </div>
          </div>

          <!-- STEP 5 -->
          <div id="step-panel-5" class="d-none">
            <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
              <div>
                <h5 class="font-weight-bold mb-0">Step 5: Electrical Appliances &amp; Equipment Load Matrix</h5>
                <small class="text-muted">Formula: <code>Energy (Wh/day) = Power (W) &times; Quantity &times; Operating Hours</code></small>
              </div>
              <button type="button" onclick="addApplianceRow()" class="btn btn-success btn-sm"><i class="fas fa-plus mr-1"></i> Add Equipment Row</button>
            </div>

            <div id="appliances-container" class="mb-3">
              @php
                $defaultRows = [
                  ['type' => 'household', 'name' => 'Household LED Lighting Bulbs', 'watts' => 10, 'qty' => 580, 'hours' => 6],
                  ['type' => 'household', 'name' => 'Ceiling / Standing Fans', 'watts' => 65, 'qty' => 220, 'hours' => 7],
                  ['type' => 'household', 'name' => 'LED Televisions & Radios', 'watts' => 80, 'qty' => 110, 'hours' => 4],
                  ['type' => 'school', 'name' => 'School Classroom Fans & Computers', 'watts' => 120, 'qty' => 12, 'hours' => 6],
                  ['type' => 'hospital', 'name' => 'PHC Solar Vaccine Refrigerator & Lighting', 'watts' => 150, 'qty' => 4, 'hours' => 24],
                  ['type' => 'business', 'name' => 'Agro-Processing Grain Mill Motor', 'watts' => 3500, 'qty' => 2, 'hours' => 5],
                ];
              @endphp
              @foreach($defaultRows as $idx => $row)
                <div class="form-row p-2 bg-light rounded border mb-2 appliance-row">
                  <div class="col-md-2 mb-1">
                    <select name="appliances[{{ $idx }}][facility_type]" class="form-control form-control-sm">
                      <option value="household" @selected($row['type'] === 'household')>Household</option>
                      <option value="school" @selected($row['type'] === 'school')>School</option>
                      <option value="hospital" @selected($row['type'] === 'hospital')>Hospital/PHC</option>
                      <option value="business" @selected($row['type'] === 'business')>Business/SME</option>
                    </select>
                  </div>
                  <div class="col-md-4 mb-1"><input type="text" name="appliances[{{ $idx }}][custom_appliance_name]" value="{{ $row['name'] }}" required class="form-control form-control-sm"></div>
                  <div class="col-md-2 mb-1"><input type="number" name="appliances[{{ $idx }}][rated_power_watts]" value="{{ $row['watts'] }}" min="1" required oninput="recalcPreview()" class="app-watts form-control form-control-sm" placeholder="Watts"></div>
                  <div class="col-md-2 mb-1"><input type="number" name="appliances[{{ $idx }}][quantity]" value="{{ $row['qty'] }}" min="1" required oninput="recalcPreview()" class="app-qty form-control form-control-sm" placeholder="Qty"></div>
                  <div class="col-md-2 mb-1"><input type="number" step="0.5" name="appliances[{{ $idx }}][operating_hours_per_day]" value="{{ $row['hours'] }}" min="0.5" max="24" required oninput="recalcPreview()" class="app-hours form-control form-control-sm" placeholder="Hrs/Day"></div>
                </div>
              @endforeach
            </div>

            <!-- Live Calculation Preview Banner -->
            <div class="alert alert-primary d-flex flex-wrap justify-content-between align-items-center">
              <div>
                <strong>Live Field Energy Demand Preview</strong><br>
                <small>Includes 10% distribution margin &amp; 0.70 simultaneous peak load factor</small>
              </div>
              <div class="d-flex mt-2 mt-sm-0">
                <div class="mr-4 text-right">
                  <div class="small">Total Daily Demand</div>
                  <div id="preview-kwh" class="h5 font-weight-bold mb-0">0.00 kWh/day</div>
                </div>
                <div class="text-right">
                  <div class="small">Est. Peak Load</div>
                  <div id="preview-kw" class="h5 font-weight-bold mb-0">0.00 kW</div>
                </div>
              </div>
            </div>

            <div class="d-flex flex-wrap justify-content-between align-items-center pt-3 border-top">
              <button type="button" onclick="showStep(4)" class="btn btn-secondary mb-2 mb-sm-0"><i class="fas fa-arrow-left mr-1"></i> Back</button>
              <div class="form-inline">
                <select name="status_enum" class="form-control form-control-sm mr-2 mb-2 mb-sm-0">
                  <option value="completed">Submit as Completed Assessment</option>
                  <option value="draft">Save to Server as Draft</option>
                </select>
                <button type="submit" class="btn btn-success btn-lg">
                  <i class="fas fa-check-circle mr-1"></i> Submit &amp; Generate Recommendation
                </button>
              </div>
            </div>
          </div>

        </div>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
let schoolIdx = 1, healthIdx = 1, bizIdx = 2, appIdx = 6;

function showStep(step) {
  for (let i = 1; i <= 5; i++) {
    const panel = document.getElementById('step-panel-' + i);
    const tab = document.getElementById('tab-step-' + i);
    if (i === step) {
      panel.classList.remove('d-none');
      tab.classList.add('active');
    } else {
      panel.classList.add('d-none');
      tab.classList.remove('active');
    }
  }
  recalcPreview();
}

function addSchoolRow() {
  const c = document.getElementById('schools-container');
  const d = document.createElement('div');
  d.className = 'form-row p-2 bg-light rounded border mb-2';
  d.innerHTML = `
    <div class="col-md-5 mb-1"><input type="text" name="schools[${schoolIdx}][name]" placeholder="School Name" class="form-control form-control-sm"></div>
    <div class="col-md-3 mb-1"><input type="text" name="schools[${schoolIdx}][school_type]" value="Secondary" class="form-control form-control-sm"></div>
    <div class="col-md-2 mb-1"><input type="number" name="schools[${schoolIdx}][classrooms_count]" value="6" class="form-control form-control-sm"></div>
    <div class="col-md-2 mb-1"><input type="number" name="schools[${schoolIdx}][operating_hours]" value="7" class="form-control form-control-sm"></div>
  `;
  c.appendChild(d); schoolIdx++;
}

function addHealthRow() {
  const c = document.getElementById('health-container');
  const d = document.createElement('div');
  d.className = 'form-row p-2 bg-light rounded border mb-2';
  d.innerHTML = `
    <div class="col-md-3 mb-1"><input type="text" name="health_facilities[${healthIdx}][name]" placeholder="Facility Name" class="form-control form-control-sm"></div>
    <div class="col-md-3 mb-1"><input type="text" name="health_facilities[${healthIdx}][facility_type]" value="Clinic" class="form-control form-control-sm"></div>
    <div class="col-md-3 mb-1"><input type="text" name="health_facilities[${healthIdx}][refrigeration_requirements]" value="Vaccine Cold Storage" class="form-control form-control-sm"></div>
    <div class="col-md-3 mb-1"><input type="text" name="health_facilities[${healthIdx}][lighting_needs]" value="24h Ward Lighting" class="form-control form-control-sm"></div>
  `;
  c.appendChild(d); healthIdx++;
}

function addBusinessRow() {
  const c = document.getElementById('businesses-container');
  const d = document.createElement('div');
  d.className = 'form-row p-2 bg-light rounded border mb-2';
  d.innerHTML = `
    <div class="col-md-4 mb-1"><input type="text" name="businesses[${bizIdx}][name]" placeholder="Business Name" class="form-control form-control-sm"></div>
    <div class="col-md-3 mb-1"><input type="text" name="businesses[${bizIdx}][category]" value="Cold Storage / Retail" class="form-control form-control-sm"></div>
    <div class="col-md-3 mb-1"><input type="text" name="businesses[${bizIdx}][business_type]" value="Cold Room / Shop" class="form-control form-control-sm"></div>
    <div class="col-md-2 mb-1"><input type="number" name="businesses[${bizIdx}][operating_hours]" value="10" class="form-control form-control-sm"></div>
  `;
  c.appendChild(d); bizIdx++;
}

function addApplianceRow() {
  const c = document.getElementById('appliances-container');
  const d = document.createElement('div');
  d.className = 'form-row p-2 bg-light rounded border mb-2 appliance-row';
  d.innerHTML = `
    <div class="col-md-2 mb-1"><select name="appliances[${appIdx}][facility_type]" class="form-control form-control-sm"><option value="household">Household</option><option value="school">School</option><option value="hospital">Hospital/PHC</option><option value="business">Business/SME</option></select></div>
    <div class="col-md-4 mb-1"><input type="text" name="appliances[${appIdx}][custom_appliance_name]" placeholder="Equipment Name" required class="form-control form-control-sm"></div>
    <div class="col-md-2 mb-1"><input type="number" name="appliances[${appIdx}][rated_power_watts]" value="100" min="1" required oninput="recalcPreview()" class="app-watts form-control form-control-sm"></div>
    <div class="col-md-2 mb-1"><input type="number" name="appliances[${appIdx}][quantity]" value="5" min="1" required oninput="recalcPreview()" class="app-qty form-control form-control-sm"></div>
    <div class="col-md-2 mb-1"><input type="number" step="0.5" name="appliances[${appIdx}][operating_hours_per_day]" value="6" min="0.5" max="24" required oninput="recalcPreview()" class="app-hours form-control form-control-sm"></div>
  `;
  c.appendChild(d); appIdx++; recalcPreview();
}

function recalcPreview() {
  let totalWh = 0, connectedW = 0;
  document.querySelectorAll('.appliance-row').forEach(row => {
    const w = parseFloat(row.querySelector('.app-watts')?.value || 0);
    const q = parseFloat(row.querySelector('.app-qty')?.value || 0);
    const h = parseFloat(row.querySelector('.app-hours')?.value || 0);
    totalWh += (w * q * h);
    connectedW += (w * q);
  });
  const kwh = (totalWh / 1000.0) * 1.10;
  const kw = (connectedW / 1000.0) * 0.70;
  const kwhEl = document.getElementById('preview-kwh');
  const kwEl = document.getElementById('preview-kw');
  if (kwhEl) kwhEl.textContent = kwh.toFixed(2) + ' kWh/day';
  if (kwEl) kwEl.textContent = kw.toFixed(2) + ' kW';
}

function saveOfflineDraft() {
  const form = document.getElementById('fieldAssessmentForm');
  const formData = new FormData(form);
  const dataObj = {};
  formData.forEach((val, key) => { if (key !== '_token' && key !== 'photo') dataObj[key] = val; });
  localStorage.setItem('kogi_remp_field_draft', JSON.stringify({ savedAt: new Date().toLocaleTimeString(), payload: dataObj }));
  document.getElementById('draft-status-text').textContent = '✓ Saved locally at ' + new Date().toLocaleTimeString();
}

function loadOfflineDraft() {
  const raw = localStorage.getItem('kogi_remp_field_draft');
  if (!raw) { alert('No offline draft found.'); return; }
  const parsed = JSON.parse(raw);
  const form = document.getElementById('fieldAssessmentForm');
  Object.entries(parsed.payload).forEach(([key, val]) => {
    const input = form.querySelector(`[name="${CSS.escape(key)}"]`);
    if (input) input.value = val;
  });
  recalcPreview();
  document.getElementById('draft-status-text').textContent = '✓ Restored draft from ' + parsed.savedAt;
}

document.addEventListener('DOMContentLoaded', recalcPreview);
</script>
@endpush
