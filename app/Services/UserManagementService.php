<?php

namespace App\Services;

use App\Enums\AuditAction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class UserManagementService
{
    public function __construct(
        private readonly AuditService $auditService,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function createUser(array $data): User
    {
        return DB::transaction(function () use ($data): User {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'staff_id' => $data['staff_id'] ?? null,
                'phone' => $data['phone'] ?? null,
                'department' => $data['department'] ?? null,
                'password' => $data['password'],
                'is_active' => $data['is_active'] ?? true,
                'must_change_password' => $data['must_change_password'] ?? true,
            ]);

            $user->syncRoles([$data['role']]);

            $this->auditService->record(
                action: AuditAction::UPDATE,
                entity: $user,
                before: ['roles' => []],
                after: ['roles' => [$data['role']]],
                metadata: ['event' => 'role_assigned'],
            );

            return $user;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateUser(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data): User {
            $previousRoles = $user->getRoleNames()->values()->all();

            $user->update([
                'name' => $data['name'],
                'email' => $data['email'],
                'staff_id' => $data['staff_id'] ?? null,
                'phone' => $data['phone'] ?? null,
                'department' => $data['department'] ?? null,
            ]);

            if (! empty($data['role']) && $previousRoles !== [$data['role']]) {
                $user->syncRoles([$data['role']]);

                $this->auditService->record(
                    action: AuditAction::UPDATE,
                    entity: $user,
                    before: ['roles' => $previousRoles],
                    after: ['roles' => [$data['role']]],
                    metadata: ['event' => 'role_changed'],
                );
            }

            return $user->fresh('roles');
        });
    }

    public function deactivate(User $target, User $actor, ?string $reason = null): User
    {
        if ($target->is($actor)) {
            throw new InvalidArgumentException('You cannot deactivate your own account.');
        }

        return DB::transaction(function () use ($target, $actor, $reason): User {
            $before = ['is_active' => $target->is_active];

            $target->forceFill([
                'is_active' => false,
                'deactivated_at' => now(),
                'deactivated_by' => $actor->getKey(),
                'remember_token' => Str::random(60),
            ])->saveQuietly();

            $sessionsTerminated = $this->invalidateSessions($target);

            $this->auditService->record(
                action: AuditAction::DEACTIVATE,
                entity: $target,
                before: $before,
                after: ['is_active' => false],
                metadata: [
                    'reason' => $reason,
                    'sessions_terminated' => $sessionsTerminated,
                ],
                actor: $actor,
            );

            return $target;
        });
    }

    public function reactivate(User $target, User $actor): User
    {
        return DB::transaction(function () use ($target, $actor): User {
            $before = ['is_active' => $target->is_active];

            $target->forceFill([
                'is_active' => true,
                'deactivated_at' => null,
                'deactivated_by' => null,
            ])->saveQuietly();

            $this->auditService->record(
                action: AuditAction::REACTIVATE,
                entity: $target,
                before: $before,
                after: ['is_active' => true],
                actor: $actor,
            );

            return $target;
        });
    }

    public function resetPassword(User $target, User $actor, ?string $plainPassword = null): string
    {
        $temporaryPassword = $plainPassword ?: $this->generateTemporaryPassword();

        DB::transaction(function () use ($target, $actor, $temporaryPassword): void {
            $target->forceFill([
                'password' => $temporaryPassword,
                'must_change_password' => true,
                'remember_token' => Str::random(60),
            ])->saveQuietly();

            $sessionsTerminated = $this->invalidateSessions($target);

            $this->auditService->record(
                action: AuditAction::PASSWORD_RESET,
                entity: $target,
                after: ['must_change_password' => true],
                metadata: [
                    'initiated_by' => $actor->email,
                    'sessions_terminated' => $sessionsTerminated,
                ],
                actor: $actor,
            );
        });

        return $temporaryPassword;
    }

    public function forceLogout(User $target, User $actor): int
    {
        if ($target->is($actor)) {
            throw new InvalidArgumentException('You cannot force-logout your own active session.');
        }

        return DB::transaction(function () use ($target, $actor): int {
            $target->forceFill([
                'remember_token' => Str::random(60),
            ])->saveQuietly();

            $sessionsTerminated = $this->invalidateSessions($target);

            $this->auditService->record(
                action: AuditAction::FORCE_LOGOUT,
                entity: $target,
                metadata: [
                    'initiated_by' => $actor->email,
                    'sessions_terminated' => $sessionsTerminated,
                ],
                actor: $actor,
            );

            return $sessionsTerminated;
        });
    }

    protected function invalidateSessions(User $user): int
    {
        return DB::table(config('session.table', 'sessions'))
            ->where('user_id', $user->getKey())
            ->delete();
    }

    protected function generateTemporaryPassword(): string
    {
        return 'Ksmct!'.Str::upper(Str::random(4)).random_int(100, 999).'a';
    }
}
