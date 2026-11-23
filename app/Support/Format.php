<?php

namespace App\Support;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

class Format
{
    public static function date(CarbonInterface|string|null $date): string
    {
        if ($date === null || $date === '') {
            return '—';
        }

        $carbon = $date instanceof CarbonInterface ? $date : Carbon::parse($date);

        return $carbon->timezone(config('app.timezone', 'Africa/Lagos'))->format('d/m/Y');
    }

    public static function datetime(CarbonInterface|string|null $date): string
    {
        if ($date === null || $date === '') {
            return '—';
        }

        $carbon = $date instanceof CarbonInterface ? $date : Carbon::parse($date);

        return $carbon->timezone(config('app.timezone', 'Africa/Lagos'))->format('d/m/Y H:i');
    }

    public static function naira(float|int|string|null $amount): string
    {
        if ($amount === null || $amount === '') {
            return '₦0.00';
        }

        return '₦'.number_format((float) $amount, 2);
    }
}
