<?php

namespace App\Enums;

enum SafeguardingStatus: string
{
    case VIABLE = 'VIABLE';
    case AT_RISK = 'AT_RISK';
    case URGENT_SAFEGUARDING = 'URGENT_SAFEGUARDING';
    case DOCUMENTED = 'DOCUMENTED';

    public function label(): string
    {
        return match ($this) {
            self::VIABLE => 'Viable & Actively Practised',
            self::AT_RISK => 'At Risk of Decline',
            self::URGENT_SAFEGUARDING => 'Needs Urgent Safeguarding',
            self::DOCUMENTED => 'Documented & Archived',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::VIABLE => 'bg-emerald-100 text-emerald-800',
            self::AT_RISK => 'bg-amber-100 text-amber-800',
            self::URGENT_SAFEGUARDING => 'bg-rose-100 text-rose-800',
            self::DOCUMENTED => 'bg-sky-100 text-sky-800',
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
