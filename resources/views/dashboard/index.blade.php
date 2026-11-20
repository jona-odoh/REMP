@extends('layouts.app')

@section('title', 'Category 1: Executive Dashboard & Overview — Kogi State REMP')

@section('content')
<!-- Header & LGA / Ward / Community Filter Card -->
<div class="card">
  <div class="card-body py-3">
    <div class="row align-items-center">
      <div class="col-lg-6 mb-3 mb-lg-0">
        {{-- <span class="badge badge-primary mb-1">Category 1 &bull; Executive Decision-Support Overview</span> --}}
        <h4 class="mb-0 font-weight-bold text-dark">Kogi State Rural Electrification Dashboard</h4>
        <small class="text-muted">Initial Scope: Okene, Idah &amp; Kabba/Bunu LGAs</small>
      </div>
      <div class="col-lg-6">
        <form method="GET" action="{{ route('dashboard') }}" class="form-row justify-content-lg-end">
          <div class="col-sm-4 mb-2 mb-sm-0">
            <select name="lga_id" onchange="this.form.submit()" class="form-control form-control-sm">
              <option value="">All 3 Selected LGAs</option>
              @foreach($lgas as $lga)
                <option value="{{ $lga->id }}" @selected($lgaId == $lga->id)>{{ $lga->name }} LGA</option>
              @endforeach
            </select>
          </div>
          <div class="col-sm-4 mb-2 mb-sm-0">
            <select name="ward_id" onchange="this.form.submit()" class="form-control form-control-sm">
              <option value="">All Wards</option>
              @foreach($wards as $ward)
                <option value="{{ $ward->id }}" @selected($wardId == $ward->id)>{{ $ward->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-sm-4 d-flex">
            <select name="community_id" onchange="this.form.submit()" class="form-control form-control-sm">
              <option value="">All Communities</option>
              @foreach($allSelectableCommunities as $comm)
                <option value="{{ $comm->id }}" @selected($communityId == $comm->id)>{{ $comm->name }}</option>
              @endforeach
            </select>
            @if($lgaId || $wardId || $communityId)
              <a href="{{ route('dashboard') }}" class="btn btn-secondary btn-sm ml-1" title="Reset Filters">
                <i class="fas fa-redo"></i>
              </a>
            @endif
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Otika Top 4 KPI Statistic Cards -->
<div class="row">
  <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-xs-12">
    <div class="card">
      <div class="card-statistic-4">
        <div class="align-items-center justify-content-between">
          <div class="row">
            <div class="col-lg-7 col-md-7 col-sm-7 col-xs-7 pr-0 pt-3">
              <div class="card-content">
                <h5 class="font-14">Communities ({{ $totalLgas }} LGAs)</h5>
                <h2 class="mb-2 font-20">{{ number_format($totalCommunities) }}</h2>
                <p class="mb-0 small"><span class="col-green font-weight-bold">{{ $verifiedCommunitiesCount }} Verified</span> &bull; {{ $assessedCommunitiesCount }} Assessed</p>
              </div>
            </div>
            <div class="col-lg-5 col-md-5 col-sm-5 col-xs-5 pl-0">
              <div class="banner-img">
                <img src="{{ asset('assets/img/banner/1.png') }}" alt="">
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-xs-12">
    <div class="card">
      <div class="card-statistic-4">
        <div class="align-items-center justify-content-between">
          <div class="row">
            <div class="col-lg-7 col-md-7 col-sm-7 col-xs-7 pr-0 pt-3">
              <div class="card-content">
                <h5 class="font-14">Affected Households</h5>
                <h2 class="mb-2 font-20">{{ number_format($affectedHouseholdsCount) }}</h2>
                <p class="mb-0 small"><span class="col-orange font-weight-bold">{{ $reportedChallengesCount }}</span> Public Reports</p>
              </div>
            </div>
            <div class="col-lg-5 col-md-5 col-sm-5 col-xs-5 pl-0">
              <div class="banner-img">
                <img src="{{ asset('assets/img/banner/2.png') }}" alt="">
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-xs-12">
    <div class="card">
      <div class="card-statistic-4">
        <div class="align-items-center justify-content-between">
          <div class="row">
            <div class="col-lg-7 col-md-7 col-sm-7 col-xs-7 pr-0 pt-3">
              <div class="card-content">
                <h5 class="font-14">Est. Daily Demand</h5>
                <h2 class="mb-2 font-20">{{ number_format($totalDailyDemandKwh, 1) }} <small>kWh</small></h2>
                <p class="mb-0 small">Peak Load: <span class="col-green font-weight-bold">{{ number_format($totalPeakLoadKw, 1) }} kW</span></p>
              </div>
            </div>
            <div class="col-lg-5 col-md-5 col-sm-5 col-xs-5 pl-0">
              <div class="banner-img">
                <img src="{{ asset('assets/img/banner/3.png') }}" alt="">
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-xs-12">
    <div class="card">
      <div class="card-statistic-4">
        <div class="align-items-center justify-content-between">
          <div class="row">
            <div class="col-lg-7 col-md-7 col-sm-7 col-xs-7 pr-0 pt-3">
              <div class="card-content">
                <h5 class="font-14">Indicative CAPEX</h5>
                <h2 class="mb-2 font-20">&#8358;{{ number_format($totalEstimatedCostNgn / 1000000, 1) }}M</h2>
                <p class="mb-0 small text-muted">&#8358;{{ number_format($totalEstimatedCostNgn, 0) }}</p>
              </div>
            </div>
            <div class="col-lg-5 col-md-5 col-sm-5 col-xs-5 pl-0">
              <div class="banner-img">
                <img src="{{ asset('assets/img/banner/4.png') }}" alt="">
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Secondary Row: Facilities Assessed & Priority Classification Tiers -->
<div class="row">
  <div class="col-lg-6">
    <div class="card">
      <div class="card-header">
        <h4>Public &amp; Economic Facilities Assessed</h4>
      </div>
      <div class="card-body py-3">
        <div class="row text-center">
          <div class="col-4 border-right">
            <div class="h4 font-weight-bold text-primary mb-0">{{ $schoolsAssessedCount }}</div>
            <div class="text-muted small">Schools</div>
          </div>
          <div class="col-4 border-right">
            <div class="h4 font-weight-bold text-danger mb-0">{{ $hospitalsAssessedCount }}</div>
            <div class="text-muted small">Hospitals / PHCs</div>
          </div>
          <div class="col-4">
            <div class="h4 font-weight-bold text-warning mb-0">{{ $businessesAssessedCount }}</div>
            <div class="text-muted small">Businesses / SMEs</div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-6">
    <div class="card">
      <div class="card-header">
        <h4>Community Prioritisation Tiers</h4>
        <div class="card-header-action">
          <a href="{{ route('planning.index') }}" class="btn btn-primary btn-sm">View All Rankings</a>
        </div>
      </div>
      <div class="card-body py-3">
        <div class="row text-center">
          <div class="col-4">
            <a href="{{ route('planning.index', ['priority' => 'high']) }}" class="d-block p-2 rounded bg-light text-decoration-none">
              <div class="h4 font-weight-bold text-danger mb-0">{{ $priorityCounts['high'] }}</div>
              <span class="badge badge-danger">High Priority</span>
            </a>
          </div>
          <div class="col-4">
            <a href="{{ route('planning.index', ['priority' => 'medium']) }}" class="d-block p-2 rounded bg-light text-decoration-none">
              <div class="h4 font-weight-bold text-warning mb-0">{{ $priorityCounts['medium'] }}</div>
              <span class="badge badge-warning">Medium</span>
            </a>
          </div>
          <div class="col-4">
            <a href="{{ route('planning.index', ['priority' => 'low']) }}" class="d-block p-2 rounded bg-light text-decoration-none">
              <div class="h4 font-weight-bold text-secondary mb-0">{{ $priorityCounts['low'] }}</div>
              <span class="badge badge-secondary">Low</span>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Charts Row -->
<div class="row">
  <div class="col-lg-5">
    <div class="card">
      <div class="card-header">
        <h4>Communities by Electricity Status</h4>
      </div>
      <div class="card-body">
        <div style="height: 250px;">
          <canvas id="statusPieChart"></canvas>
        </div>
        <div class="row mt-3 small">
          <div class="col-6 mb-1"><span class="badge badge-danger mr-1">&nbsp;</span> No Electricity: <strong>{{ $statusCounts['no_electricity'] }}</strong></div>
          <div class="col-6 mb-1"><span class="badge badge-warning mr-1">&nbsp;</span> Unreliable: <strong>{{ $statusCounts['unreliable'] }}</strong></div>
          <div class="col-6"><span class="badge badge-info mr-1">&nbsp;</span> Inadequate: <strong>{{ $statusCounts['inadequate'] }}</strong></div>
          <div class="col-6"><span class="badge badge-success mr-1">&nbsp;</span> Reliable: <strong>{{ $statusCounts['reliable'] }}</strong></div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-7">
    <div class="card">
      <div class="card-header">
        <h4>Estimated Daily Demand (kWh/day) &amp; Peak Load per LGA</h4>
        <div class="card-header-action">
          <a href="{{ route('geography.index') }}" class="btn btn-info btn-sm"><i class="fas fa-map-marked-alt mr-1"></i> GIS Map</a>
        </div>
      </div>
      <div class="card-body">
        <div style="height: 250px;">
          <canvas id="lgaDemandBarChart"></canvas>
        </div>
        <div class="row mt-3">
          @foreach($demandByLga as $lgaStat)
            <div class="col-md-4 mb-2">
              <div class="p-2 bg-light rounded border">
                <div class="font-weight-bold small">{{ $lgaStat['lga_name'] }} LGA</div>
                <div class="text-success font-weight-bold">{{ number_format($lgaStat['daily_kwh'], 1) }} kWh/day</div>
                <small class="text-muted">Peak: {{ number_format($lgaStat['peak_kw'], 1) }} kW &bull; &#8358;{{ number_format($lgaStat['cost_ngn'] / 1000000, 1) }}M</small>
              </div>
            </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Interventions & Priority Communities Tables -->
<div class="row">
  <div class="col-lg-5">
    <div class="card">
      <div class="card-header">
        <h4>Proposed Renewable Interventions</h4>
        <div class="card-header-action">
          <a href="{{ route('energy.index') }}" class="btn btn-outline-primary btn-sm">Details</a>
        </div>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-striped table-sm mb-0">
            <thead>
              <tr>
                <th>Intervention</th>
                <th class="text-center">Sites</th>
                <th class="text-right">kWp</th>
                <th class="text-right">Indicative Cost</th>
              </tr>
            </thead>
            <tbody>
              @forelse($interventionsBreakdown as $item)
                <tr>
                  <td class="font-weight-bold">{{ $item['label'] }}</td>
                  <td class="text-center">{{ $item['count'] }}</td>
                  <td class="text-right">{{ number_format($item['total_capacity_kw'], 1) }}</td>
                  <td class="text-right font-weight-bold text-success">&#8358;{{ number_format($item['total_cost_ngn'], 0) }}</td>
                </tr>
              @empty
                <tr><td colspan="4" class="text-center text-muted py-3">No interventions calculated yet.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
      <div class="card-footer bg-whitesmoke small text-muted">
        <i class="fas fa-info-circle text-warning mr-1"></i> All cost estimates are <strong>Indicative Estimates</strong> in NGN, not final supplier quotations.
      </div>
    </div>
  </div>

  <div class="col-lg-7">
    <div class="card">
      <div class="card-header">
        <h4>Top Priority Communities for Investment</h4>
        <div class="card-header-action">
          <a href="{{ route('planning.export.pdf') }}" class="btn btn-danger btn-sm"><i class="fas fa-file-pdf mr-1"></i> PDF</a>
          <a href="{{ route('planning.export.excel') }}" class="btn btn-success btn-sm"><i class="fas fa-file-excel mr-1"></i> Excel</a>
        </div>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-striped table-hover table-sm mb-0">
            <thead>
              <tr>
                <th>Community</th>
                <th>LGA</th>
                <th class="text-right">Demand</th>
                <th>Solution</th>
                <th class="text-right">Indicative Cost</th>
                <th class="text-center">Priority</th>
              </tr>
            </thead>
            <tbody>
              @foreach($priorityCommunities as $c)
                @php
                  $pLevel = $c->priorityRanking?->priority_level_enum ?? 'low';
                  $pScore = $c->priorityRanking?->score ?? 0;
                  $badgeClass = $pLevel === 'high' ? 'badge-danger' : ($pLevel === 'medium' ? 'badge-warning' : 'badge-secondary');
                @endphp
                <tr>
                  <td>
                    <a href="{{ route('communities.show', $c) }}" class="font-weight-bold">{{ $c->name }}</a>
                    <div class="small text-muted">{{ $c->ward?->name }}</div>
                  </td>
                  <td>{{ $c->ward?->lga?->name }}</td>
                  <td class="text-right">{{ $c->latestEnergyDemand ? number_format($c->latestEnergyDemand->total_daily_kwh, 1) . ' kWh' : '—' }}</td>
                  <td class="small">{{ $c->latestRecommendation?->solution_label ?? 'Pending' }}</td>
                  <td class="text-right font-weight-bold">
                    {{ $c->latestRecommendation?->costEstimate ? '₦' . number_format($c->latestRecommendation->costEstimate->total_estimated_cost_ngn, 0) : '—' }}
                  </td>
                  <td class="text-center">
                    <span class="badge {{ $badgeClass }} text-uppercase">{{ $pLevel }} ({{ number_format($pScore, 0) }})</span>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Livewire Interactive Simulator -->
@livewire('quick-energy-simulator')
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  const pieCtx = document.getElementById('statusPieChart');
  if (pieCtx) {
    new Chart(pieCtx, {
      type: 'doughnut',
      data: {
        labels: ['No Electricity', 'Unreliable Supply', 'Inadequate Supply', 'Relatively Reliable'],
        datasets: [{
          data: [
            {{ $statusCounts['no_electricity'] }},
            {{ $statusCounts['unreliable'] }},
            {{ $statusCounts['inadequate'] }},
            {{ $statusCounts['reliable'] }}
          ],
          backgroundColor: ['#fc544b', '#ffa426', '#3abaf4', '#47c363'],
          borderWidth: 2,
          borderColor: '#ffffff'
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom' } }
      }
    });
  }

  const barCtx = document.getElementById('lgaDemandBarChart');
  if (barCtx) {
    new Chart(barCtx, {
      type: 'bar',
      data: {
        labels: @json($demandByLga->pluck('lga_name')),
        datasets: [
          {
            label: 'Daily Energy Demand (kWh/day)',
            data: @json($demandByLga->pluck('daily_kwh')),
            backgroundColor: '#6777ef',
            borderRadius: 4
          },
          {
            label: 'Estimated Peak Load (kW)',
            data: @json($demandByLga->pluck('peak_kw')),
            backgroundColor: '#ffa426',
            borderRadius: 4
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: { y: { beginAtZero: true } }
      }
    });
  }
});
</script>
@endpush
