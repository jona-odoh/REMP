@extends('layouts.app')

@section('title', $asset->name)
@section('heading', $asset->name)

@section('content')
<div class="flex flex-wrap items-center justify-between gap-2">
    <a href="{{ route('culture.assets.index') }}" class="text-sm font-medium text-emerald-700 hover:text-emerald-900">← Back to Asset Registry</a>
    <div class="flex items-center gap-2">
        @can('update', $asset)
        <a href="{{ route('culture.assets.edit', $asset) }}" class="rounded-md bg-emerald-700 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-800">
            Edit Asset (New Revision)
        </a>
        @endcan
        @can('delete', $asset)
        <form method="POST" action="{{ route('culture.assets.destroy', $asset) }}" onsubmit="return confirm('Delete this cultural asset record?')">
            @csrf @method('DELETE')
            <button type="submit" class="rounded-md border border-rose-300 bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-800 hover:bg-rose-100">Delete</button>
        </form>
        @endcan
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 rounded-lg bg-white border border-slate-200 shadow-sm p-6 space-y-4">
        <div class="flex items-center justify-between">
            <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $asset->condition_assessment->badgeColor() }}">
                {{ $asset->condition_assessment->label() }}
            </span>
            <span class="text-xs font-mono text-slate-500">Revision v{{ $asset->current_version }}</span>
        </div>

        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
            <div>
                <dt class="text-xs uppercase text-slate-500">Category</dt>
                <dd class="mt-1 font-medium text-slate-900">{{ $asset->category?->name }}</dd>
            </div>
            <div>
                <dt class="text-xs uppercase text-slate-500">LGA / Senatorial District</dt>
                <dd class="mt-1 font-medium text-slate-900">{{ $asset->lga?->name }} ({{ $asset->lga?->senatorial_district }})</dd>
            </div>
            <div>
                <dt class="text-xs uppercase text-slate-500">Ownership Status</dt>
                <dd class="mt-1 font-medium text-slate-900">{{ $asset->ownership_status?->label() }}</dd>
            </div>
            <div>
                <dt class="text-xs uppercase text-slate-500">Visitor Capacity</dt>
                <dd class="mt-1 font-medium text-slate-900">{{ $asset->visitor_capacity ? number_format($asset->visitor_capacity) : 'Not specified' }}</dd>
            </div>
            <div class="sm:col-span-2">
                <dt class="text-xs uppercase text-slate-500">GPS Coordinates (SRID 4326)</dt>
                <dd class="mt-1 font-mono text-xs text-slate-800">
                    {{ $asset->location }}
                    @if($asset->is_approximate_location)
                        <span class="ml-2 rounded bg-amber-100 px-2 py-0.5 text-amber-800">Approximate (LGA Centroid)</span>
                    @endif
                </dd>
            </div>
        </dl>

        <div class="pt-3 border-t border-slate-200">
            <h3 class="text-xs font-semibold uppercase text-slate-500">Description</h3>
            <p class="mt-1 text-sm text-slate-700 whitespace-pre-line">{{ $asset->description ?: 'No description recorded.' }}</p>
        </div>
    </div>

    <div class="rounded-lg bg-white border border-slate-200 shadow-sm p-5 space-y-3">
        <h3 class="text-sm font-semibold text-slate-900">Revision History ({{ $asset->versions->count() }})</h3>
        <div class="space-y-3 max-h-96 overflow-y-auto">
            @foreach($asset->versions as $version)
            <div class="rounded-md border border-slate-200 p-3 text-xs" x-data="{ open: false }">
                <div class="flex items-center justify-between">
                    <span class="font-semibold text-slate-900">v{{ $version->version_number }}</span>
                    <span class="text-slate-500">{{ \App\Support\Format::datetime($version->created_at) }}</span>
                </div>
                <p class="mt-1 text-slate-700">{{ $version->change_summary }}</p>
                <p class="text-slate-500">By: {{ $version->changedBy?->name ?? 'System' }}</p>
                <button type="button" @click="open = !open" class="mt-1.5 text-emerald-700 font-medium hover:underline">
                    <span x-text="open ? 'Hide Snapshot' : 'Inspect Snapshot'"></span>
                </button>
                <pre x-show="open" x-cloak class="mt-2 rounded bg-slate-900 text-slate-100 p-2 text-[11px] overflow-x-auto">{{ json_encode($version->snapshot, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
            </div>
            @endforeach
        </div>
    </div>
</div>

<x-documents-panel :documents="$asset->documents" documentable-type="cultural_asset" :documentable-id="$asset->id" />

<x-notes-panel :notes="$asset->notes" notable-type="cultural_asset" :notable-id="$asset->id" />
@endsection
