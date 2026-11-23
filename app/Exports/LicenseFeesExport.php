<?php

namespace App\Exports;

use App\Models\LicenseFee;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class LicenseFeesExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(
        private readonly ?int $lgaId = null,
        private readonly ?string $month = null,
    ) {}

    public function collection(): Collection
    {
        return LicenseFee::query()
            ->with(['hotel.lga', 'licenseApplication', 'recordedBy'])
            ->when($this->lgaId, fn ($q) => $q->whereHas('hotel', fn ($h) => $h->where('lga_id', $this->lgaId)))
            ->when($this->month, fn ($q) => $q->where('paid_at', 'like', $this->month.'%'))
            ->latest('paid_at')
            ->get();
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return [
            'Receipt Reference',
            'Hotel / Establishment',
            'LGA',
            'Period',
            'Amount (NGN)',
            'Payment Date',
            'Recorded By',
        ];
    }

    /**
     * @param  LicenseFee  $fee
     * @return array<int, mixed>
     */
    public function map($fee): array
    {
        return [
            $fee->receipt_reference,
            $fee->hotel?->name,
            $fee->hotel?->lga?->name,
            $fee->period,
            (float) $fee->amount,
            $fee->paid_at?->format('d/m/Y'),
            $fee->recordedBy?->name,
        ];
    }
}
