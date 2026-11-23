@extends('layouts.app')

@section('title', 'Asset Categories')
@section('heading', 'System Configuration — Cultural & Tourism Asset Categories')

@section('content')
@can('create', \App\Models\AssetCategory::class)
<div class="rounded-lg bg-white border border-slate-200 shadow-sm p-5">
    <h3 class="text-sm font-semibold text-slate-900 mb-3">Add Asset Category</h3>
    <form method="POST" action="{{ route('config.asset-categories.store') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
        @csrf
        <input name="name" type="text" placeholder="Category Name *" required class="rounded-md border border-slate-300 px-3 py-1.5 text-sm">
        <input name="description" type="text" placeholder="Description" class="sm:col-span-2 rounded-md border border-slate-300 px-3 py-1.5 text-sm">
        <div class="flex gap-2">
            <input name="sort_order" type="number" min="0" value="0" aria-label="Sort order" class="w-20 rounded-md border border-slate-300 px-2 py-1.5 text-sm">
            <input type="hidden" name="is_active" value="1">
            <button type="submit" class="flex-1 rounded-md bg-emerald-700 px-3 py-1.5 text-sm font-semibold text-white hover:bg-emerald-800">Add</button>
        </div>
    </form>
</div>
@endcan

<div class="rounded-lg bg-white border border-slate-200 shadow-sm overflow-hidden">
    <table class="min-w-full divide-y divide-slate-200 text-sm">
        <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
            <tr>
                <th class="px-4 py-3">Order</th>
                <th class="px-4 py-3">Name</th>
                <th class="px-4 py-3">Slug</th>
                <th class="px-4 py-3">Description</th>
                <th class="px-4 py-3">Status</th>
                @can('delete', \App\Models\AssetCategory::class)<th class="px-4 py-3 text-right">Actions</th>@endcan
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @foreach($categories as $category)
            <tr class="hover:bg-slate-50">
                <td class="px-4 py-2.5 text-slate-500">{{ $category->sort_order }}</td>
                <td class="px-4 py-2.5 font-semibold text-slate-900">{{ $category->name }}</td>
                <td class="px-4 py-2.5 font-mono text-xs text-slate-500">{{ $category->slug }}</td>
                <td class="px-4 py-2.5 text-slate-600">{{ $category->description ?? '—' }}</td>
                <td class="px-4 py-2.5">
                    <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold {{ $category->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-700' }}">
                        {{ $category->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </td>
                @can('delete', $category)
                <td class="px-4 py-2.5 text-right">
                    <form method="POST" action="{{ route('config.asset-categories.destroy', $category) }}" onsubmit="return confirm('Delete category {{ $category->name }}?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-xs font-medium text-rose-700 hover:text-rose-900">Delete</button>
                    </form>
                </td>
                @endcan
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="px-4 py-3 border-t border-slate-200">{{ $categories->links() }}</div>
</div>
@endsection
