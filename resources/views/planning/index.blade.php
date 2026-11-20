@extends('layouts.app')

@section('title', 'Category 7: Planning, Prioritisation & Reporting — Kogi State REMP')

@section('content')
<div class="card">
  <div class="card-body py-3">
    <div class="row align-items-center">
      <div class="col-lg-7 mb-3 mb-lg-0">
        <span class="badge badge-primary mb-1">Category 7 &bull; Multi-Criteria Prioritisation &amp; Executive Export Engine</span>
        <h4 class="mb-1 font-weight-bold text-dark">Rural Electrification Investment Prioritisation &amp; Reports</h4>
        <small class="text-muted">Weights: Households (30%) &bull; Schools/Clinics (25%) &bull; Economic Impact (20%) &bull; Demand (15%) &bull; Feasibility (10%)</small>
      </div>
      <div class="col-lg-5 text-lg-right">
        @if(auth()->user()->hasRoleOrEnum(['admin', 'energy_analyst']))
          <form method="POST" action="{{ route('planning.recalculate') }}" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-dark btn-sm mb-1"><i class="fas fa-sync mr-1"></i> Re-Run Ranking</button>
          </form>
        @endif
        <a href="{{ route('planning.export.pdf', request()->query()) }}" class="btn btn-danger btn-sm mb-1">
          <i class="fas fa-file-pdf mr-1"></i> Export PDF
        </a>
        <a href="{{ route('planning.export.excel', request()->query()) }}" class="btn btn-success btn-sm mb-1">
          <i class="fas fa-file-excel mr-1"></i> Export Excel
        </a>
      </div>
    </div>

    <hr class="my-3">
    <form method="GET" action="{{ route('planning.index') }}" class="form-row">
      <div class="col-md-4 mb-2 mb-md-0">
        <select name="lga_id" onchange="this.form.submit()" class="form-control form-control-sm">
          <option value="">All 3 Selected LGAs (Statewide View)</option>
          @foreach($lgas as $lga)
            <option value="{{ $lga->id }}" @selected($lgaId == $lga->id)>{{ $lga->name }} LGA Report</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-4 mb-2 mb-md-0">
        <select name="priority" onchange="this.form.submit()" class="form-control form-control-sm">
          <option value="">All Priority Classifications</option>
          <option value="high" @selected($priorityFilter === 'high')>High Priority Only (Score &ge; 80)</option>
          <option value="medium" @selected($priorityFilter === 'medium')>Medium Priority (Score 50–79)</option>
          <option value="low" @selected($priorityFilter === 'low')>Low Priority (Score &lt; 50)</option>
        </select>
      </div>
      <div class="col-md-4 d-flex">
        <select name="solution" onchange="this.form.submit()" class="form-control form-control-sm">
          <option value="">All Intervention Types</option>
          <option value="solar_mini_grid" @selected($solutionFilter === 'solar_mini_grid')>Solar Hybrid Mini-Grid</option>
          <option value="solar_home_system" @selected($solutionFilter === 'solar_home_system')>Solar Home Systems (SHS)</option>
          <option value="institutional_solar" @selected($solutionFilter === 'institutional_solar')>Institutional Solar Systems</option>
          <option value="productive_use_solar" @selected($solutionFilter === 'productive_use_solar')>Productive-Use Solar Systems</option>
          <option value="solar_water_pumping" @selected($solutionFilter === 'solar_water_pumping')>Solar Water Pumping</option>
        </select>
        @if($lgaId || $priorityFilter || $solutionFilter)
          <a href="{{ route('planning.index') }}" class="btn btn-secondary btn-sm ml-1"><i class="fas fa-redo"></i></a>
        @endif
      </div>
    </form>
  </div>
</div>

