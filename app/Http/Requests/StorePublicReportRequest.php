<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePublicReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Publicly accessible without auth
    }

    public function rules(): array
    {
        return [
            'lga_id' => ['required', 'exists:lgas,id'],
            'ward_id' => ['required', 'exists:wards,id'],
            'community_id' => ['nullable', 'exists:communities,id'],
            'custom_community_name' => ['nullable', 'string', 'max:255'],
            'reporter_name' => ['nullable', 'string', 'max:255'],
            'reporter_contact' => ['nullable', 'string', 'max:255'],
            'electricity_situation_enum' => ['required', 'in:no_electricity,irregular,inadequate,partial'],
            'duration_of_problem' => ['required', 'string', 'max:255'],
            'affected_households_estimate' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'affected_facilities_json' => ['nullable', 'array'],
            'affected_facilities_json.*' => ['string', 'in:schools,hospitals,businesses,water_supply,public_lighting'],
            'description' => ['required', 'string', 'max:3000'],
            'latitude' => ['nullable', 'numeric', 'between:6.0,9.0'],
            'longitude' => ['nullable', 'numeric', 'between:5.0,8.5'],
            'photo' => ['nullable', 'image', 'max:5120'],
        ];
    }
}
