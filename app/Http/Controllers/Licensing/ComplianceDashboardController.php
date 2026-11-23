<?php

namespace App\Http\Controllers\Licensing;

use App\Enums\HotelLicenseStatus;
use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\Inspection;
use App\Models\Lga;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ComplianceDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Hotel::class);

        $filters = $request->only(['lga_id', 'establishment_type']);

        $baseQuery = Hotel::query()->filter($filters);

        $counts = [
            'total' => (clone $baseQuery)->count(),
            'licensed' => (clone $baseQuery)->where('license_status', HotelLicenseStatus::LICENSED->value)->count(),
            'unlicensed' => (clone $baseQuery)->where('license_status', HotelLicenseStatus::UNLICENSED->value)->count(),
            'expired' => (clone $baseQuery)->where('license_status', HotelLicenseStatus::EXPIRED->value)->count(),
            'pending' => (clone $baseQuery)->where('license_status', HotelLicenseStatus::PENDING->value)->count(),
        ];

        $overdueInspections = Inspection::query()
            ->with(['hotel.lga', 'assignedOfficer', 'licenseApplication'])
            ->overdue()
            ->when($filters['lga_id'] ?? null, fn ($q, $lgaId) => $q->whereHas('hotel', fn ($h) => $h->where('lga_id', $lgaId)))
            ->when($filters['establishment_type'] ?? null, fn ($q, $type) => $q->whereHas('hotel', fn ($h) => $h->where('establishment_type', $type)))
            ->orderBy('scheduled_date')
            ->get();

        $lgaBreakdown = Lga::active()
            ->withCount([
                'hotels as total_hotels',
                'hotels as licensed_hotels' => fn ($q) => $q->where('license_status', HotelLicenseStatus::LICENSED->value),
                'hotels as unlicensed_hotels' => fn ($q) => $q->where('license_status', HotelLicenseStatus::UNLICENSED->value),
                'hotels as expired_hotels' => fn ($q) => $q->where('license_status', HotelLicenseStatus::EXPIRED->value),
            ])
            ->get()
            ->filter(fn ($lga) => $lga->total_hotels > 0);

        return view('licensing.compliance.index', [
            'counts' => $counts,
            'overdueInspections' => $overdueInspections,
            'lgaBreakdown' => $lgaBreakdown,
            'lgas' => Lga::active()->get(),
            'establishmentTypes' => ['Hotel', 'Guest House', 'Motel', 'Resort', 'Lodge'],
            'filters' => $filters,
        ]);
    }
}
