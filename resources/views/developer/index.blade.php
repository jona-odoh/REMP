@extends('layouts.app')

@section('title', 'Developer & Technical Documentation')
@section('heading', 'Developer & Technical Documentation')

@section('content')
<div class="space-y-6">
    <!-- Top Header Row -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex flex-wrap items-center gap-2 mb-1.5">
                <span class="inline-flex rounded-full bg-indigo-50 border border-indigo-200 px-2.5 py-0.5 text-xs font-semibold text-indigo-700">
                    System Architecture
                </span>
                <span class="inline-flex rounded-full bg-emerald-50 border border-emerald-200 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">
                    Production Ready
                </span>
            </div>
            <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Developer &amp; Technical Documentation</h2>
            <p class="mt-1 text-sm text-slate-500">
                Full system architecture specifications, runtime diagnostics, maintainer credentials, and module documentation.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2 shrink-0 print:hidden">
            <a href="{{ route('reports.kpi') }}"
               class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50 transition">
                <span>Executive KPIs</span>
            </a>
            <button type="button" onclick="window.print()"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-indigo-700 transition">
                <span>Print Tech Specs</span>
            </button>
        </div>
    </div>

    <!-- Dark Gradient Developer Hero Banner -->
    <div class="rounded-2xl bg-gradient-to-r from-slate-950 via-indigo-950 to-slate-900 p-6 sm:p-8 text-white shadow-lg border border-indigo-900/50">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-5">
                <!-- Portrait with verified badge -->
                <div class="relative shrink-0">
                    <div class="h-24 w-24 rounded-2xl overflow-hidden border-2 border-indigo-400/60 bg-white shadow-md">
                        <img src="{{ asset($developer['photo_path']) }}"
                             alt="{{ $developer['name'] }}"
                             class="h-full w-full object-cover object-top">
                    </div>
                    <span class="absolute -bottom-1.5 -right-1.5 inline-flex h-6 w-6 items-center justify-center rounded-full bg-emerald-500 text-white text-xs font-bold ring-2 ring-slate-950" title="Verified Lead Architect">
                        ✓
                    </span>
                </div>

                <!-- Developer Identity & Contact -->
                <div class="space-y-1.5">
                    <div class="flex flex-wrap items-center gap-2.5">
                        <h3 class="text-2xl font-bold tracking-tight text-white">{{ $developer['name'] }}</h3>
                        <span class="inline-flex rounded-md bg-indigo-500/30 border border-indigo-400/40 px-2.5 py-0.5 text-xs font-semibold text-indigo-200">
                            {{ $developer['badge'] }}
                        </span>
                    </div>

                    <p class="text-sm text-slate-300">{{ $developer['title'] }}</p>

                    <div class="pt-1 flex flex-wrap items-center gap-x-5 gap-y-1.5 text-xs text-indigo-200">
                        <a href="mailto:{{ $developer['email'] }}" class="inline-flex items-center gap-1.5 hover:text-white transition">
                            <span aria-hidden="true">✉</span>
                            <span>{{ $developer['email'] }}</span>
                        </a>
                        <a href="tel:{{ preg_replace('/\s+/', '', $developer['phone']) }}" class="inline-flex items-center gap-1.5 hover:text-white transition">
                            <span aria-hidden="true">📞</span>
                            <span>{{ $developer['phone'] }}</span>
                        </a>
                    </div>

                    <div class="pt-0.5 text-xs text-slate-400 inline-flex items-center gap-1.5">
                        {{-- <span aria-hidden="true">📍</span> --}}
                        <span>
                            {{ $developer['organization'] }} · {{ $developer['location'] }}</span>
                    </div>
                </div>
            </div>

            <!-- Right-side Release Badge Box -->
            <div class="rounded-xl bg-white/5 border border-white/10 px-5 py-4 text-left lg:text-right backdrop-blur-sm shrink-0">
                <p class="text-[10px] font-semibold uppercase tracking-widest text-indigo-300">MIS Solution Release</p>
                <p class="mt-1 text-lg font-bold text-white">{{ $developer['release_label'] }}</p>
                <p class="mt-1.5 inline-flex items-center gap-1.5 text-xs font-medium text-emerald-400">
                    <span class="h-2 w-2 rounded-full bg-emerald-400 inline-block"></span>
                    <span>{{ $developer['support_status'] }}</span>
                </p>
            </div>
        </div>
    </div>

    <!-- 4 Live Runtime Diagnostic Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="rounded-xl bg-white border border-slate-200 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">PHP Engine</span>
                <span class="rounded bg-indigo-50 px-2 py-0.5 text-[10px] font-bold text-indigo-700">PHP</span>
            </div>
            <p class="mt-3 text-xl font-bold text-slate-900 font-mono">{{ $diagnostics['php_version'] }}</p>
            <p class="mt-1 text-xs text-slate-500">Memory Limit: {{ $diagnostics['memory_limit'] }}</p>
        </div>

        <div class="rounded-xl bg-white border border-slate-200 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Database Engine</span>
                <span class="rounded bg-sky-50 px-2 py-0.5 text-[10px] font-bold text-sky-700">SQL</span>
            </div>
            <p class="mt-3 text-xl font-bold text-slate-900">{{ $diagnostics['db_engine'] }}</p>
            <p class="mt-1 text-xs text-slate-500">{{ $diagnostics['table_count'] }} Schema Tables Active</p>
        </div>

        <div class="rounded-xl bg-white border border-slate-200 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Web Server</span>
                <span class="rounded bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-700">SRV</span>
            </div>
            <p class="mt-3 text-xl font-bold text-slate-900 truncate">{{ $diagnostics['web_server'] }}</p>
            <p class="mt-1 text-xs text-slate-500">Max Upload: {{ $diagnostics['max_upload'] }}</p>
        </div>

        <div class="rounded-xl bg-white border border-slate-200 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">UI &amp; Framework</span>
                <span class="rounded bg-cyan-50 px-2 py-0.5 text-[10px] font-bold text-cyan-700">CSS</span>
            </div>
            <p class="mt-3 text-xl font-bold text-slate-900">Laravel {{ \Illuminate\Support\Str::before($diagnostics['laravel_version'], '.') }} · TW4</p>
            <p class="mt-1 text-xs text-slate-500">Offline Vite Bundle (Alpine · Leaflet · Chart.js)</p>
        </div>
    </div>

    <!-- Implemented System Modules Grid -->
    <div class="rounded-xl bg-white border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-200 bg-slate-50 flex flex-wrap items-center justify-between gap-2">
            <h3 class="text-sm font-semibold text-slate-900">Implemented System Architecture &amp; Modules (Phases 1–6)</h3>
            <span class="text-xs text-slate-500">Timezone: {{ $diagnostics['timezone'] }} · Locale: {{ $diagnostics['locale'] }} · On-Premise Local Storage</span>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-px bg-slate-200">
            @foreach($modules as $mod)
            <div class="bg-white p-4 space-y-1">
                <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-700">{{ $mod['phase'] }}</span>
                <h4 class="text-sm font-semibold text-slate-900">{{ $mod['name'] }}</h4>
                <p class="text-xs text-slate-600 leading-relaxed">{{ $mod['details'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
