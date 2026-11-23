<?php

namespace App\Models;

use App\Enums\InspectionStatus;
use App\Enums\LicenseApplicationStatus;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class LicenseApplication extends Model
{
    use Auditable;

    protected $fillable = [
        'application_number',
        'hotel_id',
        'license_type_id',
        'application_type',
        'status',
        'assigned_officer_id',
        'submitted_at',
        'reviewed_at',
        'decided_at',
        'decided_by',
        'decision_reason',
        'license_number',
        'issued_at',
        'expires_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => LicenseApplicationStatus::class,
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'decided_at' => 'datetime',
            'issued_at' => 'date',
            'expires_at' => 'date',
        ];
    }

    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    public function licenseType(): BelongsTo
    {
        return $this->belongsTo(LicenseType::class);
    }

    public function assignedOfficer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_officer_id');
    }

    public function decidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }

    public function inspections(): HasMany
    {
        return $this->hasMany(Inspection::class)->latest('scheduled_date');
    }

    public function fees(): HasMany
    {
        return $this->hasMany(LicenseFee::class);
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable')->latest();
    }

    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'notable')->whereNull('parent_id')->with(['author', 'replies'])->latest();
    }

    public function hasCompletedInspection(): bool
    {
        return $this->inspections()
            ->where('status', InspectionStatus::COMPLETED->value)
            ->exists();
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['status'] ?? null, fn (Builder $q, string $s) => $q->where('status', $s))
            ->when($filters['assigned_officer_id'] ?? null, fn (Builder $q, $id) => $q->where('assigned_officer_id', $id))
            ->when($filters['lga_id'] ?? null, fn (Builder $q, $lgaId) => $q->whereHas('hotel', fn (Builder $h) => $h->where('lga_id', $lgaId)))
            ->when($filters['search'] ?? null, fn (Builder $q, string $search) => $q->where(function (Builder $sub) use ($search) {
                $sub->where('application_number', 'like', "%{$search}%")
                    ->orWhere('license_number', 'like', "%{$search}%")
                    ->orWhereHas('hotel', fn (Builder $h) => $h->where('name', 'like', "%{$search}%"));
            }));
    }
}
