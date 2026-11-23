<?php

namespace Database\Seeders;

use App\Enums\HotelLicenseStatus;
use App\Enums\InspectionStatus;
use App\Enums\LicenseApplicationStatus;
use App\Models\Hotel;
use App\Models\Inspection;
use App\Models\Lga;
use App\Models\LicenseApplication;
use App\Models\LicenseFee;
use App\Models\LicenseType;
use App\Models\User;
use App\Support\GeoPoint;
use Illuminate\Database\Seeder;

class HospitalityDemoSeeder extends Seeder
{
    public function run(): void
    {
        $hotelsHead = User::where('email', 'hotels.head@ksmct.kogistate.gov.ng')->first() ?? User::first();
        $officer = User::where('email', 'licensing@ksmct.kogistate.gov.ng')->first() ?? $hotelsHead;

        $lgaIds = Lga::pluck('id', 'name');
        $hotelType = LicenseType::where('code', 'LIC-HTL')->first();
        $guestType = LicenseType::where('code', 'LIC-GST')->first();
        $resortType = LicenseType::where('code', 'LIC-RST')->first();

        if (! $hotelType || ! $guestType || ! $resortType) {
            return;
        }

        // 1. Licensed Resort (Lokoja)
        $confluence = Hotel::firstOrCreate(
            ['name' => 'Confluence Beach Hotel & Resort', 'lga_id' => $lgaIds['Lokoja']],
            [
                'address' => 'Plot 12 Ganaja-Ajaokuta Road, Lokoja',
                'establishment_type' => 'Resort',
                'capacity_rooms' => 110,
                'capacity_beds' => 160,
                'owner_name' => 'Kogi Hospitality Holdings Ltd',
                'owner_contact' => '+234 803 441 2000',
                'classification_rating' => '4-Star Hotel',
                'license_status' => HotelLicenseStatus::LICENSED,
                'location' => new GeoPoint(7.7812000, 6.7425000),
                'created_by' => $officer->id,
            ]
        );

        $app1 = LicenseApplication::firstOrCreate(
            ['application_number' => 'KSMCT-APP-2026-0001'],
            [
                'hotel_id' => $confluence->id,
                'license_type_id' => $resortType->id,
                'application_type' => 'NEW',
                'status' => LicenseApplicationStatus::APPROVED,
                'assigned_officer_id' => $officer->id,
                'submitted_at' => now()->subMonths(3),
                'reviewed_at' => now()->subMonths(3)->addDays(2),
                'decided_at' => now()->subMonths(3)->addDays(7),
                'decided_by' => $hotelsHead->id,
                'decision_reason' => 'All fire safety, hygiene, and facility standards satisfied during inspection.',
                'license_number' => 'KSMCT-LIC-2026-0001',
                'issued_at' => today()->subMonths(3),
                'expires_at' => today()->addMonths(9),
                'created_by' => $officer->id,
            ]
        );

        Inspection::firstOrCreate(
            ['hotel_id' => $confluence->id, 'license_application_id' => $app1->id],
            [
                'assigned_officer_id' => $officer->id,
                'scheduled_date' => today()->subMonths(3)->addDays(4),
                'completed_date' => today()->subMonths(3)->addDays(5),
                'status' => InspectionStatus::COMPLETED,
                'overall_passed' => true,
                'checklist_results' => [
                    ['section' => 'Fire & Life Safety', 'item_text' => 'Functional fire extinguishers installed on every floor', 'is_mandatory' => true, 'passed' => true],
                ],
                'notes' => 'Facility compliant across all mandatory criteria.',
                'created_by' => $officer->id,
            ]
        );

        LicenseFee::firstOrCreate(
            ['receipt_reference' => 'KG-REV-2026-00101'],
            [
                'hotel_id' => $confluence->id,
                'license_application_id' => $app1->id,
                'amount' => 350000.00,
                'paid_at' => today()->subMonths(3),
                'period' => '2026 Annual',
                'recorded_by' => $officer->id,
            ]
        );

        // 2. Licensed Hotel expiring in 25 days (for 30-day renewal reminder testing)
        $reverton = Hotel::firstOrCreate(
            ['name' => 'Reverton Hotel Lokoja', 'lga_id' => $lgaIds['Lokoja']],
            [
                'address' => 'GRA Phase II, Opposite Government House, Lokoja',
                'establishment_type' => 'Hotel',
                'capacity_rooms' => 65,
                'capacity_beds' => 90,
                'owner_name' => 'Reverton Hospitality Nigeria Ltd',
                'owner_contact' => '+234 805 220 3300',
                'classification_rating' => '3-Star Hotel',
                'license_status' => HotelLicenseStatus::LICENSED,
                'location' => new GeoPoint(7.8045000, 6.7311000),
                'created_by' => $officer->id,
            ]
        );

        LicenseApplication::firstOrCreate(
            ['application_number' => 'KSMCT-APP-2025-0042'],
            [
                'hotel_id' => $reverton->id,
                'license_type_id' => $hotelType->id,
                'application_type' => 'RENEWAL',
                'status' => LicenseApplicationStatus::APPROVED,
                'assigned_officer_id' => $officer->id,
                'submitted_at' => now()->subMonths(11),
                'decided_at' => now()->subMonths(11)->addDays(5),
                'decided_by' => $hotelsHead->id,
                'decision_reason' => 'Annual renewal approved.',
                'license_number' => 'KSMCT-LIC-2025-0042',
                'issued_at' => today()->subMonths(11),
                'expires_at' => today()->addDays(25),
                'created_by' => $officer->id,
            ]
        );

        LicenseFee::firstOrCreate(
            ['receipt_reference' => 'KG-REV-2026-00102'],
            [
                'hotel_id' => $reverton->id,
                'amount' => 200000.00,
                'paid_at' => today()->subMonth(),
                'period' => '2026 Annual',
                'recorded_by' => $officer->id,
            ]
        );

        // 3. Inspection Completed — Awaiting Final Approval (Okene)
        $okeneSuites = Hotel::firstOrCreate(
            ['name' => 'Ebira Heritage Suites', 'lga_id' => $lgaIds['Okene']],
            [
                'address' => '18 Okene-Lokoja Expressway, Okene',
                'establishment_type' => 'Hotel',
                'capacity_rooms' => 42,
                'capacity_beds' => 60,
                'owner_name' => 'Alhaji Usman Ohida',
                'owner_contact' => '+234 806 333 4411',
                'classification_rating' => '2-Star Hotel',
                'license_status' => HotelLicenseStatus::PENDING,
                'location' => new GeoPoint(7.5513000, 6.2359000),
                'created_by' => $officer->id,
            ]
        );

        $app3 = LicenseApplication::firstOrCreate(
            ['application_number' => 'KSMCT-APP-2026-0012'],
            [
                'hotel_id' => $okeneSuites->id,
                'license_type_id' => $hotelType->id,
                'application_type' => 'NEW',
                'status' => LicenseApplicationStatus::INSPECTION_COMPLETED,
                'assigned_officer_id' => $officer->id,
                'submitted_at' => now()->subDays(12),
                'reviewed_at' => now()->subDays(10),
                'created_by' => $officer->id,
            ]
        );

        Inspection::firstOrCreate(
            ['hotel_id' => $okeneSuites->id, 'license_application_id' => $app3->id],
            [
                'assigned_officer_id' => $officer->id,
                'scheduled_date' => today()->subDays(5),
                'completed_date' => today()->subDays(4),
                'status' => InspectionStatus::COMPLETED,
                'overall_passed' => true,
                'notes' => 'Passed inspection; recommended for 2-Star license approval.',
                'created_by' => $officer->id,
            ]
        );

        // 4. Inspection Scheduled & OVERDUE (Idah)
        $idahLodge = Hotel::firstOrCreate(
            ['name' => 'Idah Royal Confluence Lodge', 'lga_id' => $lgaIds['Idah']],
            [
                'address' => '5 Attah Palace Road, Idah',
                'establishment_type' => 'Lodge',
                'capacity_rooms' => 28,
                'capacity_beds' => 40,
                'owner_name' => 'Chief Matthew Attah',
                'owner_contact' => '+234 809 555 1122',
                'classification_rating' => '1-Star Hotel',
                'license_status' => HotelLicenseStatus::PENDING,
                'location' => new GeoPoint(7.1135000, 6.7387000),
                'created_by' => $officer->id,
            ]
        );

        $app4 = LicenseApplication::firstOrCreate(
            ['application_number' => 'KSMCT-APP-2026-0015'],
            [
                'hotel_id' => $idahLodge->id,
                'license_type_id' => $hotelType->id,
                'application_type' => 'NEW',
                'status' => LicenseApplicationStatus::INSPECTION_SCHEDULED,
                'assigned_officer_id' => $officer->id,
                'submitted_at' => now()->subDays(18),
                'reviewed_at' => now()->subDays(15),
                'created_by' => $officer->id,
            ]
        );

        Inspection::firstOrCreate(
            ['hotel_id' => $idahLodge->id, 'license_application_id' => $app4->id],
            [
                'assigned_officer_id' => $officer->id,
                'scheduled_date' => today()->subDays(4), // Overdue!
                'status' => InspectionStatus::SCHEDULED,
                'notes' => 'Awaiting field inspection team visit to Idah.',
                'created_by' => $officer->id,
            ]
        );

        // 5. Expired License (Kabba/Bunu)
        $kabbaGuest = Hotel::firstOrCreate(
            ['name' => 'Kabba Grand Guest House', 'lga_id' => $lgaIds['Kabba/Bunu']],
            [
                'address' => '22 College Road, Kabba',
                'establishment_type' => 'Guest House',
                'capacity_rooms' => 18,
                'capacity_beds' => 24,
                'owner_name' => 'Mrs. Folake Ojo',
                'owner_contact' => '+234 803 777 8899',
                'classification_rating' => 'Standard Guest House / Motel',
                'license_status' => HotelLicenseStatus::EXPIRED,
                'location' => new GeoPoint(7.8272000, 6.0750000),
                'created_by' => $officer->id,
            ]
        );

        LicenseApplication::firstOrCreate(
            ['application_number' => 'KSMCT-APP-2025-0008'],
            [
                'hotel_id' => $kabbaGuest->id,
                'license_type_id' => $guestType->id,
                'application_type' => 'NEW',
                'status' => LicenseApplicationStatus::EXPIRED,
                'assigned_officer_id' => $officer->id,
                'license_number' => 'KSMCT-LIC-2025-0008',
                'issued_at' => today()->subMonths(14),
                'expires_at' => today()->subMonths(2),
                'created_by' => $officer->id,
            ]
        );

        // 6. Unlicensed Establishment (Lokoja — > 20 rooms, great for Phase 5 Ad-Hoc Query example!)
        Hotel::firstOrCreate(
            ['name' => 'Ganaja Executive Motel', 'lga_id' => $lgaIds['Lokoja']],
            [
                'address' => 'Km 4 Ganaja Village, Lokoja',
                'establishment_type' => 'Motel',
                'capacity_rooms' => 32,
                'capacity_beds' => 45,
                'owner_name' => 'Mr. Peter Ogwu',
                'owner_contact' => '+234 812 900 1234',
                'classification_rating' => 'Standard Guest House / Motel',
                'license_status' => HotelLicenseStatus::UNLICENSED,
                'location' => new GeoPoint(7.7650000, 6.7480000),
                'created_by' => $officer->id,
            ]
        );
    }
}
