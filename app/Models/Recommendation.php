<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Recommendation extends Model
{
    use HasFactory;

    protected $fillable = [
        'community_id',
        'energy_demand_id',
        'recommended_solution_enum',
        'justification',
        'estimated_capacity_kw',
        'equipment_requirements_json',
        'analyst_notes',
        'reviewed_by',
    ];

    protected function casts(): array
    {
        return [
            'estimated_capacity_kw' => 'float',
            'equipment_requirements_json' => 'array',
        ];
    }

    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class);
    }

    public function energyDemand(): BelongsTo
    {
        return $this->belongsTo(EnergyDemand::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function costEstimate(): HasOne
    {
        return $this->hasOne(CostEstimate::class);
    }

    public function getSolutionLabelAttribute(): string
    {
        return match ($this->recommended_solution_enum) {
            'solar_home_system' => 'Solar Home Systems (SHS)',
            'solar_mini_grid' => 'Solar Hybrid Mini-Grid',
            'institutional_solar' => 'Institutional Solar System',
            'solar_water_pumping' => 'Solar Water Pumping System',
            'productive_use_solar' => 'Productive-Use Solar System (PUE)',
            default => ucwords(str_replace('_', ' ', (string) $this->recommended_solution_enum)),
        };
    }
}
