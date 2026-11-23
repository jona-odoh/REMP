<?php

namespace App\Support;

use JsonSerializable;
use Stringable;

final readonly class GeoPoint implements JsonSerializable, Stringable
{
    public function __construct(
        public float $latitude,
        public float $longitude,
    ) {}

    public function toWkt(): string
    {
        return sprintf('POINT(%.7F %.7F)', $this->latitude, $this->longitude);
    }

    public static function fromWkt(string $wkt): ?self
    {
        if (preg_match('/POINT\s*\(\s*([+-]?\d+(?:\.\d+)?)\s+([+-]?\d+(?:\.\d+)?)\s*\)/i', $wkt, $matches)) {
            return new self((float) $matches[1], (float) $matches[2]);
        }

        return null;
    }

    public static function fromLatLngString(string $value): ?self
    {
        $parts = array_map('trim', explode(',', $value));
        if (count($parts) === 2 && is_numeric($parts[0]) && is_numeric($parts[1])) {
            return new self((float) $parts[0], (float) $parts[1]);
        }

        return null;
    }

    /**
     * Decode MySQL 8 internal 25-byte geometry binary (4-byte SRID + 21-byte WKB Point).
     */
    public static function fromMySqlBinary(string $binary): ?self
    {
        if (strlen($binary) !== 25) {
            return null;
        }

        $header = unpack('Vsrid/Corder', substr($binary, 0, 5));
        if ($header === false) {
            return null;
        }

        $format = $header['order'] === 1 ? 'Vtype/elat/elng' : 'Ntype/Elat/Elng';
        $data = unpack($format, substr($binary, 5));

        if ($data === false || (int) $data['type'] !== 1) {
            return null;
        }

        return new self((float) $data['lat'], (float) $data['lng']);
    }

    /**
     * @return array{latitude: float, longitude: float}
     */
    public function toArray(): array
    {
        return [
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
        ];
    }

    /**
     * @return array{latitude: float, longitude: float}
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function __toString(): string
    {
        return sprintf('%.7F, %.7F', $this->latitude, $this->longitude);
    }
}
