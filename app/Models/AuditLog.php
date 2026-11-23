<?php

namespace App\Models;

use App\Enums\AuditAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class AuditLog extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'audit_logs';

    protected $fillable = [
        'user_id',
        'action',
        'entity_type',
        'entity_id',
        'before_value',
        'after_value',
        'ip_address',
        'user_agent',
        'metadata',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'action' => AuditAction::class,
            'before_value' => 'array',
            'after_value' => 'array',
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::updating(function () {
            throw new LogicException('Audit logs are strictly read-only and cannot be modified.');
        });

        static::deleting(function () {
            throw new LogicException('Audit logs are strictly read-only and cannot be deleted.');
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shortEntityType(): string
    {
        if (! $this->entity_type) {
            return 'System';
        }

        return class_basename($this->entity_type);
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['action'] ?? null, fn (Builder $q, string $action) => $q->where('action', $action))
            ->when($filters['user_id'] ?? null, fn (Builder $q, $userId) => $q->where('user_id', $userId))
            ->when($filters['entity_type'] ?? null, fn (Builder $q, string $entityType) => $q->where('entity_type', 'like', '%'.$entityType.'%'))
            ->when($filters['date_from'] ?? null, fn (Builder $q, string $from) => $q->whereDate('created_at', '>=', $from))
            ->when($filters['date_to'] ?? null, fn (Builder $q, string $to) => $q->whereDate('created_at', '<=', $to))
            ->when($filters['search'] ?? null, function (Builder $q, string $search) {
                $q->where(function (Builder $sub) use ($search) {
                    $sub->where('entity_type', 'like', '%'.$search.'%')
                        ->orWhere('ip_address', 'like', '%'.$search.'%')
                        ->orWhere('action', 'like', '%'.$search.'%')
                        ->orWhereHas('user', fn (Builder $u) => $u->where('name', 'like', '%'.$search.'%')->orWhere('email', 'like', '%'.$search.'%'));
                });
            });
    }
}
