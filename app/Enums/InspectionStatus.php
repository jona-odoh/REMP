<?php

namespace App\Enums;

enum InspectionStatus: string
{
    case SCHEDULED = 'SCHEDULED';
    case IN_PROGRESS = 'IN_PROGRESS';
    case COMPLETED = 'COMPLETED';
    case CANCELLED = 'CANCELLED';

    public function label(): string
    {
        return match ($this) {
            self::SCHEDULED => 'Scheduled',
            self::IN_PROGRESS => 'In Progress',
            self::COMPLETED => 'Completed',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::SCHEDULED => 'bg-sky-100 text-sky-800',
            self::IN_PROGRESS => 'bg-amber-100 text-amber-800',
            self::COMPLETED => 'bg-emerald-100 text-emerald-800',
            self::CANCELLED => 'bg-slate-200 text-slate-700',
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
