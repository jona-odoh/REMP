@extends('layouts.app')

@section('title', 'Category 4F: Public Report Verification Queue — Kogi State REMP')

@section('content')
<div class="card">
  <div class="card-body py-3">
    <div class="row align-items-center">
      <div class="col-lg-7 mb-3 mb-lg-0">
        <span class="badge badge-primary mb-1">Category 4F &bull; LGA Officer &amp; Admin Verification Workflow</span>
        <h4 class="mb-0 font-weight-bold text-dark">Public Electricity Reports — Verification Queue</h4>
        <small class="text-muted">Only verified submissions are confirmed for official field assessment and investment planning.</small>
      </div>
      <div class="col-lg-5">
        <form method="GET" action="{{ route('reports.verification') }}" class="form-row justify-content-lg-end">
          <div class="col-sm-6 mb-2 mb-sm-0">
            <select name="lga_id" onchange="this.form.submit()" class="form-control form-control-sm">
              <option value="">All LGAs</option>
              @foreach($lgas as $lga)
                <option value="{{ $lga->id }}" @selected($lgaFilter == $lga->id)>{{ $lga->name }} LGA</option>
              @endforeach
            </select>
          </div>
          <div class="col-sm-6">
            <select name="status" onchange="this.form.submit()" class="form-control form-control-sm">
              <option value="">All Report Statuses</option>
              <option value="submitted" @selected($statusFilter === 'submitted')>Submitted</option>
              <option value="under_review" @selected($statusFilter === 'under_review')>Under Review</option>
              <option value="verified" @selected($statusFilter === 'verified')>Verified</option>
              <option value="assessed" @selected($statusFilter === 'assessed')>Field Assessed</option>
              <option value="rejected" @selected($statusFilter === 'rejected')>Rejected</option>
            </select>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

@forelse($reports as $rep)
  <div class="card">
    <div class="card-body">
      <div class="row">
        <div class="col-lg-7 mb-3 mb-lg-0">
          <div class="mb-2">
            <span class="badge badge-dark mr-1">{{ $rep->reference_number }}</span>
            <span class="badge text-uppercase {{ in_array($rep->status_enum, ['verified', 'assessed']) ? 'badge-success' : ($rep->status_enum === 'rejected' ? 'badge-danger' : 'badge-warning') }}">
              {{ str_replace('_', ' ', $rep->status_enum) }}
            </span>
            @if($rep->is_potential_duplicate)
              <span class="badge badge-danger ml-1"><i class="fas fa-exclamation-triangle"></i> Flagged Potential Duplicate (24h)</span>
            @endif
            <small class="text-muted ml-2">{{ $rep->created_at?->diffForHumans() }} by {{ $rep->reporter_name ?? 'Resident' }}</small>
          </div>

          <h5 class="font-weight-bold text-dark mb-1">
            {{ $rep->community?->name ?? $rep->custom_community_name ?? 'New Community Lead' }}
            <small class="text-muted font-weight-normal">— {{ $rep->ward?->name }} Ward, <strong>{{ $rep->lga?->name }} LGA</strong></small>
          </h5>

          <p class="p-2 bg-light rounded border small mb-2">{{ $rep->description }}</p>

          <div class="small text-muted">
            <span class="mr-3"><strong>Situation:</strong> {{ ucwords(str_replace('_', ' ', $rep->electricity_situation_enum)) }}</span>
            <span class="mr-3"><strong>Duration:</strong> {{ $rep->duration_of_problem }}</span>
            <span class="mr-3"><strong>Est. HH:</strong> {{ $rep->affected_households_estimate ?? 'N/A' }}</span>
            @if(!empty($rep->affected_facilities_json))
              <span class="mr-3"><strong>Facilities:</strong> {{ implode(', ', array_map('ucfirst', $rep->affected_facilities_json)) }}</span>
            @endif
            @if($rep->latitude && $rep->longitude)
              <span><i class="fas fa-map-marker-alt text-danger"></i> {{ number_format($rep->latitude, 4) }}, {{ number_format($rep->longitude, 4) }}</span>
            @endif
          </div>
        </div>

        <div class="col-lg-5">
          <div class="p-3 bg-light rounded border">
            <form method="POST" action="{{ route('reports.verify.update', $rep) }}">
              @csrf
              <div class="font-weight-bold small mb-2">LGA Officer Verification &amp; Field Assignment</div>
              <div class="form-row">
                <div class="form-group col-6 mb-2">
                  <label class="small mb-1">Decision Status</label>
                  <select name="status_enum" required class="form-control form-control-sm">
                    <option value="under_review" @selected($rep->status_enum === 'under_review')>Under Review</option>
                    <option value="verified" @selected($rep->status_enum === 'verified')>Verify Report</option>
                    <option value="rejected" @selected($rep->status_enum === 'rejected')>Reject Report</option>
                  </select>
                </div>
                <div class="form-group col-6 mb-2">
                  <label class="small mb-1">Assign Field Officer</label>
                  <select name="assigned_officer_id" class="form-control form-control-sm">
                    <option value="">-- Assign Officer --</option>
                    @foreach($fieldOfficers as $fo)
                      <option value="{{ $fo->id }}" @selected($rep->assigned_officer_id == $fo->id)>{{ $fo->name }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
              <div class="form-group mb-2">
                <input type="text" name="verification_comments" required
                  value="{{ $rep->verification_comments ?? 'Confirmed with ward head; scheduled for field energy assessment.' }}"
                  placeholder="Verification comments..." class="form-control form-control-sm">
              </div>
              <div class="d-flex justify-content-between">
                <button type="submit" class="btn btn-primary btn-sm">Save Decision</button>
                @if($rep->community_id && in_array($rep->status_enum, ['verified', 'assessed']))
                  <a href="{{ route('assessments.create', ['community_id' => $rep->community_id]) }}" class="btn btn-warning btn-sm">
                    Conduct Assessment <i class="fas fa-arrow-right ml-1"></i>
                  </a>
                @endif
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
@empty
  <div class="alert alert-light border text-center">No public reports match the selected filter.</div>
@endforelse

<div class="mt-3">
  {{ $reports->links() }}
</div>
@endsection
