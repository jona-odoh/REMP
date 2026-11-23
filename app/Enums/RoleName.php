<?php

namespace App\Enums;

enum RoleName: string
{
    case SUPER_ADMIN = 'SUPER_ADMIN';
    case DIRECTOR = 'DIRECTOR';
    case DEPT_HEAD_CULTURE = 'DEPT_HEAD_CULTURE';
    case DEPT_HEAD_TOURISM = 'DEPT_HEAD_TOURISM';
    case DEPT_HEAD_HOTELS = 'DEPT_HEAD_HOTELS';
    case LICENSING_OFFICER = 'LICENSING_OFFICER';
    case DATA_ENTRY = 'DATA_ENTRY';
    case VIEWER = 'VIEWER';

    public function label(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => 'Super Administrator',
            self::DIRECTOR => 'Director / Commissioner',
            self::DEPT_HEAD_CULTURE => 'Head of Culture',
            self::DEPT_HEAD_TOURISM => 'Head of Tourism',
            self::DEPT_HEAD_HOTELS => 'Head of Hotels & Hospitality',
            self::LICENSING_OFFICER => 'Licensing Officer',
            self::DATA_ENTRY => 'Data Entry Officer',
            self::VIEWER => 'Read-Only Viewer',
        };
    }

    public function defaultDepartment(): Department
    {
        return match ($this) {
            self::SUPER_ADMIN, self::DIRECTOR => Department::ADMINISTRATION,
            self::DEPT_HEAD_CULTURE => Department::CULTURE,
            self::DEPT_HEAD_TOURISM => Department::TOURISM,
            self::DEPT_HEAD_HOTELS, self::LICENSING_OFFICER => Department::HOTELS_HOSPITALITY,
            self::DATA_ENTRY, self::VIEWER => Department::PLANNING_FINANCE,
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
