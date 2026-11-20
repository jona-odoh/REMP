<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAssessmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasRoleOrEnum(['admin', 'field_officer', 'lga_officer']);
    }

    public function rules(): array
    {
        return [
            'community_id' => ['required', 'exists:communities,id'],
            'assessment_date' => ['required', 'date'],
            'grid_connected_boolean' => ['required', 'boolean'],
            'average_supply_hours' => ['required', 'numeric', 'min:0', 'max:24'],
            'interruption_frequency' => ['nullable', 'string', 'max:255'],
            'alternative_energy_sources' => ['nullable', 'string', 'max:255'],
            'households_with_electricity' => ['required', 'integer', 'min:0'],
            'households_without_electricity' => ['required', 'integer', 'min:0'],
            'gps_coordinates' => ['nullable', 'string', 'max:100'],
            'status_enum' => ['required', 'in:draft,completed'],
            'photo' => ['nullable', 'image', 'max:5120'],

            // Step 2: Households
            'households_assessed_count' => ['nullable', 'integer', 'min:0'],
            'household_existing_sources' => ['nullable', 'string', 'max:255'],

            // Step 3: Schools
            'schools' => ['nullable', 'array'],
            'schools.*.name' => ['required_with:schools', 'string', 'max:255'],
            'schools.*.location' => ['nullable', 'string', 'max:255'],
            'schools.*.school_type' => ['nullable', 'string', 'max:100'],
            'schools.*.classrooms_count' => ['nullable', 'integer', 'min:0'],
            'schools.*.offices_count' => ['nullable', 'integer', 'min:0'],
            'schools.*.existing_sources' => ['nullable', 'string', 'max:255'],
            'schools.*.operating_hours' => ['nullable', 'numeric', 'min:0', 'max:24'],

            // Step 4: Health Facilities
            'health_facilities' => ['nullable', 'array'],
            'health_facilities.*.name' => ['required_with:health_facilities', 'string', 'max:255'],
            'health_facilities.*.location' => ['nullable', 'string', 'max:255'],
            'health_facilities.*.facility_type' => ['nullable', 'string', 'max:100'],
            'health_facilities.*.refrigeration_requirements' => ['nullable', 'string', 'max:255'],
            'health_facilities.*.lighting_needs' => ['nullable', 'string', 'max:255'],
            'health_facilities.*.existing_sources' => ['nullable', 'string', 'max:255'],

            // Step 5: Businesses
            'businesses' => ['nullable', 'array'],
            'businesses.*.name' => ['required_with:businesses', 'string', 'max:255'],
            'businesses.*.category' => ['nullable', 'string', 'max:100'],
            'businesses.*.business_type' => ['nullable', 'string', 'max:100'],
            'businesses.*.operating_hours' => ['nullable', 'numeric', 'min:0', 'max:24'],
            'businesses.*.existing_sources' => ['nullable', 'string', 'max:255'],

            // Appliances Matrix
            'appliances' => ['nullable', 'array'],
            'appliances.*.facility_type' => ['required_with:appliances', 'in:household,school,hospital,business'],
            'appliances.*.appliance_id' => ['nullable', 'exists:appliances,id'],
            'appliances.*.custom_appliance_name' => ['nullable', 'string', 'max:255'],
            'appliances.*.rated_power_watts' => ['required_with:appliances', 'integer', 'min:1', 'max:500000'],
            'appliances.*.quantity' => ['required_with:appliances', 'integer', 'min:1', 'max:100000'],
            'appliances.*.operating_hours_per_day' => ['required_with:appliances', 'numeric', 'min:0.1', 'max:24'],
        ];
    }
}
