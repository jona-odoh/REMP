@extends('layouts.guest')

@section('title', 'Staff Sign In')

@section('content')
<form method="POST" action="{{ route('login.store') }}" class="space-y-4">
    @csrf

    <div>
        <label for="email" class="block text-sm font-medium text-slate-700">Username / Official Ministry Email</label>
        <input id="email" name="email" type="text" value="{{ old('email') }}" required autofocus autocomplete="username"
               placeholder="e.g. admin or officer@ksmct.kogistate.gov.ng"
               class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600">
    </div>

    <div>
        <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
        <input id="password" name="password" type="password" required autocomplete="current-password"
               class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600">
    </div>

    <div class="flex items-center justify-between text-sm">
        <label class="inline-flex items-center gap-2 text-slate-600">
            <input type="checkbox" name="remember" value="1" class="rounded border-slate-300 text-emerald-700 focus:ring-emerald-600">
            <span>Keep me signed in</span>
        </label>
    </div>

    <button type="submit"
            class="w-full rounded-md bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2 transition">
        Sign In to Back-Office
    </button>

    {{-- <p class="text-center text-xs text-slate-500 pt-2">
        All access attempts and operations are recorded in the Ministry audit trail.
    </p> --}}
</form>
@endsection
