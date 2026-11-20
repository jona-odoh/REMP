@extends('layouts.app')

@section('title', 'Phase 3: User Roles & Permissions Administration — Kogi State REMP')

@section('content')
<div class="card">
  <div class="card-body py-3">
    <span class="badge badge-primary mb-1">Phase 3 &bull; Role-Based Access Control (spatie/laravel-permission)</span>
    <h4 class="mb-0 font-weight-bold text-dark">Platform User &amp; Role Administration</h4>
    <small class="text-muted">Manage System Administrators, Field Data Collectors, LGA Officers, Energy Analysts, and Stakeholder Viewers.</small>
  </div>
</div>

<div class="row">
  <div class="col-lg-4">
    <div class="card card-primary">
      <div class="card-header">
        <h4>Provision New Official Account</h4>
      </div>
      <div class="card-body">
        <form method="POST" action="{{ route('users.store') }}">
          @csrf
          <div class="form-group">
            <label class="small font-weight-bold">Full Name *</label>
            <input type="text" name="name" required placeholder="Engr. Musa Ibrahim" class="form-control form-control-sm">
          </div>
          <div class="form-group">
            <label class="small font-weight-bold">Official Email *</label>
            <input type="email" name="email" required placeholder="officer@kogiremp.gov.ng" class="form-control form-control-sm">
          </div>
          <div class="form-group">
            <label class="small font-weight-bold">Password *</label>
            <input type="password" name="password" value="password" required class="form-control form-control-sm">
          </div>
          <div class="form-group">
            <label class="small font-weight-bold">Platform Role *</label>
            <select name="role_enum" required class="form-control form-control-sm">
              <option value="field_officer">Field Data Collector</option>
              <option value="lga_officer">LGA Officer</option>
              <option value="energy_analyst">Energy Analyst</option>
              <option value="stakeholder_viewer">Government / Stakeholder Viewer</option>
              <option value="admin">System Administrator</option>
            </select>
          </div>
          <div class="form-group">
            <label class="small font-weight-bold">Assigned LGA (Optional)</label>
            <select name="lga_id" class="form-control form-control-sm">
              <option value="">Statewide (All LGAs)</option>
              @foreach($lgas as $lga)
                <option value="{{ $lga->id }}">{{ $lga->name }} LGA</option>
              @endforeach
            </select>
          </div>
          <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-user-plus mr-1"></i> Create Official User</button>
        </form>
      </div>
    </div>
  </div>

  <div class="col-lg-8">
    <div class="card">
      <div class="card-header">
        <h4>Registered Official Accounts ({{ $users->count() }})</h4>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-striped table-sm mb-0">
            <thead>
              <tr>
                <th>Name &amp; Email</th>
                <th>Current Role</th>
                <th>Assigned LGA</th>
                <th>Update Role / Jurisdiction</th>
              </tr>
            </thead>
            <tbody>
              @foreach($users as $u)
                <tr>
                  <td>
                    <div class="font-weight-bold">{{ $u->name }}</div>
                    <small class="text-muted">{{ $u->email }}</small>
                  </td>
                  <td><span class="badge badge-primary text-uppercase">{{ str_replace('_', ' ', $u->role_enum) }}</span></td>
                  <td>{{ $u->lga?->name ?? 'All LGAs' }}</td>
                  <td>
                    <form method="POST" action="{{ route('users.role.update', $u) }}" class="form-inline">
                      @csrf
                      @method('PUT')
                      <select name="role_enum" class="form-control form-control-sm mr-1 mb-1">
                        @foreach(['admin', 'field_officer', 'lga_officer', 'energy_analyst', 'stakeholder_viewer'] as $r)
                          <option value="{{ $r }}" @selected($u->role_enum === $r)>{{ str_replace('_', ' ', $r) }}</option>
                        @endforeach
                      </select>
                      <select name="lga_id" class="form-control form-control-sm mr-1 mb-1">
                        <option value="">All LGAs</option>
                        @foreach($lgas as $lga)
                          <option value="{{ $lga->id }}" @selected($u->lga_id == $lga->id)>{{ $lga->name }}</option>
                        @endforeach
                      </select>
                      <button type="submit" class="btn btn-dark btn-sm mb-1">Save</button>
                    </form>
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
@endsection
