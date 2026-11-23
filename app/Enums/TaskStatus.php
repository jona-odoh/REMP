<?php

namespace App\Enums;

enum TaskStatus: string
{
    case PENDING = 'PENDING';
    case IN_PROGRESS = 'IN_PROGRESS';
    case COMPLETED = 'COMPLETED';
    case CANCELLED = 'CANCELLED';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::IN_PROGRESS => 'In Progress',
            self::COMPLETED => 'Completed',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::PENDING => 'bg-slate-100 text-slate-700',
            self::IN_PROGRESS => 'bg-amber-100 text-amber-800',
            self::COMPLETED => 'bg-emerald-100 text-emerald-800',
            self::CANCELLED => 'bg-rose-100 text-rose-800',
        };
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
