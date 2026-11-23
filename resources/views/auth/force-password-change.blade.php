@extends('layouts.guest')

@section('title', 'Change Temporary Password')

@section('content')
<div class="mb-4 rounded-md bg-amber-50 border border-amber-200 p-3 text-xs text-amber-900">
    Your administrator requires you to set a new personal password before accessing the Ministry back-office.
</div>

<form method="POST" action="{{ route('password.force-change.update') }}" class="space-y-4">
    @csrf
    @method('PUT')

    <div>
        <label for="current_password" class="block text-sm font-medium text-slate-700">Temporary / Current Password</label>
        <input id="current_password" name="current_password" type="password" required
               class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600">
    </div>

    <div>
        <label for="password" class="block text-sm font-medium text-slate-700">New Password</label>
        <input id="password" name="password" type="password" required
               class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600">
        <p class="mt-1 text-xs text-slate-500">Minimum 8 characters with uppercase, lowercase, and numbers.</p>
    </div>

    <div>
        <label for="password_confirmation" class="block text-sm font-medium text-slate-700">Confirm New Password</label>
        <input id="password_confirmation" name="password_confirmation" type="password" required
               class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600">
    </div>

    <button type="submit"
            class="w-full rounded-md bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-800 transition">
        Update Password &amp; Continue
    </button>
</form>
@endsection
