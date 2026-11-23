<?php

namespace App\Http\Requests\Culture;

use App\Enums\ConditionAssessment;
use App\Enums\OwnershipStatus;
use App\Models\CulturalAsset;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveCulturalAssetRequest extends FormRequest
{
    public function authorize(): bool
    {
        $asset = $this->route('cultural_asset');

        if ($asset instanceof CulturalAsset) {
            return $this->user()?->can('update', $asset) ?? false;
        }

        return $this->user()?->can('create', CulturalAsset::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $asset = $this->route('cultural_asset');
        $assetId = $asset instanceof CulturalAsset ? $asset->id : null;

        return [
            'name' => [
                'required',
                'string',
                'max:180',
                Rule::unique('cultural_assets', 'name')
                    ->where(fn ($query) => $query->where('lga_id', $this->input('lga_id')))
                    ->ignore($assetId),
            ],
            'category_id' => ['required', 'exists:asset_categories,id'],
            'lga_id' => ['required', 'exists:lgas,id'],
            'latitude' => ['nullable', 'numeric', 'between:6.0,9.5', 'required_with:longitude'],
            'longitude' => ['nullable', 'numeric', 'between:4.5,8.5', 'required_with:latitude'],
            'ownership_status' => ['required', Rule::enum(OwnershipStatus::class)],
            'condition_assessment' => ['required', Rule::enum(ConditionAssessment::class)],
            'visitor_capacity' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'description' => ['nullable', 'string', 'max:5000'],
            'change_summary' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.unique' => 'A cultural or tourism asset with this exact name already exists in the selected LGA.',
        ];
    }
}
