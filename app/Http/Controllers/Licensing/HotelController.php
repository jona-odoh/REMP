<?php

namespace App\Http\Controllers\Licensing;

use App\Enums\HotelLicenseStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Licensing\SaveHotelRequest;
use App\Models\FeeSchedule;
use App\Models\Hotel;
use App\Models\Lga;
use App\Support\GeoPoint;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HotelController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Hotel::class);

        $filters = $request->only(['search', 'lga_id', 'license_status', 'establishment_type']);

        $hotels = Hotel::query()
            ->with('lga')
            ->withCount(['licenseApplications', 'inspections'])
            ->filter($filters)
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('licensing.hotels.index', [
            'hotels' => $hotels,
            'lgas' => Lga::active()->get(),
            'statuses' => HotelLicenseStatus::cases(),
            'establishmentTypes' => ['Hotel', 'Guest House', 'Motel', 'Resort', 'Lodge'],
            'filters' => $filters,
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Hotel::class);

        return view('licensing.hotels.create', [
            'lgas' => Lga::active()->get(),
            'establishmentTypes' => ['Hotel', 'Guest House', 'Motel', 'Resort', 'Lodge'],
            'classifications' => FeeSchedule::active()->distinct()->orderBy('classification_rating')->pluck('classification_rating'),
        ]);
    }

    public function store(SaveHotelRequest $request): RedirectResponse
    {
        $data = $request->validated();
        [$point, $isApprox] = $this->resolveLocation($data);

        $hotel = Hotel::create([
            'name' => trim((string) $data['name']),
            'address' => $data['address'],
            'lga_id' => $data['lga_id'],
            'establishment_type' => $data['establishment_type'],
            'capacity_rooms' => $data['capacity_rooms'],
            'capacity_beds' => $data['capacity_beds'],
            'owner_name' => $data['owner_name'],
            'owner_contact' => $data['owner_contact'],
            'classification_rating' => $data['classification_rating'],
            'license_status' => HotelLicenseStatus::UNLICENSED,
            'location' => $point,
            'is_approximate_location' => $isApprox,
            'created_by' => $request->user()->getKey(),
            'updated_by' => $request->user()->getKey(),
        ]);

        return redirect()->route('licensing.hotels.show', $hotel)->with('status', "Establishment '{$hotel->name}' registered.");
    }

    public function show(Hotel $hotel): View
    {
        $this->authorize('view', $hotel);

        $hotel->load([
            'lga',
            'licenseApplications.licenseType',
            'inspections.assignedOfficer',
            'licenseFees.recordedBy',
            'documents.uploader',
        ]);

        return view('licensing.hotels.show', compact('hotel'));
    }

    public function edit(Hotel $hotel): View
    {
        $this->authorize('update', $hotel);

        return view('licensing.hotels.edit', [
            'hotel' => $hotel,
            'lgas' => Lga::active()->get(),
            'establishmentTypes' => ['Hotel', 'Guest House', 'Motel', 'Resort', 'Lodge'],
            'classifications' => FeeSchedule::active()->distinct()->orderBy('classification_rating')->pluck('classification_rating'),
        ]);
    }

    public function update(SaveHotelRequest $request, Hotel $hotel): RedirectResponse
    {
        $data = $request->validated();
        [$point, $isApprox] = $this->resolveLocation($data, $hotel);

        $hotel->update([
            'name' => trim((string) $data['name']),
            'address' => $data['address'],
            'lga_id' => $data['lga_id'],
            'establishment_type' => $data['establishment_type'],
            'capacity_rooms' => $data['capacity_rooms'],
            'capacity_beds' => $data['capacity_beds'],
            'owner_name' => $data['owner_name'],
            'owner_contact' => $data['owner_contact'],
            'classification_rating' => $data['classification_rating'],
            'location' => $point,
            'is_approximate_location' => $isApprox,
            'updated_by' => $request->user()->getKey(),
        ]);

        return redirect()->route('licensing.hotels.show', $hotel)->with('status', "Establishment '{$hotel->name}' updated.");
    }

    public function destroy(Hotel $hotel): RedirectResponse
    {
        $this->authorize('delete', $hotel);

        $name = $hotel->name;
        $hotel->delete();

        return redirect()->route('licensing.hotels.index')->with('status', "Establishment '{$name}' deleted.");
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{0: GeoPoint, 1: bool}
     */
    protected function resolveLocation(array $data, ?Hotel $existing = null): array
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
