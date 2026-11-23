<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectExpenditure extends Model
{
    use Auditable;

    protected $fillable = [
        'project_id',
        'fiscal_year',
        'approved_budget',
        'actual_expenditure',
        'notes',
        'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'approved_budget' => 'decimal:2',
            'actual_expenditure' => 'decimal:2',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    protected function varianceAmount(): Attribute
    {
        return Attribute::get(fn (): float => round((float) $this->actual_expenditure - (float) $this->approved_budget, 2));
    }

    protected function variancePercent(): Attribute
    {
        return Attribute::get(function (): float {
            $approved = (float) $this->approved_budget;
            if ($approved <= 0) {
                return 0.0;
            }

            return round((((float) $this->actual_expenditure - $approved) / $approved) * 100, 1);
        });
    }

    protected function varianceFlag(): Attribute
    {
        return Attribute::get(function (): bool {
            $threshold = (float) SystemSetting::getValue('budget_variance_threshold_percent', 15);

            return (float) $this->approved_budget > 0 && abs($this->variance_percent) > $threshold;
        });
    }
}
