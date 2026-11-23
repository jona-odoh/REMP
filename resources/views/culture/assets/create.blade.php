@extends('layouts.app')

@section('title', 'Register Cultural Asset')
@section('heading', 'Register Cultural or Tourism Asset')

@section('content')
<div class="max-w-3xl rounded-lg bg-white border border-slate-200 shadow-sm p-6">
    <form method="POST" action="{{ route('culture.assets.store') }}" x-data="formAutosave('ksmct_cultural_asset_create')" class="space-y-4">
        @csrf

        <div x-show="restored" x-cloak class="rounded-md bg-sky-50 border border-sky-200 px-3 py-2 text-xs text-sky-900 flex items-center justify-between">
            <span>Unsaved draft restored from browser storage (offline protection).</span>
            <button type="button" @click="clearDraft()" class="font-semibold underline">Dismiss</button>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
                <label for="name" class="block text-sm font-medium text-slate-700">Asset Name *</label>
                <input id="name" name="name" type="text" value="{{ old('name') }}" required
                       class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
            </div>

            <div>
                <label for="category_id" class="block text-sm font-medium text-slate-700">Category *</label>
                <select id="category_id" name="category_id" required class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                    <option value="">Select Category</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" @selected((string) old('category_id') === (string) $cat->id)>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="lga_id" class="block text-sm font-medium text-slate-700">Local Government Area (LGA) *</label>
                <select id="lga_id" name="lga_id" required class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                    <option value="">Select LGA</option>
                    @foreach($lgas as $lga)
                        <option value="{{ $lga->id }}" @selected((string) old('lga_id') === (string) $lga->id)>{{ $lga->name }} ({{ $lga->senatorial_district }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="latitude" class="block text-sm font-medium text-slate-700">GPS Latitude (WGS84 / SRID 4326)</label>
                <input id="latitude" name="latitude" type="number" step="0.0000001" value="{{ old('latitude') }}" placeholder="e.g. 7.8023000"
                       class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                <p class="mt-1 text-xs text-slate-500">Leave blank to default to the selected LGA centroid.</p>
            </div>

            <div>
                <label for="longitude" class="block text-sm font-medium text-slate-700">GPS Longitude (WGS84 / SRID 4326)</label>
                <input id="longitude" name="longitude" type="number" step="0.0000001" value="{{ old('longitude') }}" placeholder="e.g. 6.7333000"
                       class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
            </div>

            <x-gps-picker :latitude="old('latitude')" :longitude="old('longitude')" :lgas="$lgas" />

            <div>
                <label for="ownership_status" class="block text-sm font-medium text-slate-700">Ownership Status *</label>
                <select id="ownership_status" name="ownership_status" required class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                    @foreach($ownerships as $own)
                        <option value="{{ $own->value }}" @selected(old('ownership_status') === $own->value)>{{ $own->label() }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="condition_assessment" class="block text-sm font-medium text-slate-700">Condition Assessment *</label>
                <select id="condition_assessment" name="condition_assessment" required class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                    @foreach($conditions as $cond)
                        <option value="{{ $cond->value }}" @selected(old('condition_assessment') === $cond->value)>{{ $cond->label() }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="visitor_capacity" class="block text-sm font-medium text-slate-700">Estimated Visitor Capacity</label>
                <input id="visitor_capacity" name="visitor_capacity" type="number" min="0" value="{{ old('visitor_capacity') }}"
                       class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
            </div>

            <div class="sm:col-span-2">
                <label for="description" class="block text-sm font-medium text-slate-700">Historical &amp; Site Description</label>
                <textarea id="description" name="description" rows="4"
                          class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">{{ old('description') }}</textarea>
            </div>
        </div>

        <div class="pt-3 flex items-center gap-3">
            <button type="submit" class="rounded-md bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-800">
                Save Asset Record
            </button>
            <a href="{{ route('culture.assets.index') }}" class="text-sm text-slate-600 hover:text-slate-900">Cancel</a>
        </div>
    </form>
</div>
@endsection
