<?php

namespace App\Http\Requests\Admin;

use App\Enums\Department;
use App\Enums\RoleName;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', User::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'string', 'email', 'max:191', 'unique:users,email'],
            'staff_id' => ['nullable', 'string', 'max:50', 'unique:users,staff_id'],
            'phone' => ['nullable', 'string', 'max:30'],
            'department' => ['required', Rule::enum(Department::class)],
            'role' => ['required', 'string', Rule::in(RoleName::values())],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
            'must_change_password' => ['nullable', 'boolean'],
        ];
    }
}
