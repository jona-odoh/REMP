<?php

namespace App\Http\Requests\Licensing;

use App\Models\Inspection;
use Illuminate\Foundation\Http\FormRequest;

class CompleteInspectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $inspection = $this->route('inspection');

        return $inspection instanceof Inspection
            && ($this->user()?->can('update', $inspection) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'checklist' => ['required', 'array', 'min:1'],
            'checklist.*.template_id' => ['required', 'integer', 'exists:inspection_checklist_templates,id'],
            'checklist.*.passed' => ['required', 'boolean'],
            'checklist.*.remark' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:3000'],
        ];
    }
}
