<?php

namespace App\Enums;

enum ConditionAssessment: string
{
    case EXCELLENT = 'EXCELLENT';
    case GOOD = 'GOOD';
    case FAIR = 'FAIR';
    case POOR = 'POOR';
    case CRITICAL = 'CRITICAL';

    public function label(): string
    {
        return match ($this) {
            self::EXCELLENT => 'Excellent',
            self::GOOD => 'Good',
            self::FAIR => 'Fair',
            self::POOR => 'Poor — Needs Maintenance',
            self::CRITICAL => 'Critical — Urgent Conservation',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::EXCELLENT => 'bg-emerald-100 text-emerald-800',
            self::GOOD => 'bg-teal-100 text-teal-800',
            self::FAIR => 'bg-amber-100 text-amber-800',
            self::POOR => 'bg-orange-100 text-orange-800',
            self::CRITICAL => 'bg-rose-100 text-rose-800',
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
