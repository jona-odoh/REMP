@extends('layouts.app')

@section('title', 'Track Electricity Challenge Report — Kogi State REMP')

@section('content')
<div class="row justify-content-center">
  <div class="col-lg-9">
    @if(session('submitted_ref'))
      <div class="card card-success">
        <div class="card-body bg-success text-white rounded">
          <span class="badge badge-warning mb-2">Submission Successful</span>
          <h4 class="font-weight-bold mb-1">Thank You! Your Report Has Been Logged</h4>
          <p class="mb-3 small">Save your unique tracking reference number below to monitor LGA verification and field assessment progress:</p>
          <div class="p-3 rounded d-flex justify-content-between align-items-center" style="background: rgba(0,0,0,0.2);">
            <div>
              <div class="small text-uppercase">Tracking Reference Number</div>
              <div class="h3 font-weight-bold mb-0">{{ session('submitted_ref') }}</div>
            </div>
            <span class="badge badge-light text-success p-2">STATUS: SUBMITTED</span>
          </div>
        </div>
      </div>
    @endif

    <div class="card card-primary">
      <div class="card-header">
        <h4>Track Public Electricity Report Status</h4>
      </div>
      <div class="card-body">
        <p class="text-muted small">Enter your unique reference number (e.g., <code>KOGI-2026-1001</code>) to check verification and assessment progress.</p>
        <form method="GET" action="{{ route('public.report.track') }}" class="form-row">
          <div class="col-sm-9 mb-2 mb-sm-0">
            <input type="text" name="ref" value="{{ $ref }}" required placeholder="Enter Reference Number (e.g. KOGI-2026-1001)" class="form-control text-uppercase">
          </div>
          <div class="col-sm-3">
            <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-search mr-1"></i> Check Status</button>
          </div>
        </form>

        @if($sampleRefs->isNotEmpty())
          <div class="mt-3 small text-muted">
            Sample references:
            @foreach($sampleRefs as $sRef)
              <a href="{{ route('public.report.track', ['ref' => $sRef]) }}" class="badge badge-light border text-primary mr-1">{{ $sRef }}</a>
            @endforeach
          </div>
        @endif
      </div>
    </div>

    @if($ref !== '')
      @if($report)
        @php
          $steps = ['submitted', 'under_review', 'verified', 'assessed'];
          $currentIndex = array_search($report->status_enum, $steps);
        @endphp
        <div class="card">
          <div class="card-header">
            <h4>Report Reference: {{ $report->reference_number }}</h4>
            <div class="card-header-action">
              <span class="badge text-uppercase {{ in_array($report->status_enum, ['verified', 'assessed']) ? 'badge-success' : ($report->status_enum === 'rejected' ? 'badge-danger' : 'badge-warning') }}">
                {{ str_replace('_', ' ', $report->status_enum) }}
              </span>
            </div>
          </div>
          <div class="card-body">
            @if($report->status_enum !== 'rejected')
              <div class="row text-center mb-4">
                @foreach(['submitted' => '1. Submitted', 'under_review' => '2. Under Review', 'verified' => '3. Verified', 'assessed' => '4. Field Assessed'] as $key => $label)
                  @php
                    $idx = array_search($key, $steps);
                    $done = $currentIndex !== false && $idx <= $currentIndex;
                  @endphp
                  <div class="col-6 col-sm-3 mb-2">
                    <div class="p-2 rounded border {{ $done ? 'bg-success text-white font-weight-bold' : 'bg-light text-muted' }} small">
                      {{ $label }}
                    </div>
                  </div>
                @endforeach
              </div>
            @endif

            <div class="row mb-3">
              <div class="col-md-6 mb-2">
                <div class="p-3 bg-light rounded border">
                  <div class="small text-muted">Community &amp; LGA</div>
                  <div class="font-weight-bold">{{ $report->community?->name ?? $report->custom_community_name ?? 'Reported Community' }}</div>
                  <div class="small text-muted">{{ $report->ward?->name }} Ward &bull; {{ $report->lga?->name }} LGA</div>
                </div>
              </div>
              <div class="col-md-6 mb-2">
                <div class="p-3 bg-light rounded border">
                  <div class="small text-muted">Reported Situation &amp; Duration</div>
                  <div class="font-weight-bold text-uppercase">{{ str_replace('_', ' ', $report->electricity_situation_enum) }}</div>
                  <div class="small text-muted">Duration: {{ $report->duration_of_problem }} &bull; Est. {{ $report->affected_households_estimate ?? 'N/A' }} HH</div>
                </div>
              </div>
            </div>

            <div class="p-3 bg-light rounded border mb-3 small">
              <strong>Description:</strong> {{ $report->description }}
            </div>

            @if($report->verification_comments)
              <div class="alert alert-success mb-0">
                <strong>Official LGA Verification Feedback:</strong><br>
                {{ $report->verification_comments }}
                @if($report->verified_at)
                  <div class="small mt-1">Reviewed on {{ $report->verified_at->format('d M Y, H:i') }} by {{ $report->verifier?->name ?? 'LGA Officer' }}</div>
                @endif
              </div>
            @endif
          </div>
        </div>
      @else
        <div class="alert alert-danger text-center">
          No report found matching reference number <strong>{{ $ref }}</strong>. Please verify the code and try again.
        </div>
      @endif
    @endif
  </div>
</div>
@endsection
