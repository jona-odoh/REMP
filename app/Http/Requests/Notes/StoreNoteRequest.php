<?php

namespace App\Http\Requests\Notes;

use App\Models\Note;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Note::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'notable_type' => ['required', Rule::in(['cultural_asset', 'hotel', 'license_application', 'project'])],
            'notable_id' => ['required', 'integer'],
            'parent_id' => ['nullable', 'integer', 'exists:notes,id'],
            'body' => ['required', 'string', 'min:2', 'max:3000'],
        ];
    }
}
