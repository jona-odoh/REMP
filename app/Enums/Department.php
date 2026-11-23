<?php

namespace App\Enums;

enum Department: string
{
    case ADMINISTRATION = 'ADMINISTRATION';
    case CULTURE = 'CULTURE';
    case TOURISM = 'TOURISM';
    case HOTELS_HOSPITALITY = 'HOTELS_HOSPITALITY';
    case PLANNING_FINANCE = 'PLANNING_FINANCE';

    public function label(): string
    {
        return match ($this) {
            self::ADMINISTRATION => 'Administration & Executive',
            self::CULTURE => 'Department of Culture',
            self::TOURISM => 'Department of Tourism',
            self::HOTELS_HOSPITALITY => 'Department of Hotels & Hospitality',
            self::PLANNING_FINANCE => 'Planning, Research & Finance',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }
}
