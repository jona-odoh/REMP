<?php

namespace App\Http\Requests\Admin;

use App\Enums\Department;
use App\Enums\RoleName;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $target = $this->route('user');

        return $target instanceof User && ($this->user()?->can('update', $target) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var User $target */
        $target = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'string', 'email', 'max:191', Rule::unique('users', 'email')->ignore($target->id)],
            'staff_id' => ['nullable', 'string', 'max:50', Rule::unique('users', 'staff_id')->ignore($target->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'department' => ['required', Rule::enum(Department::class)],
            'role' => ['required', 'string', Rule::in(RoleName::values())],
        ];
    }
}
