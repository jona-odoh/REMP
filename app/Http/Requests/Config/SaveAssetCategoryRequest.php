<?php

namespace App\Http\Requests\Config;

use App\Models\AssetCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveAssetCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', AssetCategory::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $category = $this->route('asset_category');
        $categoryId = $category instanceof AssetCategory ? $category->id : null;

        return [
            'name' => ['required', 'string', 'max:120', Rule::unique('asset_categories', 'name')->ignore($categoryId)],
            'slug' => ['nullable', 'string', 'max:120', Rule::unique('asset_categories', 'slug')->ignore($categoryId)],
            'description' => ['nullable', 'string', 'max:1000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
