<?php

namespace App\Http\Requests\Document;

use App\Models\Document;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Document::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'documentable_type' => ['nullable', 'string', Rule::in(['cultural_asset', 'intangible_heritage', 'hotel', 'license_application', 'inspection', 'project'])],
            'documentable_id' => ['nullable', 'integer', 'required_with:documentable_type'],
            'title' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:2000'],
            'category' => ['nullable', 'string', 'max:80'],
            'document_date' => ['nullable', 'date'],
            'file' => [
                'required',
                'file',
                'max:25600', // 25 MB max
                'mimes:jpg,jpeg,png,webp,gif,pdf,doc,docx,xls,xlsx,csv,txt,mp4,mov,webm,mp3,wav,ogg,m4a',
            ],
        ];
    }
}
