<?php

namespace App\Http\Requests\Licensing;

use App\Models\Inspection;
use Illuminate\Foundation\Http\FormRequest;

class ScheduleInspectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Inspection::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'hotel_id' => ['required', 'exists:hotels,id'],
            'license_application_id' => ['nullable', 'exists:license_applications,id'],
            'assigned_officer_id' => ['required', 'exists:users,id'],
            'scheduled_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
