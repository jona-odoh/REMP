<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lga extends Model
{
    use Auditable;

    protected $fillable = [
        'name',
        'code',
        'senatorial_district',
        'headquarters',
        'centroid_lat',
        'centroid_lng',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'centroid_lat' => 'decimal:7',
            'centroid_lng' => 'decimal:7',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('name');
    }

    public function culturalAssets(): HasMany
    {
        return $this->hasMany(CulturalAsset::class);
    }

    public function hotels(): HasMany
    {
        return $this->hasMany(Hotel::class);
    }
}
