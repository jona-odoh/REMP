@extends('layouts.app')

@section('title', 'Inspection Checklists')
@section('heading', 'System Configuration — Inspection Checklist Templates')

@section('content')
@can('create', \App\Models\InspectionChecklistTemplate::class)
<div class="rounded-lg bg-white border border-slate-200 shadow-sm p-5">
    <h3 class="text-sm font-semibold text-slate-900 mb-3">Add Checklist Item</h3>
    <form method="POST" action="{{ route('config.checklist-templates.store') }}" class="grid grid-cols-1 sm:grid-cols-6 gap-3">
        @csrf
        <select name="license_type_id" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm">
            <option value="">All License Types</option>
            @foreach($licenseTypes as $type)
                <option value="{{ $type->id }}">{{ $type->name }}</option>
            @endforeach
        </select>
        <input name="section" type="text" placeholder="Section (e.g. Fire Safety) *" required class="rounded-md border border-slate-300 px-3 py-1.5 text-sm">
        <input name="item_text" type="text" placeholder="Inspection criterion *" required class="sm:col-span-2 rounded-md border border-slate-300 px-3 py-1.5 text-sm">
        <label class="inline-flex items-center gap-2 text-xs text-slate-700">
            <input type="checkbox" name="is_mandatory" value="1" checked class="rounded border-slate-300 text-emerald-700">
            Mandatory
        </label>
        <input type="hidden" name="is_active" value="1">
        <button type="submit" class="rounded-md bg-emerald-700 px-3 py-1.5 text-sm font-semibold text-white hover:bg-emerald-800">Add Item</button>
    </form>
</div>
@endcan

<div class="rounded-lg bg-white border border-slate-200 shadow-sm overflow-hidden">
    <table class="min-w-full divide-y divide-slate-200 text-sm">
        <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
            <tr>
                <th class="px-4 py-3">Section</th>
                <th class="px-4 py-3">Scope</th>
                <th class="px-4 py-3">Checklist Criterion</th>
                <th class="px-4 py-3">Mandatory</th>
                @can('delete', \App\Models\InspectionChecklistTemplate::class)<th class="px-4 py-3 text-right">Actions</th>@endcan
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @foreach($templates as $item)
            <tr class="hover:bg-slate-50">
                <td class="px-4 py-2.5 font-semibold text-slate-900">{{ $item->section }}</td>
                <td class="px-4 py-2.5 text-xs text-slate-600">{{ $item->licenseType?->name ?? 'All License Types' }}</td>
                <td class="px-4 py-2.5 text-slate-700">{{ $item->item_text }}</td>
                <td class="px-4 py-2.5">
                    <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold {{ $item->is_mandatory ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-600' }}">
                        {{ $item->is_mandatory ? 'Mandatory' : 'Optional' }}
                    </span>
                </td>
                @can('delete', $item)
                <td class="px-4 py-2.5 text-right">
                    <form method="POST" action="{{ route('config.checklist-templates.destroy', $item) }}" onsubmit="return confirm('Delete this checklist item?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-xs font-medium text-rose-700 hover:text-rose-900">Delete</button>
                    </form>
                </td>
                @endcan
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="px-4 py-3 border-t border-slate-200">{{ $templates->links() }}</div>
</div>
@endsection
