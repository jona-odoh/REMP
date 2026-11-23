<?php

namespace App\Models;

use App\Enums\Department;
use App\Enums\RoleName;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use Auditable, HasFactory, HasRoles, Notifiable;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'staff_id',
        'phone',
        'department',
        'password',
        'is_active',
        'must_change_password',
        'deactivated_at',
        'deactivated_by',
        'last_login_at',
        'last_login_ip',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'department' => Department::class,
            'is_active' => 'boolean',
            'must_change_password' => 'boolean',
            'deactivated_at' => 'datetime',
            'last_login_at' => 'datetime',
        ];
    }

    public function deactivatedBy(): BelongsTo
    {
        return $this->belongsTo(self::class, 'deactivated_by');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function primaryRoleName(): ?string
    {
        return $this->getRoleNames()->first();
    }

    public function primaryRoleLabel(): string
    {
        $role = $this->primaryRoleName();
        if (! $role) {
            return 'Unassigned';
        }

        return RoleName::tryFrom($role)?->label() ?? $role;
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole(RoleName::SUPER_ADMIN->value);
    }

    public function belongsToDepartment(Department $department): bool
    {
        return $this->department === $department;
    }
}
