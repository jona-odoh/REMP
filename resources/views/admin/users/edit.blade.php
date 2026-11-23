@extends('layouts.app')

@section('title', 'Edit Staff Account')
@section('heading', 'Edit Staff Account: '.$user->name)

@section('content')
<div class="max-w-2xl rounded-lg bg-white border border-slate-200 shadow-sm p-6">
    <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-4">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="name" class="block text-sm font-medium text-slate-700">Full Name *</label>
                <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required
                       class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label for="email" class="block text-sm font-medium text-slate-700">Official Email *</label>
                <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required
                       class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label for="staff_id" class="block text-sm font-medium text-slate-700">Staff ID</label>
                <input id="staff_id" name="staff_id" type="text" value="{{ old('staff_id', $user->staff_id) }}"
                       class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label for="phone" class="block text-sm font-medium text-slate-700">Phone Number</label>
                <input id="phone" name="phone" type="text" value="{{ old('phone', $user->phone) }}"
                       class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label for="department" class="block text-sm font-medium text-slate-700">Department *</label>
                <select id="department" name="department" required class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                    @foreach($departments as $dept)
                        <option value="{{ $dept->value }}" @selected(old('department', $user->department?->value) === $dept->value)>{{ $dept->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="role" class="block text-sm font-medium text-slate-700">Role *</label>
                <select id="role" name="role" required class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                    @foreach($roles as $role)
                        <option value="{{ $role->value }}" @selected(old('role', $user->primaryRoleName()) === $role->value)>{{ $role->label() }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="pt-3 flex items-center gap-3">
            <button type="submit" class="rounded-md bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-800">
                Save Changes
            </button>
            <a href="{{ route('admin.users.index') }}" class="text-sm text-slate-600 hover:text-slate-900">Cancel</a>
        </div>
    </form>
</div>
@endsection
