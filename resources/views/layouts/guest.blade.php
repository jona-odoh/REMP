<!DOCTYPE html>
<html lang="en-NG">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sign In') — {{ __('ksmct.short_name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-900 flex items-center justify-center p-4 antialiased">
    <main class="w-full max-w-md bg-white rounded-xl shadow-xl border border-slate-200 p-6 sm:p-8">
        <div class="flex justify-end mb-2">
            <x-page-help-modal />
        </div>
        <div class="text-center mb-6">
            <span class="inline-block rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-emerald-800">
                Kogi State Government
            </span>
            <h1 class="mt-3 text-xl font-bold text-slate-900">{{ __('ksmct.ministry_name') }}</h1>
            <p class="mt-1 text-xs text-slate-500">{{ __('ksmct.internal_notice') }}</p>
        </div>

        @if (session('status'))
            <div role="status" class="mb-4 rounded-md border border-emerald-300 bg-emerald-50 px-3 py-2 text-sm text-emerald-900">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div role="alert" class="mb-4 rounded-md border border-rose-300 bg-rose-50 px-3 py-2 text-sm text-rose-900">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>
