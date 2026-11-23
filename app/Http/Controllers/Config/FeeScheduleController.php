<?php

namespace App\Http\Controllers\Config;

use App\Http\Controllers\Controller;
use App\Http\Requests\Config\SaveFeeScheduleRequest;
use App\Models\FeeSchedule;
use App\Models\LicenseType;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FeeScheduleController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', FeeSchedule::class);

        $feeSchedules = FeeSchedule::with('licenseType')->orderBy('license_type_id')->orderByDesc('amount')->paginate(20);
        $licenseTypes = LicenseType::active()->get();

        return view('config.fee-schedules.index', compact('feeSchedules', 'licenseTypes'));
    }

    public function store(SaveFeeScheduleRequest $request): RedirectResponse
    {
        FeeSchedule::create($request->validated() + [
            'currency' => 'NGN',
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('config.fee-schedules.index')->with('status', 'Fee schedule added.');
    }

    public function update(SaveFeeScheduleRequest $request, FeeSchedule $feeSchedule): RedirectResponse
    {
        $feeSchedule->update($request->validated() + [
            'currency' => 'NGN',
            'is_active' => $request->boolean('is_active', false),
        ]);

        return redirect()->route('config.fee-schedules.index')->with('status', 'Fee schedule updated.');
    }

    public function destroy(FeeSchedule $feeSchedule): RedirectResponse
    {
        $this->authorize('delete', $feeSchedule);

        $feeSchedule->delete();

        return redirect()->route('config.fee-schedules.index')->with('status', 'Fee schedule deleted.');
    }
}
