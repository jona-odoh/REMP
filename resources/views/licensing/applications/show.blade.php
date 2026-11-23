@extends('layouts.app')

@section('title', $application->application_number)
@section('heading', 'License Application '.$application->application_number)

@section('content')
<div class="flex flex-wrap items-center justify-between gap-2">
    <a href="{{ route('licensing.applications.index') }}" class="text-sm font-medium text-emerald-700 hover:text-emerald-900">← Back to Licensing Pipeline</a>
    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $application->status->badgeColor() }}">
        Stage: {{ $application->status->label() }}
    </span>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 rounded-lg bg-white border border-slate-200 shadow-sm p-6 space-y-4">
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
            <div><dt class="text-xs uppercase text-slate-500">Hotel</dt><dd class="mt-1 font-semibold"><a href="{{ route('licensing.hotels.show', $application->hotel) }}" class="text-emerald-800 hover:underline">{{ $application->hotel->name }}</a></dd></div>
            <div><dt class="text-xs uppercase text-slate-500">License Type</dt><dd class="mt-1 font-medium">{{ $application->licenseType?->name }} ({{ $application->application_type }})</dd></div>
            <div><dt class="text-xs uppercase text-slate-500">Assigned Officer</dt><dd class="mt-1 font-medium">{{ $application->assignedOfficer?->name ?? 'Unassigned' }}</dd></div>
            <div><dt class="text-xs uppercase text-slate-500">License Number</dt><dd class="mt-1 font-mono text-xs">{{ $application->license_number ?? 'Not yet issued' }}</dd></div>
            <div><dt class="text-xs uppercase text-slate-500">Issued / Expires</dt><dd class="mt-1">{{ \App\Support\Format::date($application->issued_at) }} → {{ \App\Support\Format::date($application->expires_at) }}</dd></div>
            <div><dt class="text-xs uppercase text-slate-500">Completed Inspection?</dt><dd class="mt-1 font-semibold {{ $application->hasCompletedInspection() ? 'text-emerald-700' : 'text-amber-700' }}">{{ $application->hasCompletedInspection() ? 'Yes — Eligible for Decision' : 'No — Required before Approval' }}</dd></div>
        </dl>

        @if($application->decision_reason)
        <div class="rounded-md bg-slate-50 border border-slate-200 p-3 text-sm">
            <p class="text-xs font-semibold uppercase text-slate-500">Decision Reason ({{ $application->decidedBy?->name }} · {{ \App\Support\Format::datetime($application->decided_at) }})</p>
            <p class="mt-1 text-slate-800">{{ $application->decision_reason }}</p>
        </div>
        @endif
    </div>

    <div class="rounded-lg bg-white border border-slate-200 shadow-sm p-5 space-y-4">
        <h3 class="text-sm font-semibold text-slate-900">Workflow Actions</h3>

        @if($application->status === \App\Enums\LicenseApplicationStatus::DRAFT)
            @can('create', \App\Models\LicenseApplication::class)
            <form method="POST" action="{{ route('licensing.applications.submit', $application) }}">
                @csrf
                <button type="submit" class="w-full rounded-md bg-sky-700 px-3 py-2 text-xs font-semibold text-white hover:bg-sky-800">Submit Application</button>
            </form>
            @endcan
        @endif

        @if($application->status === \App\Enums\LicenseApplicationStatus::SUBMITTED)
            @can('review', $application)
            <form method="POST" action="{{ route('licensing.applications.review', $application) }}">
                @csrf
                <button type="submit" class="w-full rounded-md bg-indigo-700 px-3 py-2 text-xs font-semibold text-white hover:bg-indigo-800">Start Officer Review</button>
            </form>
            @endcan
        @endif

        @if($application->status === \App\Enums\LicenseApplicationStatus::UNDER_REVIEW)
            @can('create', \App\Models\Inspection::class)
            <form method="POST" action="{{ route('licensing.inspections.store') }}" class="space-y-2 border-t border-slate-200 pt-3">
                @csrf
                <input type="hidden" name="hotel_id" value="{{ $application->hotel_id }}">
                <input type="hidden" name="license_application_id" value="{{ $application->id }}">
                <p class="text-xs font-semibold text-slate-700">Schedule Field Inspection</p>
                <select name="assigned_officer_id" required class="block w-full rounded-md border border-slate-300 px-2.5 py-1.5 text-xs">
                    @foreach($officers as $off)
                        <option value="{{ $off->id }}">{{ $off->name }}</option>
                    @endforeach
                </select>
                <input name="scheduled_date" type="date" value="{{ now()->addDays(3)->toDateString() }}" required class="block w-full rounded-md border border-slate-300 px-2.5 py-1.5 text-xs">
                <button type="submit" class="w-full rounded-md bg-amber-600 px-3 py-2 text-xs font-semibold text-white hover:bg-amber-700">Schedule Inspection</button>
            </form>
            @endcan
        @endif

        @if(in_array($application->status, [\App\Enums\LicenseApplicationStatus::INSPECTION_COMPLETED, \App\Enums\LicenseApplicationStatus::UNDER_REVIEW, \App\Enums\LicenseApplicationStatus::SUBMITTED], true))
            @can('approve', $application)
            <div class="space-y-3 border-t border-slate-200 pt-3">
                @if($application->status === \App\Enums\LicenseApplicationStatus::INSPECTION_COMPLETED)
                <form method="POST" action="{{ route('licensing.applications.approve', $application) }}" class="space-y-2">
                    @csrf
                    <textarea name="decision_reason" rows="2" required placeholder="Mandatory approval reason..." class="block w-full rounded-md border border-slate-300 px-2.5 py-1.5 text-xs"></textarea>
                    <button type="submit" class="w-full rounded-md bg-emerald-700 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-800">Approve &amp; Issue License</button>
                </form>
                @endif
                <form method="POST" action="{{ route('licensing.applications.reject', $application) }}" class="space-y-2">
                    @csrf
                    <textarea name="decision_reason" rows="2" required placeholder="Mandatory rejection reason..." class="block w-full rounded-md border border-slate-300 px-2.5 py-1.5 text-xs"></textarea>
                    <button type="submit" class="w-full rounded-md bg-rose-700 px-3 py-2 text-xs font-semibold text-white hover:bg-rose-800">Reject Application</button>
                </form>
            </div>
            @endcan
        @endif

        @if(in_array($application->status, [\App\Enums\LicenseApplicationStatus::APPROVED, \App\Enums\LicenseApplicationStatus::EXPIRED], true))
            @can('create', \App\Models\LicenseApplication::class)
            <form method="POST" action="{{ route('licensing.applications.renew', $application) }}">
                @csrf
                <button type="submit" class="w-full rounded-md bg-purple-700 px-3 py-2 text-xs font-semibold text-white hover:bg-purple-800">Initiate Renewal Application</button>
            </form>
            @endcan
        @endif
    </div>
</div>

<x-documents-panel :documents="$application->documents" documentable-type="license_application" :documentable-id="$application->id" :categories="['Application Form', 'CAC Certificate', 'Tax Clearance', 'Fire Safety Cert', 'Sanitation Cert']" />
@endsection
