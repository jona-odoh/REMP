@extends('layouts.app')

@section('title', $hotel->name)
@section('heading', $hotel->name)

@section('content')
<div class="flex flex-wrap items-center justify-between gap-2">
    <a href="{{ route('licensing.hotels.index') }}" class="text-sm font-medium text-emerald-700 hover:text-emerald-900">← Back to Hotel Registry</a>
    <div class="flex items-center gap-2">
        @can('create', \App\Models\LicenseApplication::class)
        <a href="{{ route('licensing.applications.create', ['hotel_id' => $hotel->id]) }}" class="rounded-md bg-emerald-700 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-800">
            + New License Application
        </a>
        @endcan
        @can('update', $hotel)
        <a href="{{ route('licensing.hotels.edit', $hotel) }}" class="rounded-md border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">Edit</a>
        @endcan
    </div>
</div>

<div class="rounded-lg bg-white border border-slate-200 shadow-sm p-6">
    <div class="flex items-center justify-between mb-4">
        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $hotel->license_status->badgeColor() }}">
            {{ $hotel->license_status->label() }}
        </span>
        <span class="text-xs font-mono text-slate-500">GPS: {{ $hotel->location }}</span>
    </div>

    <dl class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
        <div><dt class="text-xs uppercase text-slate-500">Address</dt><dd class="mt-1 font-medium">{{ $hotel->address }}</dd></div>
        <div><dt class="text-xs uppercase text-slate-500">LGA</dt><dd class="mt-1 font-medium">{{ $hotel->lga?->name }} ({{ $hotel->lga?->senatorial_district }})</dd></div>
        <div><dt class="text-xs uppercase text-slate-500">Type &amp; Classification</dt><dd class="mt-1 font-medium">{{ $hotel->establishment_type }} · {{ $hotel->classification_rating }}</dd></div>
        <div><dt class="text-xs uppercase text-slate-500">Capacity</dt><dd class="mt-1 font-medium">{{ $hotel->capacity_rooms }} rooms / {{ $hotel->capacity_beds }} beds</dd></div>
        <div><dt class="text-xs uppercase text-slate-500">Proprietor</dt><dd class="mt-1 font-medium">{{ $hotel->owner_name }}</dd></div>
        <div><dt class="text-xs uppercase text-slate-500">Contact</dt><dd class="mt-1 font-medium">{{ $hotel->owner_contact }}</dd></div>
    </dl>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="rounded-lg bg-white border border-slate-200 shadow-sm p-5">
        <h3 class="text-sm font-semibold text-slate-900 mb-3">License Applications ({{ $hotel->licenseApplications->count() }})</h3>
        <div class="space-y-2 text-sm">
            @forelse($hotel->licenseApplications as $app)
            <a href="{{ route('licensing.applications.show', $app) }}" class="block rounded-md border border-slate-200 p-3 hover:bg-slate-50">
                <div class="flex items-center justify-between">
                    <span class="font-mono text-xs font-semibold text-emerald-800">{{ $app->application_number }}</span>
                    <span class="rounded-full px-2 py-0.5 text-xs font-semibold {{ $app->status->badgeColor() }}">{{ $app->status->label() }}</span>
                </div>
                <div class="mt-1 text-xs text-slate-600">{{ $app->licenseType?->name }} · {{ $app->application_type }} @if($app->expires_at) · Expires {{ \App\Support\Format::date($app->expires_at) }} @endif</div>
            </a>
            @empty
            <p class="text-xs text-slate-500">No applications filed yet.</p>
            @endforelse
        </div>
    </div>

    <div class="rounded-lg bg-white border border-slate-200 shadow-sm p-5">
        <h3 class="text-sm font-semibold text-slate-900 mb-3">Fee Payment History ({{ $hotel->licenseFees->count() }})</h3>
        <div class="space-y-2 text-xs">
            @forelse($hotel->licenseFees as $fee)
            <div class="flex items-center justify-between rounded-md border border-slate-200 p-3">
                <div>
                    <div class="font-semibold text-slate-900">{{ \App\Support\Format::naira($fee->amount) }} — {{ $fee->period }}</div>
                    <div class="text-slate-500 font-mono">Receipt: {{ $fee->receipt_reference }}</div>
                </div>
                <span class="text-slate-600">{{ \App\Support\Format::date($fee->paid_at) }}</span>
            </div>
            @empty
            <p class="text-xs text-slate-500">No license fees recorded yet.</p>
            @endforelse
        </div>
    </div>
</div>

<x-documents-panel :documents="$hotel->documents" documentable-type="hotel" :documentable-id="$hotel->id" />

<x-notes-panel :notes="$hotel->notes" notable-type="hotel" :notable-id="$hotel->id" />
@endsection
