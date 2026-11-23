@extends('layouts.app')

@section('title', 'Kogi State LGAs')
@section('heading', 'System Configuration — Kogi State LGAs (21)')

@section('content')
@can('create', \App\Models\Lga::class)
<div class="rounded-lg bg-white border border-slate-200 shadow-sm p-5" x-data="{ open: false }">
    <div class="flex items-center justify-between">
        <h3 class="text-sm font-semibold text-slate-900">Manage Local Government Areas</h3>
        <button type="button" @click="open = !open" class="rounded-md bg-emerald-700 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-800">
            <span x-text="open ? 'Close Form' : '+ Add LGA'"></span>
        </button>
    </div>

    <form x-show="open" x-cloak method="POST" action="{{ route('config.lgas.store') }}" class="mt-4 pt-4 border-t border-slate-200 grid grid-cols-1 sm:grid-cols-3 gap-3">
        @csrf
        <input name="name" type="text" placeholder="LGA Name *" required class="rounded-md border border-slate-300 px-3 py-1.5 text-sm">
        <input name="code" type="text" placeholder="Code (e.g. KG-LOK) *" required class="rounded-md border border-slate-300 px-3 py-1.5 text-sm">
        <select name="senatorial_district" required class="rounded-md border border-slate-300 px-3 py-1.5 text-sm">
            <option value="Kogi Central">Kogi Central</option>
            <option value="Kogi East">Kogi East</option>
            <option value="Kogi West">Kogi West</option>
        </select>
        <input name="headquarters" type="text" placeholder="Headquarters" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm">
        <input name="centroid_lat" type="number" step="0.0000001" placeholder="Centroid Latitude (e.g. 7.8023)" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm">
        <input name="centroid_lng" type="number" step="0.0000001" placeholder="Centroid Longitude (e.g. 6.7333)" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm">
        <div class="sm:col-span-3 flex items-center justify-between">
            <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                <input type="checkbox" name="is_active" value="1" checked class="rounded border-slate-300 text-emerald-700">
                Active
            </label>
            <button type="submit" class="rounded-md bg-emerald-700 px-4 py-1.5 text-sm font-semibold text-white hover:bg-emerald-800">Save LGA</button>
        </div>
    </form>
</div>
@endcan

<div class="rounded-lg bg-white border border-slate-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-4 py-3">Code</th>
                    <th class="px-4 py-3">LGA Name</th>
                    <th class="px-4 py-3">Senatorial District</th>
                    <th class="px-4 py-3">Headquarters</th>
                    <th class="px-4 py-3">Centroid (Lat, Lng)</th>
                    <th class="px-4 py-3">Status</th>
                    @can('delete', \App\Models\Lga::class)<th class="px-4 py-3 text-right">Actions</th>@endcan
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($lgas as $lga)
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-2.5 font-mono text-xs text-slate-600">{{ $lga->code }}</td>
                    <td class="px-4 py-2.5 font-semibold text-slate-900">{{ $lga->name }}</td>
                    <td class="px-4 py-2.5 text-slate-700">{{ $lga->senatorial_district }}</td>
                    <td class="px-4 py-2.5 text-slate-600">{{ $lga->headquarters ?? '—' }}</td>
                    <td class="px-4 py-2.5 font-mono text-xs text-slate-500">{{ $lga->centroid_lat }}, {{ $lga->centroid_lng }}</td>
                    <td class="px-4 py-2.5">
                        <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold {{ $lga->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-700' }}">
                            {{ $lga->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    @can('delete', $lga)
                    <td class="px-4 py-2.5 text-right">
                        <form method="POST" action="{{ route('config.lgas.destroy', $lga) }}" onsubmit="return confirm('Delete LGA {{ $lga->name }}?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-xs font-medium text-rose-700 hover:text-rose-900">Delete</button>
                        </form>
                    </td>
                    @endcan
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="px-4 py-3 border-t border-slate-200">{{ $lgas->links() }}</div>
</div>
@endsection
