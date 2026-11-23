<?php

namespace App\Models;

use App\Enums\SafeguardingStatus;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class IntangibleHeritage extends Model
{
    use Auditable;

    protected $fillable = [
        'name',
        'lga_id',
        'festival_season',
        'participating_communities',
        'historical_description',
        'safeguarding_status',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'safeguarding_status' => SafeguardingStatus::class,
        ];
    }

    public function lga(): BelongsTo
    {
        return $this->belongsTo(Lga::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable')->latest();
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['search'] ?? null, fn (Builder $q, string $search) => $q->where(function (Builder $sub) use ($search) {
                $sub->where('name', 'like', "%{$search}%")
                    ->orWhere('participating_communities', 'like', "%{$search}%")
                    ->orWhere('festival_season', 'like', "%{$search}%");
            }))
            ->when($filters['lga_id'] ?? null, fn (Builder $q, $lgaId) => $q->where('lga_id', $lgaId))
            ->when($filters['status'] ?? null, fn (Builder $q, string $status) => $q->where('safeguarding_status', $status));
    }
}
