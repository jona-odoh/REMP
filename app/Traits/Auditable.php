<?php

namespace App\Traits;

use App\Enums\AuditAction;
use App\Services\AuditService;
use Illuminate\Database\Eloquent\Model;

trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function (Model $model): void {
            if (AuditService::$disabled) {
                return;
            }

            app(AuditService::class)->record(
                action: AuditAction::CREATE,
                entity: $model,
                before: null,
                after: $model->getAttributes(),
            );
        });

        static::updated(function (Model $model): void {
            if (AuditService::$disabled) {
                return;
            }

            $changes = $model->getChanges();
            unset($changes['updated_at']);

            if ($changes === []) {
                return;
            }

            $before = [];
            foreach (array_keys($changes) as $key) {
                $before[$key] = $model->getOriginal($key);
            }

            app(AuditService::class)->record(
                action: AuditAction::UPDATE,
                entity: $model,
                before: $before,
                after: $changes,
            );
        });

        static::deleted(function (Model $model): void {
            if (AuditService::$disabled) {
                return;
            }

            app(AuditService::class)->record(
                action: AuditAction::DELETE,
                entity: $model,
                before: $model->getOriginal(),
                after: null,
            );
        });
    }
}
