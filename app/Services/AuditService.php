<?php

namespace App\Services;

use App\Enums\AuditAction;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AuditService
{
    public static bool $disabled = false;

    /**
     * Sensitive attributes stripped from audit snapshots.
     *
     * @var array<int, string>
     */
    protected array $redactedAttributes = [
        'password',
        'remember_token',
    ];

    public function record(
        AuditAction $action,
        ?Model $entity = null,
        ?array $before = null,
        ?array $after = null,
        ?array $metadata = null,
        ?User $actor = null,
    ): AuditLog {
        $resolvedActor = $actor ?? Auth::user();
        $request = app()->bound('request') ? request() : null;

        return AuditLog::create([
            'user_id' => $resolvedActor instanceof User ? $resolvedActor->getKey() : null,
            'action' => $action,
            'entity_type' => $entity ? $entity->getMorphClass() : null,
            'entity_id' => $entity ? $entity->getKey() : null,
            'before_value' => $before !== null ? $this->sanitize($before) : null,
            'after_value' => $after !== null ? $this->sanitize($after) : null,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent() ? Str::limit((string) $request->userAgent(), 512, '') : null,
            'metadata' => $metadata,
            'created_at' => now(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    protected function sanitize(array $values): array
    {
        foreach ($this->redactedAttributes as $attribute) {
            if (array_key_exists($attribute, $values)) {
                $values[$attribute] = '[REDACTED]';
            }
        }

        return $values;
    }
}