<!-- Summary Strip -->
<div class="row">
  <div class="col-6 col-md-2">
    <div class="card text-center py-3">
      <div class="small text-muted">Communities</div>
      <div class="h4 font-weight-bold mb-0">{{ $summary['total_communities'] }}</div>
    </div>
  </div>
  <div class="col-6 col-md-2">
    <div class="card text-center py-3 border-top border-danger">
      <div class="small text-danger font-weight-bold">High Priority</div>
      <div class="h4 font-weight-bold text-danger mb-0">{{ $summary['high_priority'] }}</div>
    </div>
  </div>
  <div class="col-6 col-md-2">
    <div class="card text-center py-3 border-top border-warning">
      <div class="small text-warning font-weight-bold">Medium Priority</div>
      <div class="h4 font-weight-bold text-warning mb-0">{{ $summary['medium_priority'] }}</div>
    </div>
  </div>
  <div class="col-6 col-md-2">
    <div class="card text-center py-3">
      <div class="small text-muted">Underserved HH</div>
      <div class="h4 font-weight-bold mb-0">{{ number_format($summary['total_households_affected']) }}</div>
    </div>
  </div>
  <div class="col-6 col-md-2">
    <div class="card text-center py-3">
      <div class="small text-muted">Daily Demand</div>
      <div class="h5 font-weight-bold text-success mb-0">{{ number_format($summary['total_daily_kwh'], 1) }} <small>kWh</small></div>
    </div>
  </div>
  <div class="col-6 col-md-2">
    <div class="card text-center py-3">
      <div class="small text-muted">Indicative Budget</div>
      <div class="h5 font-weight-bold text-primary mb-0">&#8358;{{ number_format($summary['total_cost_ngn'] / 1000000, 1) }}M</div>
    </div>
  </div>
</div>

<!-- Ranked Communities Table -->
<div class="card">
  <div class="card-header">
    <h4>Prioritised Community Investment Matrix</h4>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-striped table-hover table-sm mb-0">
        <thead>
          <tr>
            <th class="text-center">Rank</th>
            <th>Community &amp; Ward</th>
            <th>LGA</th>
            <th>Status</th>
            <th class="text-right">HH Affected</th>
            <th class="text-center">S / H / B</th>
            <th class="text-right">kWh/day</th>
            <th>Recommended Intervention</th>
            <th class="text-right">Indicative Cost (&#8358;)</th>
            <th class="text-center">Score / Tier</th>
            <th class="text-right">PDF</th>
          </tr>
        </thead>
        <tbody>
          @foreach($communities as $index => $c)
            @php
              $assessment = $c->latestAssessment;
              $demand = $c->latestEnergyDemand;
              $rec = $c->latestRecommendation;
              $cost = $rec?->costEstimate;
              $p = $c->priorityRanking;
              $level = $p?->priority_level_enum ?? 'low';
            @endphp
            <tr>
              <td class="text-center font-weight-bold text-muted">#{{ $index + 1 }}</td>
              <td>
                <a href="{{ route('communities.show', $c) }}" class="font-weight-bold">{{ $c->name }}</a>
                <div class="small text-muted">{{ $c->ward?->name }} Ward</div>
              </td>
              <td>{{ $c->ward?->lga?->name }}</td>
              <td>
                <span class="badge text-uppercase {{ $c->electricity_status_enum === 'no_electricity' ? 'badge-danger' : 'badge-warning' }}">
                  {{ str_replace('_', ' ', $c->electricity_status_enum) }}
                </span>
              </td>
              <td class="text-right font-weight-bold">{{ number_format($assessment ? $assessment->households_without_electricity : ($c->estimated_households ?? 0)) }}</td>
              <td class="text-center small">
                {{ $assessment ? $assessment->schools->count() : 0 }} /
                {{ $assessment ? $assessment->healthFacilities->count() : 0 }} /
                {{ $assessment ? $assessment->businesses->count() : 0 }}
              </td>
              <td class="text-right font-weight-bold text-success">{{ $demand ? number_format($demand->total_daily_kwh, 1) : '—' }}</td>
              <td class="small">
                <div class="font-weight-bold">{{ $rec?->solution_label ?? 'Pending' }}</div>
                @if($rec)<small class="text-muted">{{ number_format($rec->estimated_capacity_kw, 1) }} kWp</small>@endif
              </td>
              <td class="text-right font-weight-bold">{{ $cost ? '₦' . number_format($cost->total_estimated_cost_ngn, 0) : '—' }}</td>
              <td class="text-center">
                <span class="badge text-uppercase {{ $level === 'high' ? 'badge-danger' : ($level === 'medium' ? 'badge-warning' : 'badge-secondary') }}">
                  {{ number_format($p?->score ?? 0, 1) }} ({{ $level }})
                </span>
              </td>
              <td class="text-right">
                <a href="{{ route('communities.export.pdf', $c) }}" class="btn btn-outline-danger btn-sm"><i class="fas fa-file-pdf"></i></a>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
