<?php

namespace App\Listeners;

use App\Enums\AuditAction;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Events\Dispatcher;

class LogAuthenticationEvents
{
    public function __construct(
        private readonly AuditService $auditService,
    ) {}

    public function handleLogin(Login $event): void
    {
        if (! $event->user instanceof User) {
            return;
        }

        $event->user->forceFill([
            'last_login_at' => now(),
            'last_login_ip' => request()?->ip(),
        ])->saveQuietly();

        $this->auditService->record(
            action: AuditAction::LOGIN,
            entity: $event->user,
            after: [
                'email' => $event->user->email,
                'role' => $event->user->getRoleNames()->first(),
            ],
            actor: $event->user,
        );
    }

    public function handleLogout(Logout $event): void
    {
        if (! $event->user instanceof User) {
            return;
        }

        $this->auditService->record(
            action: AuditAction::LOGOUT,
            entity: $event->user,
            before: [
                'email' => $event->user->email,
            ],
            actor: $event->user,
        );
    }

    public function subscribe(Dispatcher $events): array
    {
        return [
            Login::class => 'handleLogin',
            Logout::class => 'handleLogout',
        ];
    }
}
