<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssessmentHealthFacility extends Model
{
    use HasFactory;

    protected $fillable = [
        'assessment_id',
        'name',
        'location',
        'facility_type',
        'medical_equipment_json',
        'refrigeration_requirements',
        'lighting_needs',
        'existing_sources',
    ];

    protected function casts(): array
    {
        return [
            'medical_equipment_json' => 'array',
        ];
    }

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }
}
