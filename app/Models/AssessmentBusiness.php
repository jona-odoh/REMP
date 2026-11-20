<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssessmentBusiness extends Model
{
    use HasFactory;

    protected $fillable = [
        'assessment_id',
        'name',
        'category',
        'business_type',
        'equipment_appliances_json',
        'operating_hours',
        'estimated_requirement_kwh',
        'existing_sources',
    ];

    protected function casts(): array
    {
        return [
            'equipment_appliances_json' => 'array',
            'operating_hours' => 'float',
            'estimated_requirement_kwh' => 'float',
        ];
    }

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }
}
