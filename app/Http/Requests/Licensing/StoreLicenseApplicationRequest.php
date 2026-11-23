<?php

namespace App\Http\Requests\Licensing;

use App\Models\LicenseApplication;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLicenseApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', LicenseApplication::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'hotel_id' => ['required', 'exists:hotels,id'],
            'license_type_id' => ['required', 'exists:license_types,id'],
            'application_type' => ['required', Rule::in(['NEW', 'RENEWAL'])],
            'assigned_officer_id' => ['nullable', 'exists:users,id'],
            'submit_immediately' => ['nullable', 'boolean'],
        ];
    }
}
