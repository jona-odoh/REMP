@extends('layouts.app')

@section('title', 'Edit '.$hotel->name)
@section('heading', 'Edit Establishment: '.$hotel->name)

@section('content')
<div class="max-w-3xl rounded-lg bg-white border border-slate-200 shadow-sm p-6">
    <form method="POST" action="{{ route('licensing.hotels.update', $hotel) }}" class="space-y-4">
        @csrf @method('PUT')

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
                <label for="name" class="block text-sm font-medium text-slate-700">Establishment Name *</label>
                <input id="name" name="name" type="text" value="{{ old('name', $hotel->name) }}" required class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div class="sm:col-span-2">
                <label for="address" class="block text-sm font-medium text-slate-700">Street Address *</label>
                <input id="address" name="address" type="text" value="{{ old('address', $hotel->address) }}" required class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label for="lga_id" class="block text-sm font-medium text-slate-700">LGA *</label>
                <select id="lga_id" name="lga_id" required class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                    @foreach($lgas as $lga)
                        <option value="{{ $lga->id }}" @selected((string) old('lga_id', $hotel->lga_id) === (string) $lga->id)>{{ $lga->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="establishment_type" class="block text-sm font-medium text-slate-700">Establishment Type *</label>
                <select id="establishment_type" name="establishment_type" required class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                    @foreach($establishmentTypes as $type)
                        <option value="{{ $type }}" @selected(old('establishment_type', $hotel->establishment_type) === $type)>{{ $type }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="classification_rating" class="block text-sm font-medium text-slate-700">Classification *</label>
                <select id="classification_rating" name="classification_rating" required class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                    @foreach($classifications as $cls)
                        <option value="{{ $cls }}" @selected(old('classification_rating', $hotel->classification_rating) === $cls)>{{ $cls }}</option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="capacity_rooms" class="block text-sm font-medium text-slate-700">Rooms *</label>
                    <input id="capacity_rooms" name="capacity_rooms" type="number" min="1" value="{{ old('capacity_rooms', $hotel->capacity_rooms) }}" required class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label for="capacity_beds" class="block text-sm font-medium text-slate-700">Beds *</label>
                    <input id="capacity_beds" name="capacity_beds" type="number" min="1" value="{{ old('capacity_beds', $hotel->capacity_beds) }}" required class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                </div>
            </div>
            <div>
                <label for="owner_name" class="block text-sm font-medium text-slate-700">Owner Name *</label>
                <input id="owner_name" name="owner_name" type="text" value="{{ old('owner_name', $hotel->owner_name) }}" required class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label for="owner_contact" class="block text-sm font-medium text-slate-700">Owner Contact *</label>
                <input id="owner_contact" name="owner_contact" type="text" value="{{ old('owner_contact', $hotel->owner_contact) }}" required class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label for="latitude" class="block text-sm font-medium text-slate-700">GPS Latitude</label>
                <input id="latitude" name="latitude" type="number" step="0.0000001" value="{{ old('latitude', $hotel->location?->latitude) }}" class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label for="longitude" class="block text-sm font-medium text-slate-700">GPS Longitude</label>
                <input id="longitude" name="longitude" type="number" step="0.0000001" value="{{ old('longitude', $hotel->location?->longitude) }}" class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
            </div>
        </div>

        <div class="pt-3 flex items-center gap-3">
            <button type="submit" class="rounded-md bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-800">Save Changes</button>
            <a href="{{ route('licensing.hotels.show', $hotel) }}" class="text-sm text-slate-600 hover:text-slate-900">Cancel</a>
        </div>
    </form>
</div>
@endsection
