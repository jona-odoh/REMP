<?php

namespace App\Models;

use App\Enums\ProjectStatus;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Project extends Model
{
    use Auditable;

    protected $fillable = [
        'title',
        'description',
        'lga_id',
        'budget_line',
        'funding_source',
        'implementing_partner',
        'start_date',
        'end_date',
        'status',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'status' => ProjectStatus::class,
        ];
    }

    public function lga(): BelongsTo
    {
        return $this->belongsTo(Lga::class);
    }

    public function milestones(): HasMany
    {
        return $this->hasMany(Milestone::class)->orderBy('due_date');
    }

    public function expenditures(): HasMany
    {
        return $this->hasMany(ProjectExpenditure::class)->latest();
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable')->latest();
    }

    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'notable')->whereNull('parent_id')->with(['author', 'replies'])->latest();
    }

    public function totalApprovedBudget(): float
    {
        return (float) $this->expenditures->sum('approved_budget');
    }

    public function totalActualExpenditure(): float
    {
        return (float) $this->expenditures->sum('actual_expenditure');
    }

    public function budgetUtilizationPercent(): float
    {
        $approved = $this->totalApprovedBudget();
        if ($approved <= 0) {
            return 0.0;
        }

        return round(($this->totalActualExpenditure() / $approved) * 100, 1);
    }

    public function variancePercent(): float
    {
        $approved = $this->totalApprovedBudget();
        if ($approved <= 0) {
            return 0.0;
        }

        return round((($this->totalActualExpenditure() - $approved) / $approved) * 100, 1);
    }

    public function hasVarianceAlert(?float $thresholdPercent = null): bool
    {
        $threshold = $thresholdPercent ?? (float) SystemSetting::getValue('budget_variance_threshold_percent', 15);

        return $this->totalApprovedBudget() > 0 && abs($this->variancePercent()) > $threshold;
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['search'] ?? null, fn (Builder $q, string $s) => $q->where(function (Builder $sub) use ($s) {
                $sub->where('title', 'like', "%{$s}%")
                    ->orWhere('budget_line', 'like', "%{$s}%")
                    ->orWhere('funding_source', 'like', "%{$s}%")
                    ->orWhere('implementing_partner', 'like', "%{$s}%");
            }))
            ->when($filters['lga_id'] ?? null, fn (Builder $q, $id) => $q->where('lga_id', $id))
            ->when($filters['status'] ?? null, fn (Builder $q, string $status) => $q->where('status', $status));
    }
}
