<?php

namespace App\Http\Requests\Licensing;

use App\Models\LicenseApplication;
use Illuminate\Foundation\Http\FormRequest;

class DecideLicenseApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $application = $this->route('application');

        return $application instanceof LicenseApplication
            && ($this->user()?->can('approve', $application) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'decision_reason' => ['required', 'string', 'min:5', 'max:2000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'decision_reason.required' => 'A documented reason is mandatory for license approval or rejection.',
        ];
    }
}
