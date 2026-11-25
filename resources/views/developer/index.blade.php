@extends('layouts.app')

@section('title', 'Developer Information')
@section('heading', 'Developer & System Information')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Developer Profile Card -->
    <div class="lg:col-span-2 rounded-lg bg-white border border-slate-200 shadow-sm p-6 space-y-5">
        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-200 pb-4">
            <div>
                <span class="inline-block rounded-full bg-emerald-100 px-3 py-0.5 text-xs font-semibold text-emerald-800">
                    System Developer &amp; Maintainer
                </span>
                <h3 class="mt-2 text-xl font-bold text-slate-900">{{ $developer['name'] }}</h3>
                <p class="text-sm text-slate-600">{{ $developer['title'] }}</p>
            </div>
            <span class="rounded-md bg-slate-100 px-3 py-1 text-xs font-mono font-semibold text-slate-700">
                v{{ $system['app_version'] }}
            </span>
        </div>

        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Organisation / Unit</dt>
                <dd class="mt-1 font-medium text-slate-900">{{ $developer['organization'] }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Location</dt>
                <dd class="mt-1 font-medium text-slate-900">{{ $developer['location'] }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Technical Support Email</dt>
                <dd class="mt-1">
                    <a href="mailto:{{ $developer['email'] }}" class="font-medium text-emerald-700 hover:underline">
                        {{ $developer['email'] }}
                    </a>
                </dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Support Phone</dt>
                <dd class="mt-1 font-medium text-slate-900">{{ $developer['phone'] }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Support Hours</dt>
                <dd class="mt-1 text-slate-700">{{ $developer['support_hours'] }}</dd>
            </div>
            @if(!empty($developer['website']) || !empty($developer['github']))
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Links</dt>
                <dd class="mt-1 flex flex-wrap gap-3">
                    @if(!empty($developer['website']))
                        <a href="{{ $developer['website'] }}" target="_blank" rel="noopener noreferrer" class="font-medium text-emerald-700 hover:underline">Website</a>
                    @endif
                    @if(!empty($developer['github']))
                        <a href="{{ $developer['github'] }}" target="_blank" rel="noopener noreferrer" class="font-medium text-emerald-700 hover:underline">GitHub</a>
                    @endif
                </dd>
            </div>
            @endif
        </dl>

        <div class="rounded-md bg-slate-50 border border-slate-200 p-4 text-xs text-slate-700 space-y-1">
            <p class="font-semibold text-slate-900">Technical Support &amp; Change Requests</p>
            <p>For account unlocks, new LGA or fee schedule updates, backup/restore assistance, or bug reports, contact the Ministry ICT &amp; Engineering support desk via the email or phone above. Quote your Staff ID and the page URL when reporting an issue.</p>
        </div>
    </div>

    <!-- System Runtime Metadata Card -->
    <div class="rounded-lg bg-white border border-slate-200 shadow-sm p-6 space-y-4">
        <h3 class="text-sm font-bold uppercase tracking-wider text-slate-700 border-b border-slate-200 pb-2">
            System Runtime Profile
        </h3>
        <dl class="space-y-3 text-xs">
            <div class="flex justify-between gap-2">
                <dt class="text-slate-500">Application</dt>
                <dd class="font-semibold text-slate-900 text-right">{{ $system['app_name'] }}</dd>
            </div>
            <div class="flex justify-between gap-2">
                <dt class="text-slate-500">Framework</dt>
                <dd class="font-mono font-semibold text-slate-900">Laravel {{ $system['laravel_version'] }}</dd>
            </div>
            <div class="flex justify-between gap-2">
                <dt class="text-slate-500">PHP Runtime</dt>
                <dd class="font-mono font-semibold text-slate-900">PHP {{ $system['php_version'] }}</dd>
            </div>
            <div class="flex justify-between gap-2">
                <dt class="text-slate-500">Database Driver</dt>
                <dd class="font-mono font-semibold text-slate-900">{{ $system['database_driver'] }}</dd>
            </div>
            <div class="flex justify-between gap-2">
                <dt class="text-slate-500">Timezone / Locale</dt>
                <dd class="font-mono text-slate-800">{{ $system['timezone'] }} ({{ $system['locale'] }})</dd>
            </div>
            <div class="pt-2 border-t border-slate-100">
                <dt class="text-slate-500">Storage &amp; Sovereignty</dt>
                <dd class="mt-1 inline-flex rounded-full bg-emerald-50 border border-emerald-200 px-2.5 py-0.5 font-semibold text-emerald-800">
                    {{ $system['storage_mode'] }}
                </dd>
            </div>
        </dl>
    </div>
</div>

<!-- Delivered Modules Overview -->
<div class="rounded-lg bg-white border border-slate-200 shadow-sm overflow-hidden">
    <div class="px-5 py-3.5 border-b border-slate-200 bg-slate-50">
        <h3 class="text-sm font-semibold text-slate-900">Implemented System Modules (Phases 1–6)</h3>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 divide-y md:divide-y-0 md:gap-px bg-slate-200">
        @foreach($modules as $mod)
        <div class="bg-white p-4 space-y-1">
            <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-700">{{ $mod['phase'] }}</span>
            <h4 class="text-sm font-semibold text-slate-900">{{ $mod['name'] }}</h4>
            <p class="text-xs text-slate-600 leading-relaxed">{{ $mod['details'] }}</p>
        </div>
        @endforeach
    </div>
</div>
@endsection
