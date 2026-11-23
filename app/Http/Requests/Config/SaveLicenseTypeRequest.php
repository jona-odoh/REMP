<?php

namespace App\Http\Requests\Config;

use App\Models\LicenseType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveLicenseTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', LicenseType::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $licenseType = $this->route('license_type');
        $licenseTypeId = $licenseType instanceof LicenseType ? $licenseType->id : null;

        return [
            'name' => ['required', 'string', 'max:120', Rule::unique('license_types', 'name')->ignore($licenseTypeId)],
            'code' => ['required', 'string', 'max:30', Rule::unique('license_types', 'code')->ignore($licenseTypeId)],
            'description' => ['nullable', 'string', 'max:1000'],
            'validity_months' => ['required', 'integer', 'min:1', 'max:60'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
