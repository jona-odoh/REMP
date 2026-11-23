<?php

namespace App\Http\Controllers\Culture;

use App\Enums\ConditionAssessment;
use App\Enums\OwnershipStatus;
use App\Exports\CulturalAssetsExport;
use App\Exports\CulturalAssetTemplateExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Culture\ImportCulturalAssetsRequest;
use App\Http\Requests\Culture\SaveCulturalAssetRequest;
use App\Imports\CulturalAssetsImport;
use App\Models\AssetCategory;
use App\Models\CulturalAsset;
use App\Models\Lga;
use App\Services\CulturalAssetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CulturalAssetController extends Controller
{
    public function __construct(
        private readonly CulturalAssetService $assetService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', CulturalAsset::class);

        $filters = $request->only(['search', 'category_id', 'lga_id', 'condition', 'ownership']);

        $assets = CulturalAsset::query()
            ->with(['category', 'lga'])
            ->withCount(['documents', 'versions'])
            ->filter($filters)
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('culture.assets.index', [
            'assets' => $assets,
            'categories' => AssetCategory::active()->get(),
            'lgas' => Lga::active()->get(),
            'conditions' => ConditionAssessment::cases(),
            'ownerships' => OwnershipStatus::cases(),
            'filters' => $filters,
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', CulturalAsset::class);

        return view('culture.assets.create', [
            'categories' => AssetCategory::active()->get(),
            'lgas' => Lga::active()->get(),
            'conditions' => ConditionAssessment::cases(),
            'ownerships' => OwnershipStatus::cases(),
        ]);
    }

    public function store(SaveCulturalAssetRequest $request): RedirectResponse
    {
        $asset = $this->assetService->create($request->validated(), $request->user());

        return redirect()
            ->route('culture.assets.show', $asset)
            ->with('status', "Cultural asset '{$asset->name}' registered (v1).");
    }

    public function show(CulturalAsset $culturalAsset): View
    {
        $this->authorize('view', $culturalAsset);

        $culturalAsset->load(['category', 'lga', 'creator', 'updater', 'versions.changedBy', 'documents.uploader']);

        return view('culture.assets.show', [
            'asset' => $culturalAsset,
        ]);
    }

    public function edit(CulturalAsset $culturalAsset): View
    {
        $this->authorize('update', $culturalAsset);

        return view('culture.assets.edit', [
            'asset' => $culturalAsset,
            'categories' => AssetCategory::active()->get(),
            'lgas' => Lga::active()->get(),
            'conditions' => ConditionAssessment::cases(),
            'ownerships' => OwnershipStatus::cases(),
        ]);
    }

    public function update(SaveCulturalAssetRequest $request, CulturalAsset $culturalAsset): RedirectResponse
    {
        $asset = $this->assetService->update($culturalAsset, $request->validated(), $request->user());

        return redirect()
            ->route('culture.assets.show', $asset)
            ->with('status', "Cultural asset updated — saved as revision v{$asset->current_version}.");
    }

    public function destroy(CulturalAsset $culturalAsset): RedirectResponse
    {
        $this->authorize('delete', $culturalAsset);

        $name = $culturalAsset->name;
        $culturalAsset->delete();

        return redirect()
            ->route('culture.assets.index')
            ->with('status', "Cultural asset '{$name}' deleted.");
    }

    public function export(Request $request): BinaryFileResponse
    {
        $this->authorize('viewAny', CulturalAsset::class);

        $format = $request->string('format')->lower()->toString() === 'csv' ? 'csv' : 'xlsx';
        $filters = $request->only(['search', 'category_id', 'lga_id', 'condition', 'ownership']);
        $filename = 'ksmct_cultural_assets_'.now()->format('Ymd_His').'.'.$format;

        return Excel::download(new CulturalAssetsExport($filters), $filename);
    }

    public function downloadTemplate(): BinaryFileResponse
    {
        $this->authorize('create', CulturalAsset::class);

        return Excel::download(new CulturalAssetTemplateExport, 'ksmct_cultural_assets_import_template.xlsx');
    }

    public function showImport(): View
    {
        $this->authorize('create', CulturalAsset::class);

        return view('culture.assets.import', [
            'ownerships' => OwnershipStatus::cases(),
            'conditions' => ConditionAssessment::cases(),
        ]);
    }

    public function import(ImportCulturalAssetsRequest $request): RedirectResponse
    {
        $import = new CulturalAssetsImport($this->assetService, $request->user());
        Excel::import($import, $request->file('file'));

        return redirect()
            ->route('culture.assets.import.form')
            ->with('status', "Bulk import finished: {$import->importedCount} asset(s) imported, ".count($import->rowErrors).' row(s) skipped due to validation errors.')
            ->with('import_errors', $import->rowErrors);
    }

    public function map(Request $request): View
    {
        $this->authorize('viewAny', CulturalAsset::class);

        $filters = $request->only(['category_id', 'lga_id', 'condition']);

        $assets = CulturalAsset::query()
            ->with(['category', 'lga'])
            ->filter($filters)
            ->get();

        $markers = $assets->map(fn (CulturalAsset $asset) => [
            'id' => $asset->id,
            'name' => $asset->name,
            'category' => $asset->category?->name ?? 'Uncategorised',
            'lga' => $asset->lga?->name ?? 'Unknown',
            'condition' => $asset->condition_assessment?->label() ?? '',
            'condition_code' => $asset->condition_assessment?->value ?? '',
            'latitude' => $asset->location?->latitude,
            'longitude' => $asset->location?->longitude,
            'is_approximate' => $asset->is_approximate_location,
            'url' => route('culture.assets.show', $asset),
        ])->filter(fn ($m) => $m['latitude'] !== null && $m['longitude'] !== null)->values();

        return view('culture.assets.map', [
            'markers' => $markers,
            'categories' => AssetCategory::active()->get(),
            'lgas' => Lga::active()->get(),
            'conditions' => ConditionAssessment::cases(),
            'filters' => $filters,
            'tileUrl' => config('services.map.tile_url', 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png'),
        ]);
    }
}
