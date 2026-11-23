<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CulturalAssetTemplateExport implements FromArray, WithHeadings
{
    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return [
            'name',
            'category',
            'lga',
            'latitude',
            'longitude',
            'ownership_status',
            'condition_assessment',
            'visitor_capacity',
            'description',
        ];
    }

    /**
     * @return array<int, array<int, mixed>>
     */
    public function array(): array
    {
        return [
            [
                'Example: Cenotaph of World War Heroes',
                'Historical Monument & Colonial Heritage',
                'Lokoja',
                '7.8012000',
                '6.7341000',
                'STATE_GOVERNMENT',
                'GOOD',
                250,
                'Replace or remove this sample row before uploading.',
            ],
        ];
    }
}
