<?php

namespace Database\Seeders;

use App\Models\Lga;
use Illuminate\Database\Seeder;

class LgaSeeder extends Seeder
{
    public function run(): void
    {
        $lgas = [
            // Kogi Central (5 LGAs)
            ['name' => 'Adavi', 'code' => 'KG-ADV', 'senatorial_district' => 'Kogi Central', 'headquarters' => 'Ogaminana', 'centroid_lat' => 7.6000000, 'centroid_lng' => 6.2000000],
            ['name' => 'Ajaokuta', 'code' => 'KG-AJK', 'senatorial_district' => 'Kogi Central', 'headquarters' => 'Ajaokuta', 'centroid_lat' => 7.5623000, 'centroid_lng' => 6.6549000],
            ['name' => 'Ogori/Magongo', 'code' => 'KG-OGM', 'senatorial_district' => 'Kogi Central', 'headquarters' => 'Akpafa', 'centroid_lat' => 7.4667000, 'centroid_lng' => 6.2167000],
            ['name' => 'Okehi', 'code' => 'KG-OKH', 'senatorial_district' => 'Kogi Central', 'headquarters' => 'Obangede', 'centroid_lat' => 7.5667000, 'centroid_lng' => 6.1833000],
            ['name' => 'Okene', 'code' => 'KG-OKN', 'senatorial_district' => 'Kogi Central', 'headquarters' => 'Okene', 'centroid_lat' => 7.5513000, 'centroid_lng' => 6.2359000],

            // Kogi East (9 LGAs)
            ['name' => 'Ankpa', 'code' => 'KG-ANK', 'senatorial_district' => 'Kogi East', 'headquarters' => 'Ankpa', 'centroid_lat' => 7.4025000, 'centroid_lng' => 7.6320000],
            ['name' => 'Bassa', 'code' => 'KG-BAS', 'senatorial_district' => 'Kogi East', 'headquarters' => 'Oguma', 'centroid_lat' => 7.8833000, 'centroid_lng' => 7.0500000],
            ['name' => 'Dekina', 'code' => 'KG-DEK', 'senatorial_district' => 'Kogi East', 'headquarters' => 'Dekina', 'centroid_lat' => 7.6897000, 'centroid_lng' => 7.0438000],
            ['name' => 'Ibaji', 'code' => 'KG-IBJ', 'senatorial_district' => 'Kogi East', 'headquarters' => 'Onyedega', 'centroid_lat' => 6.8667000, 'centroid_lng' => 6.8000000],
            ['name' => 'Idah', 'code' => 'KG-IDH', 'senatorial_district' => 'Kogi East', 'headquarters' => 'Idah', 'centroid_lat' => 7.1135000, 'centroid_lng' => 6.7387000],
            ['name' => 'Igalamela-Odolu', 'code' => 'KG-IGM', 'senatorial_district' => 'Kogi East', 'headquarters' => 'Ajaka', 'centroid_lat' => 7.1833000, 'centroid_lng' => 6.8333000],
            ['name' => 'Ofu', 'code' => 'KG-OFU', 'senatorial_district' => 'Kogi East', 'headquarters' => 'Ugwolawo', 'centroid_lat' => 7.3667000, 'centroid_lng' => 6.9167000],
            ['name' => 'Olamaboro', 'code' => 'KG-OLM', 'senatorial_district' => 'Kogi East', 'headquarters' => 'Okpo', 'centroid_lat' => 7.1333000, 'centroid_lng' => 7.5667000],
            ['name' => 'Omala', 'code' => 'KG-OML', 'senatorial_district' => 'Kogi East', 'headquarters' => 'Abejukolo', 'centroid_lat' => 7.8333000, 'centroid_lng' => 7.5167000],

            // Kogi West (7 LGAs)
            ['name' => 'Ijumu', 'code' => 'KG-IJM', 'senatorial_district' => 'Kogi West', 'headquarters' => 'Iyara', 'centroid_lat' => 7.8500000, 'centroid_lng' => 5.9667000],
            ['name' => 'Kabba/Bunu', 'code' => 'KG-KBB', 'senatorial_district' => 'Kogi West', 'headquarters' => 'Kabba', 'centroid_lat' => 7.8272000, 'centroid_lng' => 6.0750000],
            ['name' => 'Kogi', 'code' => 'KG-KOG', 'senatorial_district' => 'Kogi West', 'headquarters' => 'Koton-Karfe', 'centroid_lat' => 8.0912000, 'centroid_lng' => 6.7978000],
            ['name' => 'Lokoja', 'code' => 'KG-LOK', 'senatorial_district' => 'Kogi West', 'headquarters' => 'Lokoja', 'centroid_lat' => 7.8023000, 'centroid_lng' => 6.7333000],
            ['name' => 'Mopa-Muro', 'code' => 'KG-MPM', 'senatorial_district' => 'Kogi West', 'headquarters' => 'Mopa', 'centroid_lat' => 8.1000000, 'centroid_lng' => 5.9000000],
            ['name' => 'Yagba East', 'code' => 'KG-YGE', 'senatorial_district' => 'Kogi West', 'headquarters' => 'Isanlu', 'centroid_lat' => 8.2833000, 'centroid_lng' => 5.8167000],
            ['name' => 'Yagba West', 'code' => 'KG-YGW', 'senatorial_district' => 'Kogi West', 'headquarters' => 'Odo-Ere', 'centroid_lat' => 8.2500000, 'centroid_lng' => 5.5667000],
        ];

        foreach ($lgas as $lga) {
            Lga::updateOrCreate(['code' => $lga['code']], $lga + ['is_active' => true]);
        }
    }
}
