<?php

namespace App\Models;

use App\Casts\PointCast;
use App\Enums\ConditionAssessment;
use App\Enums\OwnershipStatus;
use App\Traits\Auditable;
use App\Traits\HasSpatialLocation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class CulturalAsset extends Model
{
    use Auditable, HasSpatialLocation;

    protected $fillable = [
        'name',
        'category_id',
        'lga_id',
        'location',
        'is_approximate_location',
        'ownership_status',
        'condition_assessment',
        'visitor_capacity',
        'description',
        'current_version',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'location' => PointCast::class,
            'is_approximate_location' => 'boolean',
            'ownership_status' => OwnershipStatus::class,
            'condition_assessment' => ConditionAssessment::class,
            'visitor_capacity' => 'integer',
            'current_version' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class, 'category_id');
    }

    public function lga(): BelongsTo
    {
        return $this->belongsTo(Lga::class, 'lga_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(CulturalAssetVersion::class)->orderByDesc('version_number');
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable')->latest();
    }

    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'notable')->whereNull('parent_id')->with(['author', 'replies'])->latest();
    }

    /**
     * @return array<string, mixed>
     */
    public function toVersionSnapshot(): array
    {
        return [
            'name' => $this->name,
            'category_id' => $this->category_id,
            'lga_id' => $this->lga_id,
            'latitude' => $this->location?->latitude,
            'longitude' => $this->location?->longitude,
            'is_approximate_location' => $this->is_approximate_location,
            'ownership_status' => $this->ownership_status?->value,
            'condition_assessment' => $this->condition_assessment?->value,
            'visitor_capacity' => $this->visitor_capacity,
            'description' => $this->description,
        ];
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['search'] ?? null, fn (Builder $q, string $search) => $q->where(function (Builder $sub) use ($search) {
                $sub->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            }))
            ->when($filters['category_id'] ?? null, fn (Builder $q, $catId) => $q->where('category_id', $catId))
            ->when($filters['lga_id'] ?? null, fn (Builder $q, $lgaId) => $q->where('lga_id', $lgaId))
            ->when($filters['condition'] ?? null, fn (Builder $q, string $cond) => $q->where('condition_assessment', $cond))
            ->when($filters['ownership'] ?? null, fn (Builder $q, string $own) => $q->where('ownership_status', $own));
    }
}
