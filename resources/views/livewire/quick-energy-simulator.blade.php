<div class="card card-primary">
  <div class="card-header">
    <h4><i class="fas fa-sliders-h mr-2 text-primary"></i>Interactive Community Demand &amp; Cost Simulator (Livewire)</h4>
    <div class="card-header-action">
      <span class="badge badge-info">4.6 PSH Kogi Insolation</span>
    </div>
  </div>
  <div class="card-body">
    <div class="row">
      <div class="col-6 col-md-2">
        <div class="form-group">
          <label class="small font-weight-bold">Households</label>
          <input type="number" wire:model.live="sampleHouseholds" min="5" max="2000" class="form-control form-control-sm">
        </div>
      </div>
      <div class="col-6 col-md-2">
        <div class="form-group">
          <label class="small font-weight-bold">Schools</label>
          <input type="number" wire:model.live="sampleSchools" min="0" max="20" class="form-control form-control-sm">
        </div>
      </div>
      <div class="col-6 col-md-2">
        <div class="form-group">
          <label class="small font-weight-bold">Clinics / PHCs</label>
          <input type="number" wire:model.live="sampleClinics" min="0" max="10" class="form-control form-control-sm">
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="form-group">
          <label class="small font-weight-bold">SMEs / Agro-Mills</label>
          <input type="number" wire:model.live="sampleBusinesses" min="0" max="100" class="form-control form-control-sm">
        </div>
      </div>
      <div class="col-12 col-md-3">
        <div class="form-group">
          <label class="small font-weight-bold">Coincidence Factor</label>
          <input type="number" step="0.05" wire:model.live="simultaneousFactor" min="0.2" max="1.0" class="form-control form-control-sm">
        </div>
      </div>
    </div>

    <div class="row mt-2">
      <div class="col-6 col-md-3 mb-2">
        <div class="p-3 bg-light rounded border">
          <div class="text-muted small">Est. Daily Energy</div>
          <div class="h5 font-weight-bold text-success mb-0">{{ number_format($dailyKwh, 2) }} kWh/day</div>
        </div>
      </div>
      <div class="col-6 col-md-3 mb-2">
        <div class="p-3 bg-light rounded border">
          <div class="text-muted small">Peak Load / PV Array</div>
          <div class="h5 font-weight-bold text-primary mb-0">{{ number_format($peakKw, 2) }} kW / {{ number_format($pvCapacityKw, 1) }} kWp</div>
        </div>
      </div>
      <div class="col-6 col-md-3 mb-2">
        <div class="p-3 bg-light rounded border">
          <div class="text-muted small">Suggested Intervention</div>
          <div class="font-weight-bold text-warning mt-1">{{ $recommendedSolution }}</div>
        </div>
      </div>
      <div class="col-6 col-md-3 mb-2">
        <div class="p-3 bg-light rounded border">
          <div class="text-muted small">Indicative CAPEX (NGN)</div>
          <div class="h5 font-weight-bold text-dark mb-0">&#8358;{{ number_format($indicativeCostNgn, 0) }}</div>
        </div>
      </div>
    </div>
  </div>
</div>
