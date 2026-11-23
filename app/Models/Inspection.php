<?php

namespace App\Models;

use App\Enums\InspectionStatus;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Inspection extends Model
{
    use Auditable;

    protected $fillable = [
        'hotel_id',
        'license_application_id',
        'assigned_officer_id',
        'scheduled_date',
        'completed_date',
        'status',
        'checklist_results',
        'overall_passed',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_date' => 'date',
            'completed_date' => 'date',
            'status' => InspectionStatus::class,
            'checklist_results' => 'array',
            'overall_passed' => 'boolean',
        ];
    }

    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    public function licenseApplication(): BelongsTo
    {
        return $this->belongsTo(LicenseApplication::class);
    }

    public function assignedOfficer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_officer_id');
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable')->latest();
    }

    public function isOverdue(): bool
    {
        return $this->scheduled_date !== null
            && $this->scheduled_date->lt(today())
            && ! in_array($this->status, [InspectionStatus::COMPLETED, InspectionStatus::CANCELLED], true);
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query
            ->whereDate('scheduled_date', '<', today())
            ->whereNotIn('status', [InspectionStatus::COMPLETED->value, InspectionStatus::CANCELLED->value]);
    }
}
