<?php

namespace App\Models;

use App\Casts\PointCast;
use App\Enums\HotelLicenseStatus;
use App\Traits\Auditable;
use App\Traits\HasSpatialLocation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Hotel extends Model
{
    use Auditable, HasSpatialLocation;

    protected $fillable = [
        'name',
        'address',
        'lga_id',
        'establishment_type',
        'capacity_rooms',
        'capacity_beds',
        'owner_name',
        'owner_contact',
        'classification_rating',
        'license_status',
        'location',
        'is_approximate_location',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'capacity_rooms' => 'integer',
            'capacity_beds' => 'integer',
            'license_status' => HotelLicenseStatus::class,
            'location' => PointCast::class,
            'is_approximate_location' => 'boolean',
        ];
    }

    public function lga(): BelongsTo
    {
        return $this->belongsTo(Lga::class);
    }

    public function licenseApplications(): HasMany
    {
        return $this->hasMany(LicenseApplication::class)->latest();
    }

    public function inspections(): HasMany
    {
        return $this->hasMany(Inspection::class)->latest('scheduled_date');
    }

    public function licenseFees(): HasMany
    {
        return $this->hasMany(LicenseFee::class)->latest('paid_at');
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable')->latest();
    }

    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'notable')->whereNull('parent_id')->with(['author', 'replies'])->latest();
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['search'] ?? null, fn (Builder $q, string $s) => $q->where(function (Builder $sub) use ($s) {
                $sub->where('name', 'like', "%{$s}%")
                    ->orWhere('owner_name', 'like', "%{$s}%")
                    ->orWhere('address', 'like', "%{$s}%");
            }))
            ->when($filters['lga_id'] ?? null, fn (Builder $q, $lgaId) => $q->where('lga_id', $lgaId))
            ->when($filters['license_status'] ?? null, fn (Builder $q, string $status) => $q->where('license_status', $status))
            ->when($filters['establishment_type'] ?? null, fn (Builder $q, string $type) => $q->where('establishment_type', $type));
    }
}
