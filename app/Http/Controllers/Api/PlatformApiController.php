<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Community;
use App\Models\Lga;
use App\Models\Report;
use App\Models\Ward;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlatformApiController extends Controller
{
    public function lgas(): JsonResponse
    {
        return response()->json(Lga::withCount(['wards', 'communities'])->orderBy('name')->get());
    }

    public function wardsByLga(Lga $lga): JsonResponse
    {
        return response()->json($lga->wards()->orderBy('name')->get());
    }

    public function communitiesByWard(Ward $ward): JsonResponse
    {
        return response()->json($ward->communities()->orderBy('name')->get());
    }

    public function mapCommunities(Request $request): JsonResponse
    {
        $query = Community::with([
            'ward.lga',
            'latestEnergyDemand',
            'latestRecommendation.costEstimate',
            'priorityRanking',
        ]);

        if ($request->filled('lga_id')) {
            $query->whereHas('ward', fn ($q) => $q->where('lga_id', $request->query('lga_id')));
        }

        if ($request->filled('status')) {
            $query->where('electricity_status_enum', $request->query('status'));
        }

        return response()->json($query->get());
    }

    public function trackReport(string $referenceNumber): JsonResponse
    {
        $report = Report::with(['lga', 'ward', 'community'])
            ->where('reference_number', strtoupper($referenceNumber))
            ->firstOrFail();

        return response()->json($report);
    }
}
