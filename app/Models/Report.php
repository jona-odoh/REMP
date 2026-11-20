<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_number',
        'lga_id',
        'ward_id',
        'community_id',
        'custom_community_name',
        'reporter_name',
        'reporter_contact',
        'electricity_situation_enum',
        'duration_of_problem',
        'affected_households_estimate',
        'affected_facilities_json',
        'description',
        'latitude',
        'longitude',
        'photo_path',
        'status_enum',
        'is_potential_duplicate',
        'verification_comments',
        'verified_by',
        'verified_at',
        'assigned_officer_id',
    ];

    protected function casts(): array
    {
        return [
            'affected_facilities_json' => 'array',
            'latitude' => 'float',
            'longitude' => 'float',
            'is_potential_duplicate' => 'boolean',
            'verified_at' => 'datetime',
        ];
    }

    public function lga(): BelongsTo
    {
        return $this->belongsTo(Lga::class);
    }

    public function ward(): BelongsTo
    {
        return $this->belongsTo(Ward::class);
    }

    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function assignedOfficer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_officer_id');
    }

    public static function generateReferenceNumber(): string
    {
        do {
            $ref = 'KOGI-' . date('Y') . '-' . strtoupper(substr(md5(uniqid((string) mt_rand(), true)), 0, 5));
        } while (self::where('reference_number', $ref)->exists());

        return $ref;
    }
}
