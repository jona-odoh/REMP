<?php

namespace App\Http\Requests\Culture;

use App\Models\CulturalAsset;
use Illuminate\Foundation\Http\FormRequest;

class ImportCulturalAssetsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', CulturalAsset::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:csv,txt,xlsx,xls', 'max:10240'],
        ];
    }
}
