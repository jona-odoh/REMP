@extends('layouts.app')

@section('title', $community->name . ' — Community Electrification Profile')

@section('content')
@php
  $assessment = $community->assessments->first();
  $demand = $community->latestEnergyDemand;
  $rec = $community->latestRecommendation;
  $cost = $rec?->costEstimate;
  $priority = $community->priorityRanking;
@endphp

<!-- Header Card -->
<div class="card card-primary">
  <div class="card-body">
    <div class="row align-items-center">
      <div class="col-lg-8 mb-3 mb-lg-0">
        <div class="mb-2">
          <span class="badge badge-light border">{{ $community->ward?->lga?->name }} LGA &bull; {{ $community->ward?->name }} Ward</span>
          <span class="badge text-uppercase {{ $community->electricity_status_enum === 'no_electricity' ? 'badge-danger' : ($community->electricity_status_enum === 'reliable' ? 'badge-success' : 'badge-warning') }}">
            {{ str_replace('_', ' ', $community->electricity_status_enum) }}
          </span>
          @if($community->is_verified)
            <span class="badge badge-success"><i class="fas fa-check"></i> Verified Community</span>
          @else
            <span class="badge badge-warning">Unverified Lead</span>
          @endif
          @if($priority)
            <span class="badge text-uppercase {{ $priority->priority_level_enum === 'high' ? 'badge-danger' : ($priority->priority_level_enum === 'medium' ? 'badge-warning' : 'badge-secondary') }}">
              {{ $priority->priority_level_enum }} Priority ({{ number_format($priority->score, 1) }}/100)
            </span>
          @endif
        </div>
        <h3 class="font-weight-bold text-dark mb-1">{{ $community->name }}</h3>
        <p class="text-muted mb-0 small">{{ $community->description ?? 'Registered community under the Kogi State Rural Electrification Decision-Support Platform.' }}</p>
      </div>
      <div class="col-lg-4 text-lg-right">
        <a href="{{ route('communities.export.pdf', $community) }}" class="btn btn-danger btn-sm mb-1">
          <i class="fas fa-file-pdf mr-1"></i> Community Dossier (PDF)
        </a>
        @if(auth()->user()->hasRoleOrEnum(['admin', 'field_officer', 'lga_officer']))
          <a href="{{ route('assessments.create', ['community_id' => $community->id]) }}" class="btn btn-primary btn-sm mb-1">
            <i class="fas fa-clipboard-check mr-1"></i> Conduct Field Assessment
          </a>
        @endif
      </div>
    </div>
  </div>
</div>

<!-- 4 Summary Metric Cards -->
<div class="row">
  <div class="col-md-3 col-sm-6">
    <div class="card card-statistic-1">
      <div class="card-icon bg-primary"><i class="fas fa-home"></i></div>
      <div class="card-wrap">
        <div class="card-header"><h4>Households</h4></div>
        <div class="card-body">{{ number_format($community->estimated_households) }}</div>
      </div>
    </div>
  </div>
  <div class="col-md-3 col-sm-6">
    <div class="card card-statistic-1">
      <div class="card-icon bg-success"><i class="fas fa-bolt"></i></div>
      <div class="card-wrap">
        <div class="card-header"><h4>Daily Demand</h4></div>
        <div class="card-body">{{ $demand ? number_format($demand->total_daily_kwh, 1) . ' kWh' : 'Pending' }}</div>
      </div>
    </div>
  </div>
  <div class="col-md-3 col-sm-6">
    <div class="card card-statistic-1">
      <div class="card-icon bg-info"><i class="fas fa-solar-panel"></i></div>
      <div class="card-wrap">
        <div class="card-header"><h4>PV Capacity</h4></div>
        <div class="card-body">{{ $rec ? number_format($rec->estimated_capacity_kw, 1) . ' kWp' : '—' }}</div>
      </div>
    </div>
  </div>
  <div class="col-md-3 col-sm-6">
    <div class="card card-statistic-1">
      <div class="card-icon bg-warning"><i class="fas fa-coins"></i></div>
      <div class="card-wrap">
        <div class="card-header"><h4>Indicative CAPEX</h4></div>
        <div class="card-body" style="font-size: 16px;">{{ $cost ? '₦' . number_format($cost->total_estimated_cost_ngn / 1000000, 2) . 'M' : '—' }}</div>
      </div>
    </div>
  </div>
