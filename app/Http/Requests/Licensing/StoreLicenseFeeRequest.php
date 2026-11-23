<?php

namespace App\Http\Requests\Licensing;

use App\Models\LicenseFee;
use Illuminate\Foundation\Http\FormRequest;

class StoreLicenseFeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', LicenseFee::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'hotel_id' => ['required', 'exists:hotels,id'],
            'license_application_id' => ['nullable', 'exists:license_applications,id'],
            'amount' => ['required', 'numeric', 'min:1', 'max:999999999.99'],
            'receipt_reference' => ['required', 'string', 'max:80', 'unique:license_fees,receipt_reference'],
            'paid_at' => ['required', 'date'],
            'period' => ['required', 'string', 'max:60'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
