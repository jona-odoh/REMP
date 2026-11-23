<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LicenseType extends Model
{
    use Auditable;

    protected $fillable = [
        'name',
        'code',
        'description',
        'validity_months',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'validity_months' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function feeSchedules(): HasMany
    {
        return $this->hasMany(FeeSchedule::class);
    }

    public function checklistTemplates(): HasMany
    {
        return $this->hasMany(InspectionChecklistTemplate::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('name');
    }
}
