<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CulturalAssetVersion extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'cultural_asset_id',
        'version_number',
        'snapshot',
        'change_summary',
        'changed_by',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'version_number' => 'integer',
            'snapshot' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function culturalAsset(): BelongsTo
    {
        return $this->belongsTo(CulturalAsset::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
