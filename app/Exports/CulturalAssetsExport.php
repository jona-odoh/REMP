<?php

namespace App\Exports;

use App\Models\CulturalAsset;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CulturalAssetsExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function __construct(
        private readonly array $filters = [],
    ) {}

    public function collection(): Collection
    {
        return CulturalAsset::query()
            ->with(['category', 'lga'])
            ->filter($this->filters)
            ->orderBy('name')
            ->get();
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return [
            'ID',
            'Asset Name',
            'Category',
            'LGA',
            'Senatorial District',
            'Latitude',
            'Longitude',
            'Ownership Status',
            'Condition Assessment',
            'Visitor Capacity',
            'Version',
            'Description',
        ];
    }

    /**
     * @param  CulturalAsset  $asset
     * @return array<int, mixed>
     */
    public function map($asset): array
    {
        return [
            $asset->id,
            $asset->name,
            $asset->category?->name,
            $asset->lga?->name,
            $asset->lga?->senatorial_district,
            $asset->location?->latitude,
            $asset->location?->longitude,
            $asset->ownership_status?->label(),
            $asset->condition_assessment?->label(),
            $asset->visitor_capacity,
            'v'.$asset->current_version,
            $asset->description,
        ];
    }
}
