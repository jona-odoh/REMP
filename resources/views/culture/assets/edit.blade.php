@extends('layouts.app')

@section('title', 'Edit '.$asset->name)
@section('heading', 'Edit Cultural Asset (Current: v'.$asset->current_version.')')

@section('content')
<div class="max-w-3xl rounded-lg bg-white border border-slate-200 shadow-sm p-6">
    <div class="mb-4 rounded-md bg-amber-50 border border-amber-200 px-3 py-2 text-xs text-amber-900">
        Edits do not overwrite previous versions. Saving this form will create revision <strong>v{{ $asset->current_version + 1 }}</strong> in the asset version history.
    </div>

    <form method="POST" action="{{ route('culture.assets.update', $asset) }}" class="space-y-4">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
                <label for="name" class="block text-sm font-medium text-slate-700">Asset Name *</label>
                <input id="name" name="name" type="text" value="{{ old('name', $asset->name) }}" required
                       class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
            </div>

            <div>
                <label for="category_id" class="block text-sm font-medium text-slate-700">Category *</label>
                <select id="category_id" name="category_id" required class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" @selected((string) old('category_id', $asset->category_id) === (string) $cat->id)>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="lga_id" class="block text-sm font-medium text-slate-700">LGA *</label>
                <select id="lga_id" name="lga_id" required class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                    @foreach($lgas as $lga)
                        <option value="{{ $lga->id }}" @selected((string) old('lga_id', $asset->lga_id) === (string) $lga->id)>{{ $lga->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="latitude" class="block text-sm font-medium text-slate-700">GPS Latitude</label>
                <input id="latitude" name="latitude" type="number" step="0.0000001" value="{{ old('latitude', $asset->location?->latitude) }}"
                       class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
            </div>

            <div>
                <label for="longitude" class="block text-sm font-medium text-slate-700">GPS Longitude</label>
                <input id="longitude" name="longitude" type="number" step="0.0000001" value="{{ old('longitude', $asset->location?->longitude) }}"
                       class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
            </div>

            <div>
                <label for="ownership_status" class="block text-sm font-medium text-slate-700">Ownership Status *</label>
                <select id="ownership_status" name="ownership_status" required class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                    @foreach($ownerships as $own)
                        <option value="{{ $own->value }}" @selected(old('ownership_status', $asset->ownership_status?->value) === $own->value)>{{ $own->label() }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="condition_assessment" class="block text-sm font-medium text-slate-700">Condition Assessment *</label>
                <select id="condition_assessment" name="condition_assessment" required class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                    @foreach($conditions as $cond)
                        <option value="{{ $cond->value }}" @selected(old('condition_assessment', $asset->condition_assessment?->value) === $cond->value)>{{ $cond->label() }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="visitor_capacity" class="block text-sm font-medium text-slate-700">Visitor Capacity</label>
                <input id="visitor_capacity" name="visitor_capacity" type="number" min="0" value="{{ old('visitor_capacity', $asset->visitor_capacity) }}"
                       class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
            </div>

            <div>
                <label for="change_summary" class="block text-sm font-medium text-slate-700">Reason for Update (Version Note)</label>
                <input id="change_summary" name="change_summary" type="text" value="{{ old('change_summary') }}" placeholder="e.g. Updated condition after field inspection"
                       class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
            </div>

            <div class="sm:col-span-2">
                <label for="description" class="block text-sm font-medium text-slate-700">Description</label>
                <textarea id="description" name="description" rows="4"
                          class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">{{ old('description', $asset->description) }}</textarea>
            </div>
        </div>

        <div class="pt-3 flex items-center gap-3">
            <button type="submit" class="rounded-md bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-800">
                Save Revision v{{ $asset->current_version + 1 }}
            </button>
            <a href="{{ route('culture.assets.show', $asset) }}" class="text-sm text-slate-600 hover:text-slate-900">Cancel</a>
        </div>
    </form>
</div>
@endsection
