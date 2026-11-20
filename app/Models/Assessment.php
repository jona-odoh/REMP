<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Assessment extends Model
{
    use HasFactory;

    protected $fillable = [
        'community_id',
        'officer_id',
        'assessment_date',
        'grid_connected_boolean',
        'average_supply_hours',
        'interruption_frequency',
        'alternative_energy_sources',
        'households_with_electricity',
        'households_without_electricity',
        'gps_coordinates',
        'photo_path',
        'status_enum',
    ];

    protected function casts(): array
    {
        return [
            'assessment_date' => 'date',
            'grid_connected_boolean' => 'boolean',
            'average_supply_hours' => 'float',
        ];
    }

    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class);
    }

    public function officer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'officer_id');
    }

    public function householdAssessment(): HasOne
    {
        return $this->hasOne(AssessmentHousehold::class);
    }

    public function schools(): HasMany
    {
        return $this->hasMany(AssessmentSchool::class);
    }

    public function healthFacilities(): HasMany
    {
        return $this->hasMany(AssessmentHealthFacility::class);
    }

    public function businesses(): HasMany
    {
        return $this->hasMany(AssessmentBusiness::class);
    }

    public function assessmentAppliances(): HasMany
    {
        return $this->hasMany(AssessmentAppliance::class);
    }

    public function energyDemand(): HasOne
    {
        return $this->hasOne(EnergyDemand::class);
    }
}
