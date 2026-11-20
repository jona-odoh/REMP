<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssessmentAppliance extends Model
{
    use HasFactory;

    protected $fillable = [
        'assessment_id',
        'facility_type',
        'facility_record_id',
        'appliance_id',
        'custom_appliance_name',
        'rated_power_watts',
        'quantity',
        'operating_hours_per_day',
        'calculated_daily_energy_wh',
    ];

    protected function casts(): array
    {
        return [
            'operating_hours_per_day' => 'float',
            'calculated_daily_energy_wh' => 'float',
        ];
    }

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }

    public function appliance(): BelongsTo
    {
        return $this->belongsTo(Appliance::class);
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->custom_appliance_name ?: ($this->appliance?->name ?? 'Appliance');
    }
}
