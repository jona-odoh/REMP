@extends('layouts.app')

@section('title', 'Categories 5 & 6: Energy Demand, Recommendations & Costing — Kogi State REMP')

@section('content')
<div class="card">
  <div class="card-body py-3">
    <div class="row align-items-center">
      <div class="col-lg-7 mb-3 mb-lg-0">
        <span class="badge badge-primary mb-1">Categories 5 &amp; 6 &bull; Energy Demand Engine, Renewable Sizing &amp; Indicative Costing</span>
        <h4 class="mb-0 font-weight-bold text-dark">Energy Needs Assessment, Recommendations &amp; Cost Estimates (NGN)</h4>
      </div>
      <div class="col-lg-5">
        <form method="GET" action="{{ route('energy.index') }}" class="form-row justify-content-lg-end">
          <div class="col-sm-5 mb-2 mb-sm-0">
            <select name="lga_id" onchange="this.form.submit()" class="form-control form-control-sm">
              <option value="">All 3 LGAs</option>
              @foreach($lgas as $lga)
                <option value="{{ $lga->id }}" @selected($lgaId == $lga->id)>{{ $lga->name }} LGA</option>
              @endforeach
            </select>
          </div>
          <div class="col-sm-7">
            <select name="solution" onchange="this.form.submit()" class="form-control form-control-sm">
              <option value="">All Renewable Solutions</option>
              <option value="solar_mini_grid" @selected($solutionFilter === 'solar_mini_grid')>Solar Hybrid Mini-Grid</option>
              <option value="solar_home_system" @selected($solutionFilter === 'solar_home_system')>Solar Home Systems (SHS)</option>
              <option value="institutional_solar" @selected($solutionFilter === 'institutional_solar')>Institutional Solar Systems</option>
              <option value="productive_use_solar" @selected($solutionFilter === 'productive_use_solar')>Productive-Use Solar Systems</option>
              <option value="solar_water_pumping" @selected($solutionFilter === 'solar_water_pumping')>Solar Water Pumping</option>
            </select>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Appliance Library Card -->
<div class="card card-info">
  <div class="card-header">
    <h4>Standard Electrical Appliance &amp; Equipment Library ({{ $appliances->count() }} items)</h4>
  </div>
  <div class="card-body">
    @if(auth()->user()->hasRoleOrEnum(['admin', 'energy_analyst']))
      <form method="POST" action="{{ route('energy.appliances.store') }}" class="form-row mb-3">
        @csrf
        <div class="col-md-4 mb-2"><input type="text" name="name" placeholder="Equipment Name (e.g. Borehole Pump)" required class="form-control form-control-sm"></div>
        <div class="col-md-2 mb-2"><input type="number" name="rated_power_watts" placeholder="Watts (W)" required min="1" class="form-control form-control-sm"></div>
        <div class="col-md-3 mb-2">
          <select name="category" class="form-control form-control-sm">
            <option value="household">Household</option>
            <option value="school">School</option>
            <option value="hospital">Hospital / PHC</option>
            <option value="business">Business / Agro-Processing</option>
          </select>
        </div>
        <div class="col-md-3 mb-2">
          <button type="submit" class="btn btn-primary btn-sm btn-block"><i class="fas fa-plus mr-1"></i> Add to Library</button>
        </div>
      </form>
    @endif

    <div class="d-flex flex-wrap">
      @foreach($appliances as $app)
        <span class="badge badge-light border mr-1 mb-1 py-1 px-2">
          <strong>{{ $app->name }}</strong>: <span class="text-primary">{{ number_format($app->rated_power_watts) }}W</span>
          <small class="text-muted">({{ $app->category }})</small>
        </span>
      @endforeach
    </div>
  </div>
</div>