</div>

<!-- Recommendation & Indicative Cost Breakdown -->
@if($rec && $cost)
  <div class="row">
    <div class="col-lg-6">
      <div class="card card-info">
        <div class="card-header">
          <h4>Renewable Energy Recommendation &amp; Sizing</h4>
          <div class="card-header-action">
            <span class="badge badge-primary">{{ $rec->solution_label }}</span>
          </div>
        </div>
        <div class="card-body">
          <div class="alert alert-light border">
            <strong>Engineering Justification:</strong> {{ $rec->justification }}
          </div>
          @php $eq = $rec->equipment_requirements_json ?? []; @endphp
          <div class="row">
            <div class="col-6 mb-3">
              <div class="p-3 bg-light rounded border">
                <div class="small text-muted">PV Array Capacity</div>
                <div class="font-weight-bold text-dark">{{ $eq['pv_array_capacity_kwp'] ?? $rec->estimated_capacity_kw }} kWp ({{ $eq['solar_panels_550w_mono_count'] ?? '—' }} &times; 550W)</div>
              </div>
            </div>
            <div class="col-6 mb-3">
              <div class="p-3 bg-light rounded border">
                <div class="small text-muted">LiFePO4 Battery Bank</div>
                <div class="font-weight-bold text-dark">{{ $eq['battery_bank_capacity_kwh'] ?? '—' }} kWh ({{ $eq['lifepo4_5kwh_modules_count'] ?? '—' }} modules)</div>
              </div>
            </div>
            <div class="col-6">
              <div class="p-3 bg-light rounded border">
                <div class="small text-muted">Hybrid Inverter Capacity</div>
                <div class="font-weight-bold text-dark">{{ $eq['inverter_capacity_kva'] ?? '—' }} kVA</div>
              </div>
            </div>
            <div class="col-6">
              <div class="p-3 bg-light rounded border">
                <div class="small text-muted">Connections &amp; LV Grid</div>
                <div class="font-weight-bold text-dark">{{ $eq['smart_meters_or_shs_kits'] ?? '—' }} units ({{ $eq['distribution_pole_km'] ?? 0 }} km LV)</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-6">
      <div class="card card-warning">
        <div class="card-header">
          <h4>Indicative Cost Breakdown (NGN)</h4>
          <div class="card-header-action">
            <span class="badge badge-warning">INDICATIVE ESTIMATE</span>
          </div>
        </div>
        <div class="card-body p-0">
          <table class="table table-striped table-sm mb-0">
            <tbody>
              <tr><td>1. Solar Panels (550W Monocrystalline)</td><td class="text-right font-weight-bold">&#8358;{{ number_format($cost->solar_panels_cost, 2) }}</td></tr>
              <tr><td>2. LiFePO4 Battery Storage Bank</td><td class="text-right font-weight-bold">&#8358;{{ number_format($cost->batteries_cost, 2) }}</td></tr>
              <tr><td>3. Hybrid Inverters</td><td class="text-right font-weight-bold">&#8358;{{ number_format($cost->inverters_cost, 2) }}</td></tr>
              <tr><td>4. MPPT Charge Controllers</td><td class="text-right font-weight-bold">&#8358;{{ number_format($cost->charge_controllers_cost, 2) }}</td></tr>
              <tr><td>5. Mounting Structures &amp; Racking</td><td class="text-right font-weight-bold">&#8358;{{ number_format($cost->mounting_structures_cost, 2) }}</td></tr>
              <tr><td>6. Distribution Network &amp; Metering</td><td class="text-right font-weight-bold">&#8358;{{ number_format($cost->distribution_cost, 2) }}</td></tr>
              <tr><td>7. Protection Equipment &amp; Earthing</td><td class="text-right font-weight-bold">&#8358;{{ number_format($cost->protection_equipment_cost, 2) }}</td></tr>
              <tr><td>8. Installation &amp; Civil Works</td><td class="text-right font-weight-bold">&#8358;{{ number_format($cost->installation_cost, 2) }}</td></tr>
              <tr><td>9. Logistics &amp; Transportation</td><td class="text-right font-weight-bold">&#8358;{{ number_format($cost->transportation_cost, 2) }}</td></tr>
              <tr class="table-primary font-weight-bold">
                <td>TOTAL INDICATIVE COST (NGN)</td>
                <td class="text-right text-primary">&#8358;{{ number_format($cost->total_estimated_cost_ngn, 2) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
