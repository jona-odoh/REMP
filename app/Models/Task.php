<?php

namespace App\Models;

use App\Enums\TaskStatus;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    use Auditable;

    protected $fillable = [
        'title',
        'description',
        'assigned_to',
        'assigned_by',
        'related_entity_type',
        'related_entity_id',
        'due_date',
        'status',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'status' => TaskStatus::class,
            'completed_at' => 'datetime',
        ];
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function assigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function isOverdue(): bool
    {
        return $this->due_date !== null
            && $this->due_date->lt(today())
            && ! in_array($this->status, [TaskStatus::COMPLETED, TaskStatus::CANCELLED], true);
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereNotIn('status', [TaskStatus::COMPLETED->value, TaskStatus::CANCELLED->value]);
    }
}
