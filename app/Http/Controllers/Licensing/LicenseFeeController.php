<?php

namespace App\Http\Controllers\Licensing;

use App\Exports\LicenseFeesExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Licensing\StoreLicenseFeeRequest;
use App\Models\Hotel;
use App\Models\Lga;
use App\Models\LicenseFee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class LicenseFeeController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', LicenseFee::class);

        $lgaId = $request->integer('lga_id') ?: null;
        $month = $request->filled('month') ? $request->string('month')->toString() : null;

        $feesQuery = LicenseFee::query()
            ->with(['hotel.lga', 'recordedBy'])
            ->when($lgaId, fn ($q) => $q->whereHas('hotel', fn ($h) => $h->where('lga_id', $lgaId)))
            ->when($month, fn ($q) => $q->where('paid_at', 'like', $month.'%'));

        $totalRevenue = (clone $feesQuery)->sum('amount');
        $fees = (clone $feesQuery)->latest('paid_at')->paginate(20)->withQueryString();

        $allForSummary = (clone $feesQuery)->get();

        $revenueByMonth = $allForSummary
            ->groupBy(fn (LicenseFee $f) => $f->paid_at?->format('Y-m') ?? 'Unknown')
            ->map(fn ($group) => $group->sum('amount'))
            ->sortKeysDesc();

        $revenueByLga = $allForSummary
            ->groupBy(fn (LicenseFee $f) => $f->hotel?->lga?->name ?? 'Unknown')
            ->map(fn ($group) => $group->sum('amount'))
            ->sortDesc();

        return view('licensing.fees.index', [
            'fees' => $fees,
            'totalRevenue' => $totalRevenue,
            'revenueByMonth' => $revenueByMonth,
            'revenueByLga' => $revenueByLga,
            'hotels' => Hotel::with('lga')->orderBy('name')->get(),
            'lgas' => Lga::active()->get(),
            'filters' => ['lga_id' => $lgaId, 'month' => $month],
        ]);
    }

    public function store(StoreLicenseFeeRequest $request): RedirectResponse
    {
        $fee = LicenseFee::create($request->validated() + [
            'recorded_by' => $request->user()->getKey(),
        ]);

        return redirect()
            ->route('licensing.fees.index')
            ->with('status', "Payment receipt {$fee->receipt_reference} recorded.");
    }

    public function export(Request $request): BinaryFileResponse
    {
        $this->authorize('viewAny', LicenseFee::class);

        $lgaId = $request->integer('lga_id') ?: null;
        $month = $request->filled('month') ? $request->string('month')->toString() : null;

        return Excel::download(
            new LicenseFeesExport($lgaId, $month),
            'ksmct_license_revenue_'.now()->format('Ymd_His').'.xlsx'
        );
    }

    public function destroy(LicenseFee $fee): RedirectResponse
    {
        $this->authorize('delete', $fee);

        $ref = $fee->receipt_reference;
        $fee->delete();

        return redirect()->route('licensing.fees.index')->with('status', "Fee receipt {$ref} removed.");
    }
}
