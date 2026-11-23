<!DOCTYPE html>
<html lang="en-NG">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — {{ __('ksmct.short_name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 text-slate-900 antialiased" x-data="{ sidebarOpen: false }">
    <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:top-2 focus:left-2 focus:z-50 focus:rounded focus:bg-emerald-800 focus:px-4 focus:py-2 focus:text-white">
        Skip to main content
    </a>

    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <aside
            class="fixed inset-y-0 left-0 z-40 w-64 bg-slate-900 text-slate-100 transform transition-transform duration-200 lg:static lg:translate-x-0 flex flex-col"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
            aria-label="Primary Ministry Navigation"
        >
            <div class="px-5 py-4 border-b border-slate-800">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="inline-block rounded bg-emerald-700 px-2 py-0.5 text-xs font-semibold uppercase tracking-wider text-white">Kogi State</span>
                        <h1 class="mt-1 text-sm font-bold leading-snug text-white">Ministry of Culture &amp; Tourism</h1>
                        <p class="text-xs text-slate-400">Internal Back-Office</p>
                    </div>
                    <button type="button" class="lg:hidden text-slate-300 hover:text-white" @click="sidebarOpen = false" aria-label="Close navigation menu">
                        ✕
                    </button>
                </div>
            </div>

            <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-6 text-sm">
                <div>
                    <p class="px-3 text-xs font-semibold uppercase tracking-wider text-slate-400">Workspace</p>
                    <div class="mt-2 space-y-1">
                        <a href="{{ route('dashboard') }}"
                           class="flex items-center rounded-md px-3 py-2 font-medium {{ request()->routeIs('dashboard') ? 'bg-emerald-700 text-white' : 'text-slate-200 hover:bg-slate-800' }}">
                            Overview Dashboard
                        </a>
                    </div>
                </div>

                @if(auth()->user()->can('viewAny', \App\Models\CulturalAsset::class) || auth()->user()->can('viewAny', \App\Models\IntangibleHeritage::class))
                <div>
                    <p class="px-3 text-xs font-semibold uppercase tracking-wider text-slate-400">Culture &amp; Tourism</p>
                    <div class="mt-2 space-y-1">
                        @can('viewAny', \App\Models\CulturalAsset::class)
                        <a href="{{ route('culture.assets.index') }}"
                           class="flex items-center rounded-md px-3 py-2 font-medium {{ request()->routeIs('culture.assets.index', 'culture.assets.create', 'culture.assets.show', 'culture.assets.edit', 'culture.assets.import*') ? 'bg-emerald-700 text-white' : 'text-slate-200 hover:bg-slate-800' }}">
                            Cultural &amp; Tourism Assets
                        </a>
                        <a href="{{ route('culture.assets.map') }}"
                           class="flex items-center rounded-md px-3 py-2 font-medium {{ request()->routeIs('culture.assets.map') ? 'bg-emerald-700 text-white' : 'text-slate-200 hover:bg-slate-800' }}">
                            Interactive Asset Map
                        </a>
                        @endcan
                        @can('viewAny', \App\Models\IntangibleHeritage::class)
                        <a href="{{ route('culture.heritage.index') }}"
                           class="flex items-center rounded-md px-3 py-2 font-medium {{ request()->routeIs('culture.heritage.*') ? 'bg-emerald-700 text-white' : 'text-slate-200 hover:bg-slate-800' }}">
                            Intangible Heritage
                        </a>
                        @endcan
                    </div>
                </div>
                @endif

                @can('viewAny', \App\Models\Hotel::class)
                <div>
                    <p class="px-3 text-xs font-semibold uppercase tracking-wider text-slate-400">Hospitality Licensing</p>
                    <div class="mt-2 space-y-1">
                        <a href="{{ route('licensing.compliance.index') }}"
                           class="flex items-center rounded-md px-3 py-2 font-medium {{ request()->routeIs('licensing.compliance.*') ? 'bg-emerald-700 text-white' : 'text-slate-200 hover:bg-slate-800' }}">
                            Compliance Dashboard
                        </a>
                        <a href="{{ route('licensing.hotels.index') }}"
                           class="flex items-center rounded-md px-3 py-2 font-medium {{ request()->routeIs('licensing.hotels.*') ? 'bg-emerald-700 text-white' : 'text-slate-200 hover:bg-slate-800' }}">
                            Hotel Registry
                        </a>
                        <a href="{{ route('licensing.applications.index') }}"
                           class="flex items-center rounded-md px-3 py-2 font-medium {{ request()->routeIs('licensing.applications.*') ? 'bg-emerald-700 text-white' : 'text-slate-200 hover:bg-slate-800' }}">
                            License Pipeline
                        </a>
                        <a href="{{ route('licensing.inspections.index') }}"
                           class="flex items-center rounded-md px-3 py-2 font-medium {{ request()->routeIs('licensing.inspections.*') ? 'bg-emerald-700 text-white' : 'text-slate-200 hover:bg-slate-800' }}">
                            Inspections
                        </a>
                        <a href="{{ route('licensing.fees.index') }}"
                           class="flex items-center rounded-md px-3 py-2 font-medium {{ request()->routeIs('licensing.fees.*') ? 'bg-emerald-700 text-white' : 'text-slate-200 hover:bg-slate-800' }}">
                            Fee &amp; Revenue Tracker
                        </a>
                    </div>
                </div>
                @endcan

                @can('viewAny', \App\Models\Project::class)
                <div>
                    <p class="px-3 text-xs font-semibold uppercase tracking-wider text-slate-400">Projects &amp; Programs</p>
                    <div class="mt-2 space-y-1">
                        <a href="{{ route('projects.index') }}"
                           class="flex items-center rounded-md px-3 py-2 font-medium {{ request()->routeIs('projects.*') ? 'bg-emerald-700 text-white' : 'text-slate-200 hover:bg-slate-800' }}">
                            Project Registry &amp; Budgets
                        </a>
                    </div>
                </div>
                @endcan

                @if(auth()->user()->can('reports.view'))
                <div>
                    <p class="px-3 text-xs font-semibold uppercase tracking-wider text-slate-400">Reporting &amp; Analytics</p>
                    <div class="mt-2 space-y-1">
                        <a href="{{ route('reports.kpi') }}"
                           class="flex items-center rounded-md px-3 py-2 font-medium {{ request()->routeIs('reports.kpi') ? 'bg-emerald-700 text-white' : 'text-slate-200 hover:bg-slate-800' }}">
                            Executive KPI Dashboard
                        </a>
                        <a href="{{ route('reports.standard') }}"
                           class="flex items-center rounded-md px-3 py-2 font-medium {{ request()->routeIs('reports.standard*') ? 'bg-emerald-700 text-white' : 'text-slate-200 hover:bg-slate-800' }}">
                            Standard Reports (PDF/Excel)
                        </a>
                        <a href="{{ route('reports.query-builder') }}"
                           class="flex items-center rounded-md px-3 py-2 font-medium {{ request()->routeIs('reports.query-builder') ? 'bg-emerald-700 text-white' : 'text-slate-200 hover:bg-slate-800' }}">
                            Ad-Hoc Query Builder
                        </a>
                    </div>
                </div>
                @endif

                <div>
                    <p class="px-3 text-xs font-semibold uppercase tracking-wider text-slate-400">Operations</p>
                    <div class="mt-2 space-y-1">
                        @can('viewAny', \App\Models\Document::class)
                        <a href="{{ route('documents.index') }}"
                           class="flex items-center rounded-md px-3 py-2 font-medium {{ request()->routeIs('documents.*') ? 'bg-emerald-700 text-white' : 'text-slate-200 hover:bg-slate-800' }}">
                            Document Repository
                        </a>
                        @endcan
                        @can('viewAny', \App\Models\Task::class)
                        <a href="{{ route('tasks.index') }}"
                           class="flex items-center rounded-md px-3 py-2 font-medium {{ request()->routeIs('tasks.*') ? 'bg-emerald-700 text-white' : 'text-slate-200 hover:bg-slate-800' }}">
                            Tasks &amp; Assignments
                        </a>
                        @endcan
                        @php($unreadCount = auth()->user()->unreadNotifications()->count())
                        <a href="{{ route('notifications.index') }}"
                           class="flex items-center justify-between rounded-md px-3 py-2 font-medium {{ request()->routeIs('notifications.*') ? 'bg-emerald-700 text-white' : 'text-slate-200 hover:bg-slate-800' }}">
                            <span>Notifications</span>
                            @if($unreadCount > 0)
                                <span class="rounded-full bg-rose-600 px-2 py-0.5 text-[10px] font-bold text-white">{{ $unreadCount }}</span>
                            @endif
                        </a>
                    </div>
                </div>

                @if(auth()->user()->can('viewAny', \App\Models\User::class) || auth()->user()->can('viewAny', \App\Models\AuditLog::class))
                <div>
                    <p class="px-3 text-xs font-semibold uppercase tracking-wider text-slate-400">Administration</p>
                    <div class="mt-2 space-y-1">
                        @can('viewAny', \App\Models\User::class)
                        <a href="{{ route('admin.users.index') }}"
                           class="flex items-center rounded-md px-3 py-2 font-medium {{ request()->routeIs('admin.users.*') ? 'bg-emerald-700 text-white' : 'text-slate-200 hover:bg-slate-800' }}">
                            Staff &amp; Roles
                        </a>
                        @endcan
                        @can('viewAny', \App\Models\AuditLog::class)
                        <a href="{{ route('admin.audit-logs.index') }}"
                           class="flex items-center rounded-md px-3 py-2 font-medium {{ request()->routeIs('admin.audit-logs.*') ? 'bg-emerald-700 text-white' : 'text-slate-200 hover:bg-slate-800' }}">
                            Audit Trail
                        </a>
                        @endcan
                    </div>
                </div>
                @endif

                @can('viewAny', \App\Models\Lga::class)
                <div>
                    <p class="px-3 text-xs font-semibold uppercase tracking-wider text-slate-400">System Configuration</p>
                    <div class="mt-2 space-y-1">
                        <a href="{{ route('config.lgas.index') }}"
                           class="flex items-center rounded-md px-3 py-2 font-medium {{ request()->routeIs('config.lgas.*') ? 'bg-emerald-700 text-white' : 'text-slate-200 hover:bg-slate-800' }}">
                            Kogi State LGAs (21)
                        </a>
                        <a href="{{ route('config.asset-categories.index') }}"
                           class="flex items-center rounded-md px-3 py-2 font-medium {{ request()->routeIs('config.asset-categories.*') ? 'bg-emerald-700 text-white' : 'text-slate-200 hover:bg-slate-800' }}">
                            Asset Categories
                        </a>
                        <a href="{{ route('config.license-types.index') }}"
                           class="flex items-center rounded-md px-3 py-2 font-medium {{ request()->routeIs('config.license-types.*') ? 'bg-emerald-700 text-white' : 'text-slate-200 hover:bg-slate-800' }}">
                            License Types
                        </a>
                        <a href="{{ route('config.fee-schedules.index') }}"
                           class="flex items-center rounded-md px-3 py-2 font-medium {{ request()->routeIs('config.fee-schedules.*') ? 'bg-emerald-700 text-white' : 'text-slate-200 hover:bg-slate-800' }}">
                            Fee Schedules (₦)
                        </a>
                        <a href="{{ route('config.checklist-templates.index') }}"
                           class="flex items-center rounded-md px-3 py-2 font-medium {{ request()->routeIs('config.checklist-templates.*') ? 'bg-emerald-700 text-white' : 'text-slate-200 hover:bg-slate-800' }}">
                            Inspection Checklists
                        </a>
                    </div>
                </div>
                @endcan
            </nav>

            <div class="border-t border-slate-800 p-4 text-xs">
                <p class="font-semibold text-white truncate">{{ auth()->user()->name }}</p>
                <p class="text-emerald-400 truncate">{{ auth()->user()->primaryRoleLabel() }}</p>
                <p class="text-slate-400 truncate">{{ auth()->user()->department?->label() ?? 'Ministry Staff' }}</p>
                <form method="POST" action="{{ route('logout') }}" class="mt-3">
                    @csrf
                    <button type="submit" class="w-full rounded-md bg-slate-800 px-3 py-1.5 text-left font-medium text-slate-200 hover:bg-rose-700 hover:text-white transition">
                        Sign Out
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col min-w-0">
            <header class="bg-white border-b border-slate-200 px-4 sm:px-6 py-3 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <button type="button" class="lg:hidden rounded-md p-2 text-slate-600 hover:bg-slate-100" @click="sidebarOpen = true" aria-label="Open navigation menu">
                        ☰
                    </button>
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">@yield('heading', 'Dashboard')</h2>
                        <p class="text-xs text-slate-500">{{ __('ksmct.internal_notice') }}</p>
                    </div>
                </div>
                <div class="text-right text-xs text-slate-600 hidden sm:block">
                    <div>{{ \App\Support\Format::date(now()) }} (WAT)</div>
                    <div class="font-medium text-slate-800">{{ auth()->user()->staff_id ?? auth()->user()->email }}</div>
                </div>
            </header>

            <main id="main-content" class="flex-1 p-4 sm:p-6 space-y-6">
                @if (session('status'))
                    <div role="status" class="rounded-md border border-emerald-300 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
                        {{ session('status') }}
                    </div>
                @endif

                @if (session('warning'))
                    <div role="alert" class="rounded-md border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                        {{ session('warning') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div role="alert" class="rounded-md border border-rose-300 bg-rose-50 px-4 py-3 text-sm text-rose-900">
                        <p class="font-semibold">Please correct the following errors:</p>
                        <ul class="mt-1 list-disc list-inside space-y-0.5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
