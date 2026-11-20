<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssessmentSchool extends Model
{
    use HasFactory;

    protected $fillable = [
        'assessment_id',
        'name',
        'location',
        'school_type',
        'classrooms_count',
        'offices_count',
        'equipment_appliances_json',
        'existing_sources',
        'operating_hours',
    ];

    protected function casts(): array
    {
        return [
            'equipment_appliances_json' => 'array',
            'operating_hours' => 'float',
        ];
    }

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }
}
