<?php

namespace App\Enums;

enum ProjectStatus: string
{
    case PLANNED = 'planned';
    case ONGOING = 'ongoing';
    case COMPLETED = 'completed';
    case STALLED = 'stalled';

    public function label(): string
    {
        return match ($this) {
            self::PLANNED => 'Planned',
            self::ONGOING => 'Ongoing',
            self::COMPLETED => 'Completed',
            self::STALLED => 'Stalled',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::PLANNED => 'bg-sky-100 text-sky-800',
            self::ONGOING => 'bg-amber-100 text-amber-800',
            self::COMPLETED => 'bg-emerald-100 text-emerald-800',
            self::STALLED => 'bg-rose-100 text-rose-800',
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
