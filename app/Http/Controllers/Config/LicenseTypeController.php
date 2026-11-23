<?php

namespace App\Http\Controllers\Config;

use App\Http\Controllers\Controller;
use App\Http\Requests\Config\SaveLicenseTypeRequest;
use App\Models\LicenseType;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LicenseTypeController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', LicenseType::class);

        $licenseTypes = LicenseType::withCount(['feeSchedules', 'checklistTemplates'])->orderBy('name')->paginate(20);

        return view('config.license-types.index', compact('licenseTypes'));
    }

    public function store(SaveLicenseTypeRequest $request): RedirectResponse
    {
        LicenseType::create($request->validated() + ['is_active' => $request->boolean('is_active', true)]);

        return redirect()->route('config.license-types.index')->with('status', 'License type created.');
    }

    public function update(SaveLicenseTypeRequest $request, LicenseType $licenseType): RedirectResponse
    {
        $licenseType->update($request->validated() + ['is_active' => $request->boolean('is_active', false)]);

        return redirect()->route('config.license-types.index')->with('status', 'License type updated.');
    }

    public function destroy(LicenseType $licenseType): RedirectResponse
    {
        $this->authorize('delete', $licenseType);

        if ($licenseType->feeSchedules()->exists()) {
            return redirect()->route('config.license-types.index')->withErrors(['license_type' => 'Cannot delete a license type that has fee schedules attached.']);
        }

        $licenseType->delete();

        return redirect()->route('config.license-types.index')->with('status', 'License type deleted.');
    }
}
