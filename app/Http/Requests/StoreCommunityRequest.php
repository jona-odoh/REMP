<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommunityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasRoleOrEnum(['admin', 'lga_officer']);
    }

    public function rules(): array
    {
        return [
            'ward_id' => ['required', 'exists:wards,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'latitude' => ['required', 'numeric', 'between:6.0,9.0'],
            'longitude' => ['required', 'numeric', 'between:5.0,8.5'],
            'estimated_population' => ['nullable', 'integer', 'min:0'],
            'estimated_households' => ['nullable', 'integer', 'min:0'],
            'electricity_status_enum' => ['required', 'in:no_electricity,unreliable,inadequate,reliable'],
            'is_verified' => ['nullable', 'boolean'],
        ];
    }
}
