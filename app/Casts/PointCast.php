<?php

namespace App\Casts;

use App\Support\GeoPoint;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Custom Eloquent Cast for MySQL 8 native POINT SRID 4326 columns.
 *
 * @implements CastsAttributes<GeoPoint|null, GeoPoint|array<string, mixed>|string|null>
 */
class PointCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?GeoPoint
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof GeoPoint) {
            return $value;
        }

        if (is_string($value)) {
            if (strlen($value) === 25 && ! ctype_print($value)) {
                return GeoPoint::fromMySqlBinary($value);
            }

            return GeoPoint::fromWkt($value) ?? GeoPoint::fromLatLngString($value);
        }

        return null;
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($value === null) {
            return null;
        }

        $point = match (true) {
            $value instanceof GeoPoint => $value,
            is_array($value) && isset($value['latitude'], $value['longitude']) => new GeoPoint((float) $value['latitude'], (float) $value['longitude']),
            is_string($value) => GeoPoint::fromWkt($value) ?? GeoPoint::fromLatLngString($value),
            default => null,
        };

        if (! $point instanceof GeoPoint) {
            throw new InvalidArgumentException("Invalid GeoPoint value provided for attribute [{$key}].");
        }

        $wkt = $point->toWkt();
        $driver = $model->getConnection()->getDriverName();

        if ($driver === 'mysql' || $driver === 'mariadb') {
            return DB::raw("ST_GeomFromText('{$wkt}', 4326)");
        }

        return $wkt;
    }
}