@endif

<!-- Field Assessment Summary -->
@if($assessment)
  <div class="card">
    <div class="card-header">
      <h4>Verified Field Assessment Record (#{{ $assessment->id }})</h4>
      <div class="card-header-action">
        <a href="{{ route('assessments.show', $assessment) }}" class="btn btn-primary btn-sm">Full Assessment Sheet</a>
      </div>
    </div>
    <div class="card-body">
      <p class="small text-muted mb-3">
        Assessed on <strong>{{ $assessment->assessment_date?->format('d M Y') }}</strong> by {{ $assessment->officer?->name ?? 'Field Officer' }}
        &bull; Grid Connected: <strong>{{ $assessment->grid_connected_boolean ? 'Yes' : 'No' }}</strong>
        &bull; Avg Supply: <strong>{{ $assessment->average_supply_hours }} hrs/day</strong>
      </p>
      <div class="row">
        <div class="col-md-4 mb-3">
          <div class="p-3 bg-light rounded border h-100">
            <h6 class="text-primary font-weight-bold">Schools Assessed ({{ $assessment->schools->count() }})</h6>
            @forelse($assessment->schools as $sch)
              <div class="border-bottom pb-2 mb-2 small">
                <div class="font-weight-bold">{{ $sch->name }}</div>
                <div class="text-muted">{{ $sch->school_type }} &bull; {{ $sch->classrooms_count }} classrooms</div>
              </div>
            @empty
              <span class="text-muted small">No schools recorded.</span>
            @endforelse
          </div>
        </div>
        <div class="col-md-4 mb-3">
          <div class="p-3 bg-light rounded border h-100">
            <h6 class="text-danger font-weight-bold">Healthcare Facilities ({{ $assessment->healthFacilities->count() }})</h6>
            @forelse($assessment->healthFacilities as $hf)
              <div class="border-bottom pb-2 mb-2 small">
                <div class="font-weight-bold">{{ $hf->name }}</div>
                <div class="text-muted">{{ $hf->facility_type }} &bull; {{ $hf->refrigeration_requirements }}</div>
              </div>
            @empty
              <span class="text-muted small">No health facilities recorded.</span>
            @endforelse
          </div>
        </div>
        <div class="col-md-4 mb-3">
          <div class="p-3 bg-light rounded border h-100">
            <h6 class="text-warning font-weight-bold">Businesses &amp; SMEs ({{ $assessment->businesses->count() }})</h6>
            @forelse($assessment->businesses as $biz)
              <div class="border-bottom pb-2 mb-2 small">
                <div class="font-weight-bold">{{ $biz->name }}</div>
                <div class="text-muted">{{ $biz->category }} ({{ $biz->business_type }}) &bull; {{ $biz->operating_hours }} hrs/day</div>
              </div>
            @empty
              <span class="text-muted small">No businesses recorded.</span>
            @endforelse
          </div>
        </div>
      </div>
    </div>
  </div>
@endif
@endsection
