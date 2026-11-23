@extends('layouts.app')

@section('title', 'New License Application')
@section('heading', 'Create Hotel License Application')

@section('content')
<div class="max-w-2xl rounded-lg bg-white border border-slate-200 shadow-sm p-6">
    <form method="POST" action="{{ route('licensing.applications.store') }}" class="space-y-4">
        @csrf

        <div>
            <label for="hotel_id" class="block text-sm font-medium text-slate-700">Establishment *</label>
            <select id="hotel_id" name="hotel_id" required class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                <option value="">Select Hotel / Establishment</option>
                @foreach($hotels as $hotel)
                    <option value="{{ $hotel->id }}" @selected((int) old('hotel_id', $selectedHotelId) === $hotel->id)>
                        {{ $hotel->name }} ({{ $hotel->lga?->name }})
                    </option>
                @endforeach
            </select>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="license_type_id" class="block text-sm font-medium text-slate-700">License Type *</label>
                <select id="license_type_id" name="license_type_id" required class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                    @foreach($licenseTypes as $type)
                        <option value="{{ $type->id }}" @selected((string) old('license_type_id') === (string) $type->id)>{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="application_type" class="block text-sm font-medium text-slate-700">Application Type *</label>
                <select id="application_type" name="application_type" required class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                    <option value="NEW">New License</option>
                    <option value="RENEWAL">Annual Renewal</option>
                </select>
            </div>
        </div>

        <div>
            <label for="assigned_officer_id" class="block text-sm font-medium text-slate-700">Assign Licensing Officer</label>
            <select id="assigned_officer_id" name="assigned_officer_id" class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                <option value="">Unassigned (Review Queue)</option>
                @foreach($officers as $off)
                    <option value="{{ $off->id }}">{{ $off->name }}</option>
                @endforeach
            </select>
        </div>

        <label class="inline-flex items-center gap-2 text-sm text-slate-700">
            <input type="checkbox" name="submit_immediately" value="1" class="rounded border-slate-300 text-emerald-700">
            <span>Submit immediately (move from Draft to Submitted)</span>
        </label>

        <div class="pt-3 flex items-center gap-3">
            <button type="submit" class="rounded-md bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-800">Create Application</button>
            <a href="{{ route('licensing.applications.index') }}" class="text-sm text-slate-600 hover:text-slate-900">Cancel</a>
        </div>
    </form>
</div>
@endsection
