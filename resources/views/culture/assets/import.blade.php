@extends('layouts.app')

@section('title', 'Bulk Import Cultural Assets')
@section('heading', 'Bulk Import Cultural & Tourism Assets')

@section('content')
<div class="max-w-3xl space-y-5">
    <a href="{{ route('culture.assets.index') }}" class="text-sm font-medium text-emerald-700 hover:text-emerald-900">← Back to Asset Registry</a>

    <div class="rounded-lg bg-white border border-slate-200 shadow-sm p-6 space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <div>
                <h3 class="text-base font-semibold text-slate-900">Upload Excel (.xlsx) or CSV File</h3>
                <p class="text-xs text-slate-600 mt-0.5">Rows are validated individually. Duplicate assets (same name + LGA) and invalid rows are skipped and reported below.</p>
            </div>
            <a href="{{ route('culture.assets.template') }}" class="rounded-md border border-emerald-700 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-800 hover:bg-emerald-100">
                Download Template (.xlsx)
            </a>
        </div>

        <form method="POST" action="{{ route('culture.assets.import') }}" enctype="multipart/form-data" class="space-y-4 pt-2 border-t border-slate-200">
            @csrf
            <div>
                <label for="file" class="block text-sm font-medium text-slate-700">Spreadsheet File (.xlsx, .xls, .csv — max 10MB) *</label>
                <input id="file" name="file" type="file" required accept=".csv,.xlsx,.xls"
                       class="mt-1 block w-full text-sm text-slate-700 file:mr-3 file:rounded-md file:border-0 file:bg-emerald-700 file:px-4 file:py-2 file:text-xs file:font-semibold file:text-white hover:file:bg-emerald-800">
            </div>

            <div class="rounded-md bg-slate-50 border border-slate-200 p-3 text-xs text-slate-700 space-y-1">
                <p class="font-semibold">Allowed enum codes:</p>
                <p><strong>ownership_status:</strong> {{ implode(', ', array_map(fn($c) => $c->value, $ownerships)) }}</p>
                <p><strong>condition_assessment:</strong> {{ implode(', ', array_map(fn($c) => $c->value, $conditions)) }}</p>
            </div>

            <button type="submit" class="rounded-md bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-800">
                Run Bulk Import
            </button>
        </form>
    </div>

    @if(session('import_errors') && count(session('import_errors')) > 0)
    <div class="rounded-lg bg-white border border-rose-200 shadow-sm overflow-hidden">
        <div class="px-5 py-3 bg-rose-50 border-b border-rose-200">
            <h3 class="text-sm font-semibold text-rose-900">Skipped Rows &amp; Validation Errors ({{ count(session('import_errors')) }})</h3>
        </div>
        <table class="min-w-full divide-y divide-slate-200 text-xs">
            <thead class="bg-slate-50 text-left uppercase text-slate-500">
                <tr>
                    <th class="px-4 py-2">Row #</th>
                    <th class="px-4 py-2">Asset Name</th>
                    <th class="px-4 py-2">Validation Errors</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach(session('import_errors') as $err)
                <tr>
                    <td class="px-4 py-2 font-mono font-semibold">{{ $err['row'] }}</td>
                    <td class="px-4 py-2 font-medium text-slate-900">{{ $err['name'] }}</td>
                    <td class="px-4 py-2 text-rose-800">
                        <ul class="list-disc list-inside">
                            @foreach($err['errors'] as $msg)
                                <li>{{ $msg }}</li>
                            @endforeach
                        </ul>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>
@endsection
