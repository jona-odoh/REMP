<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class EnergyDemand extends Model
{
    use HasFactory;

    protected $fillable = [
        'community_id',
        'assessment_id',
        'total_daily_kwh',
        'peak_load_kw',
        'calculation_assumptions_json',
        'calculated_by',
    ];

    protected function casts(): array
    {
        return [
            'total_daily_kwh' => 'float',
            'peak_load_kw' => 'float',
            'calculation_assumptions_json' => 'array',
        ];
    }

    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class);
    }

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }

    public function calculator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'calculated_by');
    }

    public function recommendation(): HasOne
    {
        return $this->hasOne(Recommendation::class);
    }
}
