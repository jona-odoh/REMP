<?php

namespace App\Http\Requests\Config;

use App\Models\InspectionChecklistTemplate;
use Illuminate\Foundation\Http\FormRequest;

class SaveChecklistTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', InspectionChecklistTemplate::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'license_type_id' => ['nullable', 'exists:license_types,id'],
            'section' => ['required', 'string', 'max:100'],
            'item_text' => ['required', 'string', 'max:255'],
            'is_mandatory' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
