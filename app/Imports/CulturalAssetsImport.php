<?php

namespace App\Imports;

use App\Enums\ConditionAssessment;
use App\Enums\OwnershipStatus;
use App\Models\AssetCategory;
use App\Models\CulturalAsset;
use App\Models\Lga;
use App\Models\User;
use App\Services\CulturalAssetService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Row;

class CulturalAssetsImport implements OnEachRow, WithChunkReading, WithHeadingRow
{
    public int $importedCount = 0;

    /**
     * @var array<int, array{row: int, name: string, errors: array<int, string>}>
     */
    public array $rowErrors = [];

    /** @var array<string, int> */
    private array $lgaMap = [];

    /** @var array<string, int> */
    private array $categoryMap = [];

    public function __construct(
        private readonly CulturalAssetService $assetService,
        private readonly User $actor,
    ) {
        $this->lgaMap = Lga::all()->mapWithKeys(fn (Lga $lga) => [strtolower($lga->name) => $lga->id])->all();
        $this->categoryMap = AssetCategory::all()->mapWithKeys(fn (AssetCategory $cat) => [strtolower($cat->name) => $cat->id])->all();
    }

    public function onRow(Row $row): void
    {
        $rowIndex = $row->getIndex();
        $data = array_map(fn ($v) => is_string($v) ? trim($v) : $v, $row->toArray());

        $name = (string) ($data['name'] ?? '');
        $lgaName = strtolower((string) ($data['lga'] ?? ''));
        $categoryName = strtolower((string) ($data['category'] ?? ''));

        $lgaId = $this->lgaMap[$lgaName] ?? null;
        $categoryId = $this->categoryMap[$categoryName] ?? null;

        $payload = [
            'name' => $name,
            'lga_id' => $lgaId,
            'category_id' => $categoryId,
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'ownership_status' => strtoupper((string) ($data['ownership_status'] ?? '')),
            'condition_assessment' => strtoupper((string) ($data['condition_assessment'] ?? '')),
            'visitor_capacity' => isset($data['visitor_capacity']) && $data['visitor_capacity'] !== '' ? (int) $data['visitor_capacity'] : null,
            'description' => $data['description'] ?? null,
            'change_summary' => 'Bulk imported via spreadsheet',
        ];

        $validator = Validator::make($payload, [
            'name' => [
                'required',
                'string',
                'max:180',
                Rule::unique('cultural_assets', 'name')->where(fn ($q) => $q->where('lga_id', $lgaId)),
            ],
            'lga_id' => ['required', 'integer'],
            'category_id' => ['required', 'integer'],
            'latitude' => ['nullable', 'numeric', 'between:6.0,9.5'],
            'longitude' => ['nullable', 'numeric', 'between:4.5,8.5'],
            'ownership_status' => ['required', Rule::in(OwnershipStatus::values())],
            'condition_assessment' => ['required', Rule::in(ConditionAssessment::values())],
            'visitor_capacity' => ['nullable', 'integer', 'min:0'],
        ], [
            'name.unique' => "Duplicate asset '{$name}' already exists in LGA '{$data['lga']}'.",
            'lga_id.required' => "Unknown LGA '{$data['lga']}'. Must match one of Kogi's 21 configured LGAs.",
            'category_id.required' => "Unknown category '{$data['category']}'. Must match an active Asset Category.",
        ]);

        if ($validator->fails()) {
            $this->rowErrors[] = [
                'row' => $rowIndex,
                'name' => $name !== '' ? $name : '(blank)',
                'errors' => $validator->errors()->all(),
            ];

            return;
        }

        $this->assetService->create($payload, $this->actor);
        $this->importedCount++;
    }

    public function chunkSize(): int
    {
        return 100;
    }
}
