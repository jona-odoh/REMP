<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasSpatialLocation
{
    /**
     * Filter records within a radius (in km) using MySQL 8 ST_Distance_Sphere.
     */
    public function scopeWithinDistanceKm(Builder $query, float $latitude, float $longitude, float $radiusKm): Builder
    {
        $driver = $query->getConnection()->getDriverName();

        if ($driver === 'mysql') {
            $wkt = sprintf('POINT(%.7F %.7F)', $latitude, $longitude);
            $meters = $radiusKm * 1000;

            return $query->whereRaw(
                'ST_Distance_Sphere(location, ST_GeomFromText(?, 4326)) <= ?',
                [$wkt, $meters]
            );
        }

        return $query;
    }

    /**
     * Filter records inside a geographic bounding box using MySQL 8 ST_Contains.
     */
    public function scopeWithinBoundingBox(Builder $query, float $minLat, float $minLng, float $maxLat, float $maxLng): Builder
    {
        $driver = $query->getConnection()->getDriverName();

        if ($driver === 'mysql') {
            $polygon = sprintf(
                'POLYGON((%.7F %.7F, %.7F %.7F, %.7F %.7F, %.7F %.7F, %.7F %.7F))',
                $minLat, $minLng,
                $maxLat, $minLng,
                $maxLat, $maxLng,
                $minLat, $maxLng,
                $minLat, $minLng,
            );

            return $query->whereRaw(
                'ST_Contains(ST_GeomFromText(?, 4326), location)',
                [$polygon]
            );
        }

        return $query;
    }
}
