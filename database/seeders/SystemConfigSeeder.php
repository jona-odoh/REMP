<?php

namespace Database\Seeders;

use App\Models\AssetCategory;
use App\Models\FeeSchedule;
use App\Models\InspectionChecklistTemplate;
use App\Models\LicenseType;
use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class SystemConfigSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Historical Monument & Colonial Heritage', 'slug' => 'historical-monument', 'description' => 'Colonial relics, memorials, and historic buildings (e.g., Lord Lugard House, Iron of Liberty).', 'sort_order' => 1],
            ['name' => 'Natural Landmark & Eco-Tourism Site', 'slug' => 'natural-landmark', 'description' => 'Hills, river confluences, waterfalls, caves, and forest reserves (e.g., Mount Patti, River Niger-Benue Confluence).', 'sort_order' => 2],
            ['name' => 'Royal Palace & Traditional Institution', 'slug' => 'royal-palace', 'description' => 'Historic palaces and royal heritage compounds across Kogi State.', 'sort_order' => 3],
            ['name' => 'Museum & Cultural Centre', 'slug' => 'museum-cultural-centre', 'description' => 'Museums, galleries, craft centres, and cultural theatres.', 'sort_order' => 4],
            ['name' => 'Archaeological & Sacred Site', 'slug' => 'archaeological-sacred-site', 'description' => 'Ancient shrines, rock art, and archaeological excavation sites.', 'sort_order' => 5],
            ['name' => 'Recreational Park & Resort', 'slug' => 'recreational-park', 'description' => 'Public parks, lakeside resorts, and leisure attractions.', 'sort_order' => 6],
        ];

        foreach ($categories as $category) {
            AssetCategory::updateOrCreate(['slug' => $category['slug']], $category + ['is_active' => true]);
        }

        $licenseTypes = [
            ['name' => 'Hotel Operational License', 'code' => 'LIC-HTL', 'description' => 'Annual operational license for graded hotels (1-Star to 5-Star).', 'validity_months' => 12],
            ['name' => 'Guest House & Lodge Permit', 'code' => 'LIC-GST', 'description' => 'Annual registration and operational permit for guest houses, motels, and lodges.', 'validity_months' => 12],
            ['name' => 'Resort & Recreational Facility License', 'code' => 'LIC-RST', 'description' => 'Annual license for tourism resorts, event resorts, and eco-lodges.', 'validity_months' => 12],
            ['name' => 'Restaurant & Lounge Hospitality Permit', 'code' => 'LIC-RNG', 'description' => 'Hospitality classification permit for tourist-grade restaurants and lounges.', 'validity_months' => 12],
        ];

        foreach ($licenseTypes as $type) {
            LicenseType::updateOrCreate(['code' => $type['code']], $type + ['is_active' => true]);
        }

        $hotelLicense = LicenseType::where('code', 'LIC-HTL')->first();
        $guestLicense = LicenseType::where('code', 'LIC-GST')->first();
        $resortLicense = LicenseType::where('code', 'LIC-RST')->first();

        $fees = [
            ['license_type_id' => $hotelLicense->id, 'classification_rating' => '5-Star Hotel', 'amount' => 500000.00],
            ['license_type_id' => $hotelLicense->id, 'classification_rating' => '4-Star Hotel', 'amount' => 350000.00],
            ['license_type_id' => $hotelLicense->id, 'classification_rating' => '3-Star Hotel', 'amount' => 200000.00],
            ['license_type_id' => $hotelLicense->id, 'classification_rating' => '2-Star Hotel', 'amount' => 120000.00],
            ['license_type_id' => $hotelLicense->id, 'classification_rating' => '1-Star Hotel', 'amount' => 75000.00],
            ['license_type_id' => $guestLicense->id, 'classification_rating' => 'Standard Guest House / Motel', 'amount' => 50000.00],
            ['license_type_id' => $resortLicense->id, 'classification_rating' => 'Tourism Resort', 'amount' => 300000.00],
        ];

        foreach ($fees as $fee) {
            FeeSchedule::updateOrCreate(
                [
                    'license_type_id' => $fee['license_type_id'],
                    'classification_rating' => $fee['classification_rating'],
                ],
                [
                    'amount' => $fee['amount'],
                    'currency' => 'NGN',
                    'effective_from' => now()->startOfYear()->toDateString(),
                    'effective_to' => null,
                    'is_active' => true,
                ]
            );
        }

        $checklists = [
            ['license_type_id' => null, 'section' => 'Fire & Life Safety', 'item_text' => 'Functional fire extinguishers installed on every floor with valid inspection tags', 'is_mandatory' => true, 'sort_order' => 1],
            ['license_type_id' => null, 'section' => 'Fire & Life Safety', 'item_text' => 'Clearly marked emergency exits and unobstructed evacuation routes', 'is_mandatory' => true, 'sort_order' => 2],
            ['license_type_id' => null, 'section' => 'Sanitation & Hygiene', 'item_text' => 'Potable water supply, functional plumbing, and hygienic waste disposal', 'is_mandatory' => true, 'sort_order' => 3],
            ['license_type_id' => null, 'section' => 'Sanitation & Hygiene', 'item_text' => 'Kitchen and food-handling areas meet state public health standards', 'is_mandatory' => true, 'sort_order' => 4],
            ['license_type_id' => null, 'section' => 'Security & Guest Registration', 'item_text' => '24-hour security personnel and maintained guest register/ID verification', 'is_mandatory' => true, 'sort_order' => 5],
            ['license_type_id' => $hotelLicense->id, 'section' => 'Room & Facility Standards', 'item_text' => 'Adequate ventilation/air-conditioning, clean bedding, and backup power supply', 'is_mandatory' => true, 'sort_order' => 6],
            ['license_type_id' => $hotelLicense->id, 'section' => 'Compliance & Documentation', 'item_text' => 'Valid CAC registration, tax clearance certificate, and prior license displayed', 'is_mandatory' => true, 'sort_order' => 7],
        ];

        foreach ($checklists as $item) {
            InspectionChecklistTemplate::updateOrCreate(
                ['section' => $item['section'], 'item_text' => $item['item_text']],
                $item + ['is_active' => true]
            );
        }

        $settings = [
            ['key' => 'budget_variance_threshold_percent', 'value' => ['val' => 15], 'description' => 'Percentage threshold above which project budget variance is flagged on dashboards.'],
            ['key' => 'license_expiry_reminder_days', 'value' => ['val' => [90, 60, 30]], 'description' => 'Days prior to hotel license expiration to trigger in-app renewal reminders.'],
        ];

        foreach ($settings as $setting) {
            SystemSetting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
