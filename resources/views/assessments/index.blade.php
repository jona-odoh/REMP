@extends('layouts.app')

@section('title', 'Category 4: Field Data Collection & Assessments — Kogi State REMP')

@section('content')
<div class="card">
  <div class="card-body py-3 d-flex flex-wrap justify-content-between align-items-center">
    <div>
      <span class="badge badge-primary mb-1">Category 4 &bull; Official Field Data Collection &amp; Verification</span>
      <h4 class="mb-0 font-weight-bold text-dark">Community, Household &amp; Facility Field Assessments</h4>
    </div>
    @if(auth()->user()->hasRoleOrEnum(['admin', 'field_officer', 'lga_officer']))
      <a href="{{ route('assessments.create') }}" class="btn btn-primary mt-2 mt-sm-0">
        <i class="fas fa-plus mr-1"></i> New Multi-Step Field Assessment
      </a>
    @endif
  </div>
</div>

@if($verifiedCommunitiesPending->isNotEmpty())
  <div class="card card-warning">
    <div class="card-header">
      <h4>Verified Communities Awaiting Field Assessment ({{ $verifiedCommunitiesPending->count() }} shown)</h4>
    </div>
    <div class="card-body py-2">
      @foreach($verifiedCommunitiesPending as $vc)
        <a href="{{ route('assessments.create', ['community_id' => $vc->id]) }}" class="btn btn-outline-warning btn-sm text-dark mb-1 mr-1">
          {{ $vc->name }} <small class="text-muted">({{ $vc->ward?->lga?->name }})</small> &rarr;
        </a>
      @endforeach
    </div>
  </div>
@endif

<div class="card">
  <div class="card-header">
    <h4>Completed &amp; Draft Field Assessments</h4>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-striped table-hover table-sm mb-0">
        <thead>
          <tr>
            <th>Date</th>
            <th>Community / LGA</th>
            <th>Grid Status</th>
            <th class="text-right">HH Without Power</th>
            <th class="text-center">Facilities (Sch / PHC / Biz)</th>
            <th class="text-right">Calc. Demand</th>
            <th>Field Officer</th>
            <th class="text-right">Action</th>
          </tr>
        </thead>
        <tbody>
          @foreach($assessments as $a)
            <tr>
              <td class="small">{{ $a->assessment_date?->format('Y-m-d') }}</td>
              <td>
                <a href="{{ route('communities.show', $a->community_id) }}" class="font-weight-bold">{{ $a->community?->name }}</a>
                <div class="small text-muted">{{ $a->community?->ward?->name }} &bull; {{ $a->community?->ward?->lga?->name }} LGA</div>
              </td>
              <td>
                @if($a->grid_connected_boolean)
                  <span class="badge badge-warning">Grid ({{ $a->average_supply_hours }}h/d)</span>
                @else
                  <span class="badge badge-danger">Off-Grid (0h)</span>
                @endif
              </td>
              <td class="text-right font-weight-bold">{{ number_format($a->households_without_electricity) }}</td>
              <td class="text-center">
                <span class="badge badge-light border">
                  {{ $a->schools->count() }} Sch &bull; {{ $a->healthFacilities->count() }} PHC &bull; {{ $a->businesses->count() }} Biz
                </span>
              </td>
              <td class="text-right font-weight-bold text-success">
                {{ $a->energyDemand ? number_format($a->energyDemand->total_daily_kwh, 1) . ' kWh/d' : '—' }}
              </td>
              <td class="small">{{ $a->officer?->name ?? 'Field Officer' }}</td>
              <td class="text-right">
                <a href="{{ route('assessments.show', $a) }}" class="btn btn-outline-primary btn-sm">View Sheet</a>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
  <div class="card-footer">
    {{ $assessments->links() }}
  </div>
</div>
@endsection
