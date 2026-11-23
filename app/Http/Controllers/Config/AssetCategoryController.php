<?php

namespace App\Http\Controllers\Config;

use App\Http\Controllers\Controller;
use App\Http\Requests\Config\SaveAssetCategoryRequest;
use App\Models\AssetCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AssetCategoryController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', AssetCategory::class);

        $categories = AssetCategory::orderBy('sort_order')->orderBy('name')->paginate(20);

        return view('config.asset-categories.index', compact('categories'));
    }

    public function store(SaveAssetCategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = ! empty($data['slug']) ? Str::slug($data['slug']) : Str::slug($data['name']);
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['is_active'] = $request->boolean('is_active', true);

        AssetCategory::create($data);

        return redirect()->route('config.asset-categories.index')->with('status', 'Asset category created.');
    }

    public function update(SaveAssetCategoryRequest $request, AssetCategory $assetCategory): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = ! empty($data['slug']) ? Str::slug($data['slug']) : Str::slug($data['name']);
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['is_active'] = $request->boolean('is_active', false);

        $assetCategory->update($data);

        return redirect()->route('config.asset-categories.index')->with('status', 'Asset category updated.');
    }

    public function destroy(AssetCategory $assetCategory): RedirectResponse
    {
        $this->authorize('delete', $assetCategory);

        $assetCategory->delete();

        return redirect()->route('config.asset-categories.index')->with('status', 'Asset category deleted.');
    }
}
