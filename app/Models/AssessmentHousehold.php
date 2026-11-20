<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssessmentHousehold extends Model
{
    use HasFactory;

    protected $fillable = [
        'assessment_id',
        'households_assessed_count',
        'households_without_electricity',
        'existing_sources',
        'common_appliances_json',
        'estimated_daily_usage_kwh',
    ];

    protected function casts(): array
    {
        return [
            'common_appliances_json' => 'array',
            'estimated_daily_usage_kwh' => 'float',
        ];
    }

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }
}