<!-- Community Energy Analysis & Costing Cards -->
@foreach($demands as $demand)
  @php
    $rec = $demand->recommendation;
    $cost = $rec?->costEstimate;
    $assumptions = $demand->calculation_assumptions_json ?? [];
    $breakdown = $assumptions['facility_breakdown_kwh'] ?? [];
  @endphp
  <div class="card">
    <div class="card-header">
      <h4>
        <a href="{{ route('communities.show', $demand->community_id) }}">{{ $demand->community?->name }}</a>
        <small class="text-muted font-weight-normal ml-1">({{ $demand->community?->ward?->name }} Ward &bull; {{ $demand->community?->ward?->lga?->name }} LGA)</small>
      </h4>
      <div class="card-header-action">
        <span class="badge badge-success mr-1">{{ number_format($demand->total_daily_kwh, 2) }} kWh/day</span>
        <span class="badge badge-warning mr-1">{{ number_format($demand->peak_load_kw, 2) }} kW Peak</span>
        @if($cost)
          <span class="badge badge-dark">&#8358;{{ number_format($cost->total_estimated_cost_ngn, 0) }}</span>
        @endif
      </div>
    </div>
    <div class="card-body">
      <div class="row">
        <div class="col-lg-5 mb-3 mb-lg-0">
          <div class="small text-muted mb-2">
            Coincidence Factor: <strong>{{ $assumptions['simultaneous_usage_factor'] ?? 0.70 }}</strong> &bull;
            Loss Margin: <strong>{{ (($assumptions['distribution_loss_margin'] ?? 0.10) * 100) }}%</strong> &bull;
            Connected: <strong>{{ $assumptions['connected_load_kw'] ?? $demand->peak_load_kw }} kW</strong>
          </div>
          <div class="row small">
            <div class="col-6 mb-2"><div class="p-2 bg-light rounded border d-flex justify-content-between"><span>Households:</span><strong>{{ number_format($breakdown['household'] ?? 0, 1) }} kWh</strong></div></div>
            <div class="col-6 mb-2"><div class="p-2 bg-light rounded border d-flex justify-content-between"><span>Schools:</span><strong>{{ number_format($breakdown['school'] ?? 0, 1) }} kWh</strong></div></div>
            <div class="col-6 mb-2"><div class="p-2 bg-light rounded border d-flex justify-content-between"><span>Hospitals/PHC:</span><strong>{{ number_format($breakdown['hospital'] ?? 0, 1) }} kWh</strong></div></div>
            <div class="col-6 mb-2"><div class="p-2 bg-light rounded border d-flex justify-content-between"><span>Businesses:</span><strong>{{ number_format($breakdown['business'] ?? 0, 1) }} kWh</strong></div></div>
          </div>
          @if(!empty($assumptions['audit_log']))
            <div class="alert alert-info py-2 px-3 small mb-0">
              <strong>Analyst Audit Log ({{ count($assumptions['audit_log']) }}):</strong> {{ collect($assumptions['audit_log'])->last()['note'] ?? '' }}
            </div>
          @endif
        </div>

        <div class="col-lg-7">
          @if($rec)
            <div class="d-flex justify-content-between align-items-center mb-2">
              <strong class="text-primary">{{ $rec->solution_label }} ({{ $rec->estimated_capacity_kw }} kWp)</strong>
              <span class="badge badge-warning">INDICATIVE ESTIMATE</span>
            </div>
            <p class="small text-muted bg-light p-2 rounded border mb-2">{{ $rec->justification }}</p>
            @if($cost)
              <div class="row small text-center">
                <div class="col-4 col-md mb-1"><div class="p-1 bg-light border rounded">Panels<br><strong>&#8358;{{ number_format($cost->solar_panels_cost / 1000000, 2) }}M</strong></div></div>
                <div class="col-4 col-md mb-1"><div class="p-1 bg-light border rounded">Batteries<br><strong>&#8358;{{ number_format($cost->batteries_cost / 1000000, 2) }}M</strong></div></div>
                <div class="col-4 col-md mb-1"><div class="p-1 bg-light border rounded">Inverters<br><strong>&#8358;{{ number_format($cost->inverters_cost / 1000000, 2) }}M</strong></div></div>
                <div class="col-6 col-md mb-1"><div class="p-1 bg-light border rounded">Grid/Meters<br><strong>&#8358;{{ number_format($cost->distribution_cost / 1000000, 2) }}M</strong></div></div>
                <div class="col-6 col-md mb-1"><div class="p-1 bg-light border rounded">Install/Log.<br><strong>&#8358;{{ number_format(($cost->installation_cost + $cost->transportation_cost) / 1000000, 2) }}M</strong></div></div>
              </div>
            @endif
          @endif
        </div>
      </div>

      @if(auth()->user()->hasRoleOrEnum(['admin', 'energy_analyst']))
        <div class="mt-3 pt-2 border-top">
          <a class="btn btn-outline-primary btn-sm" data-toggle="collapse" href="#analystPanel{{ $demand->id }}" role="button">
            <i class="fas fa-cog mr-1"></i> Energy Analyst Review: Adjust Assumptions, Solution &amp; Costing
          </a>
          <div class="collapse mt-3" id="analystPanel{{ $demand->id }}">
            <div class="row bg-light p-3 rounded border">
              <div class="col-lg-6 mb-3 mb-lg-0">
                <form method="POST" action="{{ route('energy.demands.recalculate', $demand) }}">
                  @csrf
                  <h6 class="font-weight-bold small">1. Recalculate Demand &amp; Peak Load Assumptions</h6>
                  <div class="form-row">
                    <div class="form-group col-6 mb-2">
                      <label class="small">Simultaneous Factor</label>
                      <input type="number" step="0.05" name="simultaneous_usage_factor" value="{{ $assumptions['simultaneous_usage_factor'] ?? 0.70 }}" required class="form-control form-control-sm">
                    </div>
                    <div class="form-group col-6 mb-2">
                      <label class="small">Loss Margin</label>
                      <input type="number" step="0.02" name="distribution_loss_margin" value="{{ $assumptions['distribution_loss_margin'] ?? 0.10 }}" required class="form-control form-control-sm">
                    </div>
                  </div>
                  <div class="form-group mb-2">
                    <input type="text" name="override_note" required value="Adjusted coincidence factor based on daytime agro-processing load profile." class="form-control form-control-sm">
                  </div>
                  <button type="submit" class="btn btn-dark btn-sm">Recalculate Demand &amp; Log</button>
                </form>
              </div>
              @if($rec)
                <div class="col-lg-6">
                  <form method="POST" action="{{ route('energy.recommendations.update', $rec) }}">
                    @csrf
                    @method('PUT')
                    <h6 class="font-weight-bold small">2. Amend Recommended Solution &amp; Capacity</h6>
                    <div class="form-row">
                      <div class="form-group col-7 mb-2">
                        <label class="small">Recommended Solution</label>
                        <select name="recommended_solution_enum" class="form-control form-control-sm">
                          <option value="solar_mini_grid" @selected($rec->recommended_solution_enum === 'solar_mini_grid')>Solar Hybrid Mini-Grid</option>
                          <option value="solar_home_system" @selected($rec->recommended_solution_enum === 'solar_home_system')>Solar Home Systems (SHS)</option>
                          <option value="institutional_solar" @selected($rec->recommended_solution_enum === 'institutional_solar')>Institutional Solar System</option>
                          <option value="productive_use_solar" @selected($rec->recommended_solution_enum === 'productive_use_solar')>Productive-Use Solar System</option>
                          <option value="solar_water_pumping" @selected($rec->recommended_solution_enum === 'solar_water_pumping')>Solar Water Pumping</option>
                        </select>
                      </div>
                      <div class="form-group col-5 mb-2">
                        <label class="small">PV Capacity (kWp)</label>
                        <input type="number" step="0.5" name="estimated_capacity_kw" value="{{ $rec->estimated_capacity_kw }}" required class="form-control form-control-sm">
                      </div>
                    </div>
                    <div class="form-group mb-2">
                      <input type="text" name="justification" value="{{ $rec->justification }}" required class="form-control form-control-sm">
                    </div>
                    <input type="hidden" name="recalculate_costs_automatically" value="1">
                    <button type="submit" class="btn btn-primary btn-sm">Update Solution &amp; Re-Estimate NGN Costs</button>
                  </form>
                </div>
              @endif
            </div>
          </div>
        </div>
      @endif
    </div>
  </div>
@endforeach

<div class="mt-3">
  {{ $demands->links() }}
</div>
@endsection
