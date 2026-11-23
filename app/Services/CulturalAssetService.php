<?php

namespace App\Services;

use App\Models\CulturalAsset;
use App\Models\CulturalAssetVersion;
use App\Models\Lga;
use App\Models\User;
use App\Support\GeoPoint;
use Illuminate\Support\Facades\DB;

class CulturalAssetService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, User $actor): CulturalAsset
    {
        return DB::transaction(function () use ($data, $actor): CulturalAsset {
            [$point, $isApproximate] = $this->resolveLocation($data);

            $asset = CulturalAsset::create([
                'name' => trim((string) $data['name']),
                'category_id' => $data['category_id'],
                'lga_id' => $data['lga_id'],
                'location' => $point,
                'is_approximate_location' => $isApproximate,
                'ownership_status' => $data['ownership_status'],
                'condition_assessment' => $data['condition_assessment'],
                'visitor_capacity' => $data['visitor_capacity'] ?? null,
                'description' => $data['description'] ?? null,
                'current_version' => 1,
                'created_by' => $actor->getKey(),
                'updated_by' => $actor->getKey(),
            ]);

            CulturalAssetVersion::create([
                'cultural_asset_id' => $asset->id,
                'version_number' => 1,
                'snapshot' => $asset->toVersionSnapshot(),
                'change_summary' => $data['change_summary'] ?? 'Initial asset registration',
                'changed_by' => $actor->getKey(),
                'created_at' => now(),
            ]);

            return $asset;
        });
    }

    /**
     * Update an asset without overwriting history — appends a new numbered version snapshot.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(CulturalAsset $asset, array $data, User $actor): CulturalAsset
    {
        return DB::transaction(function () use ($asset, $data, $actor): CulturalAsset {
            [$point, $isApproximate] = $this->resolveLocation($data, $asset);
            $nextVersion = $asset->current_version + 1;

            $asset->update([
                'name' => trim((string) $data['name']),
                'category_id' => $data['category_id'],
                'lga_id' => $data['lga_id'],
                'location' => $point,
                'is_approximate_location' => $isApproximate,
                'ownership_status' => $data['ownership_status'],
                'condition_assessment' => $data['condition_assessment'],
                'visitor_capacity' => $data['visitor_capacity'] ?? null,
                'description' => $data['description'] ?? null,
                'current_version' => $nextVersion,
                'updated_by' => $actor->getKey(),
            ]);

            CulturalAssetVersion::create([
                'cultural_asset_id' => $asset->id,
                'version_number' => $nextVersion,
                'snapshot' => $asset->toVersionSnapshot(),
                'change_summary' => $data['change_summary'] ?? 'Asset record updated',
                'changed_by' => $actor->getKey(),
                'created_at' => now(),
            ]);

            return $asset->fresh(['category', 'lga', 'versions']);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{0: GeoPoint, 1: bool}
     */
    protected function resolveLocation(array $data, ?CulturalAsset $existing = null): array
    {
        if (isset($data['latitude'], $data['longitude']) && $data['latitude'] !== '' && $data['longitude'] !== '') {
            return [new GeoPoint((float) $data['latitude'], (float) $data['longitude']), false];
        }

        if ($existing && $existing->location instanceof GeoPoint) {
            return [$existing->location, $existing->is_approximate_location];
        }

        $lga = Lga::find($data['lga_id']);
        $lat = $lga?->centroid_lat !== null ? (float) $lga->centroid_lat : 7.8023000;
        $lng = $lga?->centroid_lng !== null ? (float) $lga->centroid_lng : 6.7333000;

        return [new GeoPoint($lat, $lng), true];
    }
}
