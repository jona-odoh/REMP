@extends('layouts.app')

@section('title', 'License Types')
@section('heading', 'System Configuration — Hospitality License Types')

@section('content')
@can('create', \App\Models\LicenseType::class)
<div class="rounded-lg bg-white border border-slate-200 shadow-sm p-5">
    <h3 class="text-sm font-semibold text-slate-900 mb-3">Add License Type</h3>
    <form method="POST" action="{{ route('config.license-types.store') }}" class="grid grid-cols-1 sm:grid-cols-5 gap-3">
        @csrf
        <input name="name" type="text" placeholder="License Name *" required class="sm:col-span-2 rounded-md border border-slate-300 px-3 py-1.5 text-sm">
        <input name="code" type="text" placeholder="Code (e.g. LIC-HTL) *" required class="rounded-md border border-slate-300 px-3 py-1.5 text-sm">
        <input name="validity_months" type="number" min="1" max="60" value="12" required aria-label="Validity in months" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm">
        <input type="hidden" name="is_active" value="1">
        <button type="submit" class="rounded-md bg-emerald-700 px-3 py-1.5 text-sm font-semibold text-white hover:bg-emerald-800">Add</button>
        <input name="description" type="text" placeholder="Description" class="sm:col-span-5 rounded-md border border-slate-300 px-3 py-1.5 text-sm">
    </form>
</div>
@endcan

<div class="rounded-lg bg-white border border-slate-200 shadow-sm overflow-hidden">
    <table class="min-w-full divide-y divide-slate-200 text-sm">
        <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
            <tr>
                <th class="px-4 py-3">Code</th>
                <th class="px-4 py-3">License Type</th>
                <th class="px-4 py-3">Validity</th>
                <th class="px-4 py-3">Fee Tiers</th>
                <th class="px-4 py-3">Status</th>
                @can('delete', \App\Models\LicenseType::class)<th class="px-4 py-3 text-right">Actions</th>@endcan
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @foreach($licenseTypes as $type)
            <tr class="hover:bg-slate-50">
                <td class="px-4 py-2.5 font-mono text-xs text-slate-600">{{ $type->code }}</td>
                <td class="px-4 py-2.5">
                    <div class="font-semibold text-slate-900">{{ $type->name }}</div>
                    <div class="text-xs text-slate-500">{{ $type->description ?? '' }}</div>
                </td>
                <td class="px-4 py-2.5 text-slate-700">{{ $type->validity_months }} months</td>
                <td class="px-4 py-2.5 text-slate-600">{{ $type->fee_schedules_count }}</td>
                <td class="px-4 py-2.5">
                    <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold {{ $type->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-700' }}">
                        {{ $type->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </td>
                @can('delete', $type)
                <td class="px-4 py-2.5 text-right">
                    <form method="POST" action="{{ route('config.license-types.destroy', $type) }}" onsubmit="return confirm('Delete license type {{ $type->name }}?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-xs font-medium text-rose-700 hover:text-rose-900">Delete</button>
                    </form>
                </td>
                @endcan
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="px-4 py-3 border-t border-slate-200">{{ $licenseTypes->links() }}</div>
</div>
@endsection
