<?php

namespace App\Http\Requests\Config;

use App\Models\FeeSchedule;
use Illuminate\Foundation\Http\FormRequest;

class SaveFeeScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', FeeSchedule::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'license_type_id' => ['required', 'exists:license_types,id'],
            'classification_rating' => ['required', 'string', 'max:60'],
            'amount' => ['required', 'numeric', 'min:0', 'max:999999999.99'],
            'effective_from' => ['required', 'date'],
            'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
