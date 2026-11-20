<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Community extends Model
{
    use HasFactory;

    protected $fillable = [
        'ward_id',
        'name',
        'description',
        'latitude',
        'longitude',
        'estimated_population',
        'estimated_households',
        'electricity_status_enum',
        'is_verified',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'is_verified' => 'boolean',
        ];
    }

    public function ward(): BelongsTo
    {
        return $this->belongsTo(Ward::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class);
    }

    public function latestAssessment(): HasOne
    {
        return $this->hasOne(Assessment::class)->latestOfMany();
    }

    public function energyDemands(): HasMany
    {
        return $this->hasMany(EnergyDemand::class);
    }

    public function latestEnergyDemand(): HasOne
    {
        return $this->hasOne(EnergyDemand::class)->latestOfMany();
    }

    public function recommendations(): HasMany
    {
        return $this->hasMany(Recommendation::class);
    }

    public function latestRecommendation(): HasOne
    {
        return $this->hasOne(Recommendation::class)->latestOfMany();
    }

    public function priorityRanking(): HasOne
    {
        return $this->hasOne(PriorityRanking::class)->latestOfMany();
    }
}
