<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriorityRanking extends Model
{
    use HasFactory;

    protected $fillable = [
        'community_id',
        'score',
        'priority_level_enum',
        'ranking_criteria_json',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'float',
            'ranking_criteria_json' => 'array',
        ];
    }

    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class);
    }
}
