<?php

namespace App\Http\Controllers;

use App\Models\Lga;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserManagementController extends Controller
{
    public function index(): View
    {
        $users = User::with(['lga', 'roles'])->orderBy('id')->get();
        $lgas = Lga::orderBy('name')->get();
        return view('users.index', compact('users', 'lgas'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'role_enum' => ['required', 'in:admin,field_officer,lga_officer,energy_analyst,stakeholder_viewer'],
            'lga_id' => ['nullable', 'exists:lgas,id'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role_enum' => $data['role_enum'],
            'lga_id' => $data['lga_id'] ?? null,
        ]);

        if (Role::where('name', $data['role_enum'])->exists()) {
            $user->syncRoles([$data['role_enum']]);
        }

        return back()->with('success', "User '{$user->name}' created with role " . strtoupper(str_replace('_', ' ', $user->role_enum)) . ".");
    }

    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'role_enum' => ['required', 'in:admin,field_officer,lga_officer,energy_analyst,stakeholder_viewer'],
            'lga_id' => ['nullable', 'exists:lgas,id'],
        ]);

        $user->update([
            'role_enum' => $data['role_enum'],
            'lga_id' => $data['lga_id'] ?: null,
        ]);

        if (Role::where('name', $data['role_enum'])->exists()) {
            $user->syncRoles([$data['role_enum']]);
        }

        return back()->with('success', "Updated role for {$user->name}.");
    }
}
