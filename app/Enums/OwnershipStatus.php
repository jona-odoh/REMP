<?php

namespace App\Enums;

enum OwnershipStatus: string
{
    case STATE_GOVERNMENT = 'STATE_GOVERNMENT';
    case FEDERAL_GOVERNMENT = 'FEDERAL_GOVERNMENT';
    case COMMUNITY_TRADITIONAL = 'COMMUNITY_TRADITIONAL';
    case PRIVATE = 'PRIVATE';
    case PUBLIC_PRIVATE_PARTNERSHIP = 'PUBLIC_PRIVATE_PARTNERSHIP';

    public function label(): string
    {
        return match ($this) {
            self::STATE_GOVERNMENT => 'Kogi State Government',
            self::FEDERAL_GOVERNMENT => 'Federal Government',
            self::COMMUNITY_TRADITIONAL => 'Community / Traditional',
            self::PRIVATE => 'Private Ownership',
            self::PUBLIC_PRIVATE_PARTNERSHIP => 'Public-Private Partnership',
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
