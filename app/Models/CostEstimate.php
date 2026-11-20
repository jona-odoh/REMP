<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CostEstimate extends Model
{
    use HasFactory;

    protected $fillable = [
        'recommendation_id',
        'solar_panels_cost',
        'batteries_cost',
        'inverters_cost',
        'charge_controllers_cost',
        'mounting_structures_cost',
        'distribution_cost',
        'protection_equipment_cost',
        'installation_cost',
        'transportation_cost',
        'total_estimated_cost_ngn',
        'assumptions_json',
    ];

    protected function casts(): array
    {
        return [
            'solar_panels_cost' => 'float',
            'batteries_cost' => 'float',
            'inverters_cost' => 'float',
            'charge_controllers_cost' => 'float',
            'mounting_structures_cost' => 'float',
            'distribution_cost' => 'float',
            'protection_equipment_cost' => 'float',
            'installation_cost' => 'float',
            'transportation_cost' => 'float',
            'total_estimated_cost_ngn' => 'float',
            'assumptions_json' => 'array',
        ];
    }

    public function recommendation(): BelongsTo
    {
        return $this->belongsTo(Recommendation::class);
    }
}
