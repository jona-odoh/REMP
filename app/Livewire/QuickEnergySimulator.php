<?php

namespace App\Livewire;

use App\Models\Community;
use App\Models\Lga;
use App\Models\Ward;
use Livewire\Component;

class QuickEnergySimulator extends Component
{
    public ?int $selectedLgaId = null;
    public ?int $selectedWardId = null;
    public float $simultaneousFactor = 0.70;
    public int $sampleHouseholds = 85;
    public int $sampleSchools = 1;
    public int $sampleClinics = 1;
    public int $sampleBusinesses = 3;

    public function render()
    {
        $dailyKwh = round(
            ($this->sampleHouseholds * 0.85) +
            ($this->sampleSchools * 8.5) +
            ($this->sampleClinics * 12.0) +
            ($this->sampleBusinesses * 14.5),
            2
        );

        $peakKw = round(($dailyKwh / 5.2) * $this->simultaneousFactor, 2);
        $pvCapacityKw = round($dailyKwh / (4.6 * 0.78), 2);

        $recommendedSolution = $dailyKwh >= 45
            ? 'Solar Hybrid Mini-Grid'
            : ($this->sampleBusinesses >= 3 ? 'Productive-Use Solar System' : 'Solar Home Systems (SHS)');

        $indicativeCostNgn = round($pvCapacityKw * 1450000, 2);

        return view('livewire.quick-energy-simulator', [
            'dailyKwh' => $dailyKwh,
            'peakKw' => $peakKw,
            'pvCapacityKw' => $pvCapacityKw,
            'recommendedSolution' => $recommendedSolution,
            'indicativeCostNgn' => $indicativeCostNgn,
        ]);
    }
}
