<?php

namespace App\Enums;

enum HotelLicenseStatus: string
{
    case UNLICENSED = 'UNLICENSED';
    case PENDING = 'PENDING';
    case LICENSED = 'LICENSED';
    case EXPIRED = 'EXPIRED';
    case SUSPENDED = 'SUSPENDED';

    public function label(): string
    {
        return match ($this) {
            self::UNLICENSED => 'Unlicensed',
            self::PENDING => 'Pending Application',
            self::LICENSED => 'Licensed',
            self::EXPIRED => 'Expired',
            self::SUSPENDED => 'Suspended',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::LICENSED => 'bg-emerald-100 text-emerald-800',
            self::PENDING => 'bg-amber-100 text-amber-800',
            self::EXPIRED => 'bg-rose-100 text-rose-800',
            self::SUSPENDED => 'bg-purple-100 text-purple-800',
            self::UNLICENSED => 'bg-slate-200 text-slate-800',
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
