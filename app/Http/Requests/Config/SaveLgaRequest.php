<?php

namespace App\Http\Requests\Config;

use App\Models\Lga;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveLgaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Lga::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $lga = $this->route('lga');
        $lgaId = $lga instanceof Lga ? $lga->id : null;

        return [
            'name' => ['required', 'string', 'max:100', Rule::unique('lgas', 'name')->ignore($lgaId)],
            'code' => ['required', 'string', 'max:20', Rule::unique('lgas', 'code')->ignore($lgaId)],
            'senatorial_district' => ['required', Rule::in(['Kogi Central', 'Kogi East', 'Kogi West'])],
            'headquarters' => ['nullable', 'string', 'max:100'],
            'centroid_lat' => ['nullable', 'numeric', 'between:6.0,9.0'],
            'centroid_lng' => ['nullable', 'numeric', 'between:5.0,8.5'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
