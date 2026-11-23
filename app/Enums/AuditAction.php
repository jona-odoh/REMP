<?php

namespace App\Enums;

enum AuditAction: string
{
    case CREATE = 'CREATE';
    case UPDATE = 'UPDATE';
    case DELETE = 'DELETE';
    case APPROVE = 'APPROVE';
    case REJECT = 'REJECT';
    case LOGIN = 'LOGIN';
    case LOGOUT = 'LOGOUT';
    case FORCE_LOGOUT = 'FORCE_LOGOUT';
    case DEACTIVATE = 'DEACTIVATE';
    case REACTIVATE = 'REACTIVATE';
    case PASSWORD_RESET = 'PASSWORD_RESET';
    case VIEW = 'VIEW';
    case DOWNLOAD = 'DOWNLOAD';

    public function label(): string
    {
        return match ($this) {
            self::CREATE => 'Create',
            self::UPDATE => 'Update',
            self::DELETE => 'Delete',
            self::APPROVE => 'Approve',
            self::REJECT => 'Reject',
            self::LOGIN => 'Login',
            self::LOGOUT => 'Logout',
            self::FORCE_LOGOUT => 'Forced Logout',
            self::DEACTIVATE => 'Deactivate User',
            self::REACTIVATE => 'Reactivate User',
            self::PASSWORD_RESET => 'Password Reset',
            self::VIEW => 'View Document',
            self::DOWNLOAD => 'Download Document',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::CREATE, self::APPROVE, self::REACTIVATE => 'bg-emerald-100 text-emerald-800',
            self::UPDATE, self::PASSWORD_RESET => 'bg-amber-100 text-amber-800',
            self::DELETE, self::REJECT, self::DEACTIVATE, self::FORCE_LOGOUT => 'bg-rose-100 text-rose-800',
            self::LOGIN, self::LOGOUT => 'bg-sky-100 text-sky-800',
            self::VIEW, self::DOWNLOAD => 'bg-slate-100 text-slate-800',
        };
    }
}
