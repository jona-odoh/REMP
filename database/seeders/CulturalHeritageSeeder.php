<?php

namespace Database\Seeders;

use App\Enums\ConditionAssessment;
use App\Enums\OwnershipStatus;
use App\Enums\SafeguardingStatus;
use App\Models\AssetCategory;
use App\Models\CulturalAsset;
use App\Models\IntangibleHeritage;
use App\Models\Lga;
use App\Models\User;
use App\Services\CulturalAssetService;
use Illuminate\Database\Seeder;

class CulturalHeritageSeeder extends Seeder
{
    public function run(): void
    {
        $actor = User::where('email', 'admin')->first() ?? User::first();
        $service = app(CulturalAssetService::class);

        $lgaIds = Lga::pluck('id', 'name');
        $catIds = AssetCategory::pluck('id', 'slug');

        $assets = [
            [
                'name' => 'Lord Lugard House & Colonial Office',
                'category_id' => $catIds['historical-monument'],
                'lga_id' => $lgaIds['Lokoja'],
                'latitude' => 7.7974000,
                'longitude' => 6.7406000,
                'ownership_status' => OwnershipStatus::STATE_GOVERNMENT->value,
                'condition_assessment' => ConditionAssessment::GOOD->value,
                'visitor_capacity' => 300,
                'description' => 'Historic prefabricated residence and administrative headquarters of Lord Frederick Lugard, first Governor-General of amalgamated Nigeria (1914).',
            ],
            [
                'name' => 'Mount Patti',
                'category_id' => $catIds['natural-landmark'],
                'lga_id' => $lgaIds['Lokoja'],
                'latitude' => 7.8167000,
                'longitude' => 6.7250000,
                'ownership_status' => OwnershipStatus::STATE_GOVERNMENT->value,
                'condition_assessment' => ConditionAssessment::FAIR->value,
                'visitor_capacity' => 1200,
                'description' => 'Iconic 458-metre tabletop mountain overlooking Lokoja and the Confluence of Rivers Niger and Benue, where Flora Shaw coined the name "Nigeria".',
            ],
            [
                'name' => 'Confluence of Rivers Niger and Benue',
                'category_id' => $catIds['natural-landmark'],
                'lga_id' => $lgaIds['Lokoja'],
                'latitude' => 7.8000000,
                'longitude' => 6.7700000,
                'ownership_status' => OwnershipStatus::FEDERAL_GOVERNMENT->value,
                'condition_assessment' => ConditionAssessment::GOOD->value,
                'visitor_capacity' => 2000,
                'description' => 'Natural meeting point of West Africa\'s two largest rivers, forming a dramatic Y-shaped confluence visible from Mount Patti.',
            ],
            [
                'name' => 'Iron of Liberty (Spot of Slave Trade Abolition)',
                'category_id' => $catIds['historical-monument'],
                'lga_id' => $lgaIds['Lokoja'],
                'latitude' => 7.8019000,
                'longitude' => 6.7435000,
                'ownership_status' => OwnershipStatus::STATE_GOVERNMENT->value,
                'condition_assessment' => ConditionAssessment::FAIR->value,
                'visitor_capacity' => 150,
                'description' => 'Historic monument marking the spot in Lokoja where enslaved persons were officially declared free in 1860.',
            ],
            [
                'name' => 'Attah of Igala Royal Palace & Inikpi Statue',
                'category_id' => $catIds['royal-palace'],
                'lga_id' => $lgaIds['Idah'],
                'latitude' => 7.1135000,
                'longitude' => 6.7387000,
                'ownership_status' => OwnershipStatus::COMMUNITY_TRADITIONAL->value,
                'condition_assessment' => ConditionAssessment::EXCELLENT->value,
                'visitor_capacity' => 800,
                'description' => 'Ancient seat of the Igala Kingdom in Idah alongside the revered statue of Princess Inikpi, symbol of Igala sacrifice and heritage.',
            ],
            [
                'name' => 'Ofejiji Waterfall (Okura-Olafia)',
                'category_id' => $catIds['natural-landmark'],
                'lga_id' => $lgaIds['Dekina'],
                'latitude' => 7.6240000,
                'longitude' => 7.1850000,
                'ownership_status' => OwnershipStatus::COMMUNITY_TRADITIONAL->value,
                'condition_assessment' => ConditionAssessment::POOR->value,
                'visitor_capacity' => 400,
                'description' => 'Scenic seasonal eco-tourism waterfall located in Dekina LGA requiring access-road rehabilitation and visitor safety facilities.',
            ],
        ];

        foreach ($assets as $data) {
            $exists = CulturalAsset::where('name', $data['name'])
                ->where('lga_id', $data['lga_id'])
                ->exists();

            if (! $exists && $actor) {
                $service->create($data, $actor);
            }
        }

        $festivals = [
            [
                'name' => 'Ovia-Osese Festival',
                'lga_id' => $lgaIds['Ogori/Magongo'],
                'festival_season' => 'April (Annually)',
                'participating_communities' => 'Ogori, Magongo, and surrounding Okun/Ebira communities',
                'historical_description' => 'Centuries-old maidenhood initiation and cultural celebration of the Ogori people honouring purity, womanhood, traditional songs, body painting (Iburu), and communal unity.',
                'safeguarding_status' => SafeguardingStatus::VIABLE->value,
            ],
            [
                'name' => 'New Yam Festival (Ogidi Day)',
                'lga_id' => $lgaIds['Ijumu'],
                'festival_season' => 'June (Annually)',
                'participating_communities' => 'Ogidi, Iyara, Ayere, and Okunland communities in Ijumu LGA',
                'historical_description' => 'Annual harvest thanksgiving festival celebrating the first yam harvest with traditional masquerades, royal processions, cultural dances, and community development launches.',
                'safeguarding_status' => SafeguardingStatus::VIABLE->value,
            ],
            [
                'name' => 'Ekuechi Festival',
                'lga_id' => $lgaIds['Okene'],
                'festival_season' => 'November – December (Annually)',
                'participating_communities' => 'Okene, Adavi, Okehi, and Ajaokuta (Ebiraland)',
                'historical_description' => 'End-of-year nocturnal ancestral masquerade and musical festival of the Ebira people featuring Eku-rahu night masquerades, philosophical poetry, and renewal rites.',
                'safeguarding_status' => SafeguardingStatus::AT_RISK->value,
            ],
        ];

        foreach ($festivals as $festival) {
            IntangibleHeritage::updateOrCreate(
                ['name' => $festival['name'], 'lga_id' => $festival['lga_id']],
                $festival + ['created_by' => $actor?->id, 'updated_by' => $actor?->id]
            );
        }
    }
}
