@extends('layouts.app')

@section('title', 'Staff & Roles')
@section('heading', 'Staff & Role Management')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-wrap items-center gap-2">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, email, staff ID..."
               aria-label="Search staff"
               class="rounded-md border border-slate-300 bg-white px-3 py-1.5 text-sm focus:border-emerald-600 focus:outline-none">
        <select name="role" aria-label="Filter by role" class="rounded-md border border-slate-300 bg-white px-3 py-1.5 text-sm">
            <option value="">All Roles</option>
            @foreach($roles as $role)
                <option value="{{ $role->value }}" @selected(request('role') === $role->value)>{{ $role->label() }}</option>
            @endforeach
        </select>
        <select name="department" aria-label="Filter by department" class="rounded-md border border-slate-300 bg-white px-3 py-1.5 text-sm">
            <option value="">All Departments</option>
            @foreach($departments as $dept)
                <option value="{{ $dept->value }}" @selected(request('department') === $dept->value)>{{ $dept->label() }}</option>
            @endforeach
        </select>
        <select name="status" aria-label="Filter by status" class="rounded-md border border-slate-300 bg-white px-3 py-1.5 text-sm">
            <option value="">All Statuses</option>
            <option value="active" @selected(request('status') === 'active')>Active</option>
            <option value="inactive" @selected(request('status') === 'inactive')>Deactivated</option>
        </select>
        <button type="submit" class="rounded-md bg-slate-800 px-3 py-1.5 text-sm font-medium text-white hover:bg-slate-700">Filter</button>
    </form>

    @can('create', \App\Models\User::class)
    <a href="{{ route('admin.users.create') }}"
       class="inline-flex items-center justify-center rounded-md bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-800">
        + Add Staff Account
    </a>
    @endcan
</div>

<div class="rounded-lg bg-white border border-slate-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-4 py-3">Officer</th>
                    <th class="px-4 py-3">Role &amp; Department</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Last Login</th>
                    <th class="px-4 py-3 text-right">Lifecycle Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($users as $staff)
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-3">
                        <div class="font-semibold text-slate-900">{{ $staff->name }}</div>
                        <div class="text-xs text-slate-500">{{ $staff->email }} @if($staff->staff_id) · {{ $staff->staff_id }} @endif</div>
                    </td>
                    <td class="px-4 py-3">
                        <div class="font-medium text-slate-800">{{ $staff->primaryRoleLabel() }}</div>
                        <div class="text-xs text-slate-500">{{ $staff->department?->label() ?? '—' }}</div>
                    </td>
                    <td class="px-4 py-3">
                        @if($staff->is_active)
                            <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-800">Active</span>
                        @else
                            <span class="inline-flex rounded-full bg-rose-100 px-2.5 py-0.5 text-xs font-semibold text-rose-800">Deactivated</span>
                        @endif
                        @if($staff->must_change_password)
                            <span class="ml-1 inline-flex rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-800">Pwd Reset Req.</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-xs text-slate-600">
                        {{ \App\Support\Format::datetime($staff->last_login_at) }}
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="inline-flex flex-wrap justify-end gap-1.5">
                            @can('update', $staff)
                                <a href="{{ route('admin.users.edit', $staff) }}" class="rounded border border-slate-300 px-2 py-1 text-xs font-medium text-slate-700 hover:bg-slate-100">Edit</a>
                            @endcan
                            @can('resetPassword', $staff)
                                <form method="POST" action="{{ route('admin.users.reset-password', $staff) }}" onsubmit="return confirm('Generate a temporary password and force password change for {{ $staff->name }}?')">
                                    @csrf
                                    <button type="submit" class="rounded border border-amber-300 bg-amber-50 px-2 py-1 text-xs font-medium text-amber-800 hover:bg-amber-100">Reset Pwd</button>
                                </form>
                            @endcan
                            @can('forceLogout', $staff)
                                <form method="POST" action="{{ route('admin.users.force-logout', $staff) }}" onsubmit="return confirm('Terminate all active sessions for {{ $staff->name }}?')">
                                    @csrf
                                    <button type="submit" class="rounded border border-sky-300 bg-sky-50 px-2 py-1 text-xs font-medium text-sky-800 hover:bg-sky-100">Force Logout</button>
                                </form>
                            @endcan
                            @if($staff->is_active)
                                @can('deactivate', $staff)
                                    <form method="POST" action="{{ route('admin.users.deactivate', $staff) }}" onsubmit="return confirm('Deactivate {{ $staff->name }}? They will be logged out immediately.')">
                                        @csrf
                                        <button type="submit" class="rounded border border-rose-300 bg-rose-50 px-2 py-1 text-xs font-medium text-rose-800 hover:bg-rose-100">Deactivate</button>
                                    </form>
                                @endcan
                            @else
                                @can('update', $staff)
                                    <form method="POST" action="{{ route('admin.users.reactivate', $staff) }}">
                                        @csrf
                                        <button type="submit" class="rounded border border-emerald-300 bg-emerald-50 px-2 py-1 text-xs font-medium text-emerald-800 hover:bg-emerald-100">Reactivate</button>
                                    </form>
                                @endcan
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-slate-500">No staff accounts matched your filters.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-4 py-3 border-t border-slate-200">
        {{ $users->links() }}
    </div>
</div>
@endsection
