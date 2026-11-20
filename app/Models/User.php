<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_enum',
        'lga_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function lga(): BelongsTo
    {
        return $this->belongsTo(Lga::class);
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class, 'officer_id');
    }

    public function verifiedReports(): HasMany
    {
        return $this->hasMany(Report::class, 'verified_by');
    }

    public function hasRoleOrEnum(string|array $roles): bool
    {
        $roles = (array) $roles;
        if (in_array($this->role_enum, $roles, true)) {
            return true;
        }
        return $this->hasAnyRole($roles);
    }
}
