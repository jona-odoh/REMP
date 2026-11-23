<?php

namespace App\Http\Requests\Tasks;

use App\Models\Task;
use Illuminate\Foundation\Http\FormRequest;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Task::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:3000'],
            'assigned_to' => ['required', 'exists:users,id'],
            'related_entity_type' => ['nullable', 'string', 'max:100'],
            'related_entity_id' => ['nullable', 'integer'],
            'due_date' => ['required', 'date'],
        ];
    }
}
