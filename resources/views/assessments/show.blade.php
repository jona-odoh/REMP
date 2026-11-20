@extends('layouts.app')

@section('title', 'Field Assessment #' . $assessment->id . ' — ' . $assessment->community?->name)

@section('content')
<div class="card card-primary">
  <div class="card-body d-flex flex-wrap justify-content-between align-items-center">
    <div>
      <span class="badge badge-success mb-1">Field Assessment #{{ $assessment->id }} &bull; {{ strtoupper($assessment->status_enum) }}</span>
      <h4 class="font-weight-bold text-dark mb-1">{{ $assessment->community?->name }} ({{ $assessment->community?->ward?->lga?->name }} LGA)</h4>
      <small class="text-muted">
        Assessed on {{ $assessment->assessment_date?->format('d M Y') }} by {{ $assessment->officer?->name ?? 'Field Officer' }}
        &bull; GPS: {{ $assessment->gps_coordinates ?? ($assessment->community?->latitude . ', ' . $assessment->community?->longitude) }}
      </small>
    </div>
    <a href="{{ route('communities.show', $assessment->community_id) }}" class="btn btn-primary btn-sm mt-2 mt-sm-0">
      View Community Profile &amp; Costing <i class="fas fa-arrow-right ml-1"></i>
    </a>
  </div>
</div>

<div class="row">
  <div class="col-md-3 col-sm-6">
    <div class="card card-statistic-1">
      <div class="card-icon bg-info"><i class="fas fa-plug"></i></div>
      <div class="card-wrap">
        <div class="card-header"><h4>National Grid</h4></div>
        <div class="card-body" style="font-size:16px;">{{ $assessment->grid_connected_boolean ? 'Connected (' . $assessment->average_supply_hours . 'h)' : 'Off-Grid' }}</div>
      </div>
    </div>
  </div>
  <div class="col-md-3 col-sm-6">
    <div class="card card-statistic-1">
      <div class="card-icon bg-danger"><i class="fas fa-home"></i></div>
      <div class="card-wrap">
        <div class="card-header"><h4>HH Without Power</h4></div>
        <div class="card-body">{{ number_format($assessment->households_without_electricity) }}</div>
      </div>
    </div>
  </div>
  <div class="col-md-3 col-sm-6">
    <div class="card card-statistic-1">
      <div class="card-icon bg-warning"><i class="fas fa-gas-pump"></i></div>
      <div class="card-wrap">
        <div class="card-header"><h4>Alt. Sources</h4></div>
        <div class="card-body" style="font-size:13px;">{{ Str::limit($assessment->alternative_energy_sources ?? 'Petrol Gen', 22) }}</div>
      </div>
    </div>
  </div>
  <div class="col-md-3 col-sm-6">
    <div class="card card-statistic-1">
      <div class="card-icon bg-success"><i class="fas fa-bolt"></i></div>
      <div class="card-wrap">
        <div class="card-header"><h4>Daily Demand</h4></div>
        <div class="card-body" style="font-size:16px;">{{ $assessment->energyDemand ? number_format($assessment->energyDemand->total_daily_kwh, 1) . ' kWh' : '—' }}</div>
      </div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-header">
    <h4>Captured Electrical Equipment &amp; Appliances Inventory</h4>
    <div class="card-header-action">
      <small class="text-muted">Energy (Wh/day) = Power (W) &times; Qty &times; Operating Hours</small>
    </div>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-striped table-sm mb-0">
        <thead>
          <tr>
            <th>Facility Category</th>
            <th>Appliance / Equipment</th>
            <th class="text-right">Rated Power (W)</th>
            <th class="text-right">Qty</th>
            <th class="text-right">Hrs/Day</th>
            <th class="text-right">Wh/day</th>
            <th class="text-right">kWh/day</th>
          </tr>
        </thead>
        <tbody>
          @foreach($assessment->assessmentAppliances as $appItem)
            <tr>
              <td><span class="badge badge-light border text-uppercase">{{ $appItem->facility_type }}</span></td>
              <td class="font-weight-bold">{{ $appItem->display_name }}</td>
              <td class="text-right">{{ number_format($appItem->rated_power_watts) }} W</td>
              <td class="text-right">{{ number_format($appItem->quantity) }}</td>
              <td class="text-right">{{ number_format($appItem->operating_hours_per_day, 1) }} h</td>
              <td class="text-right">{{ number_format($appItem->calculated_daily_energy_wh, 0) }} Wh</td>
              <td class="text-right font-weight-bold text-success">{{ number_format($appItem->calculated_daily_energy_wh / 1000, 2) }} kWh</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
