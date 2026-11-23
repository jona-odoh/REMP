<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Department;
use App\Enums\RoleName;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DeactivateUserRequest;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use App\Services\UserManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(
        private readonly UserManagementService $userService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        $users = User::query()
            ->with('roles')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->toString();
                $query->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('staff_id', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('role'), function ($query) use ($request) {
                $query->role($request->string('role')->toString());
            })
            ->when($request->filled('department'), function ($query) use ($request) {
                $query->where('department', $request->string('department')->toString());
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('is_active', $request->string('status')->toString() === 'active');
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'roles' => RoleName::cases(),
            'departments' => Department::cases(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', User::class);

        return view('admin.users.create', [
            'roles' => RoleName::cases(),
            'departments' => Department::cases(),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $user = $this->userService->createUser($request->validated());

        return redirect()
            ->route('admin.users.index')
            ->with('status', "Staff account created for {$user->name} ({$user->email}).");
    }

    public function edit(User $user): View
    {
        $this->authorize('update', $user);

        return view('admin.users.edit', [
            'user' => $user->load('roles'),
            'roles' => RoleName::cases(),
            'departments' => Department::cases(),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->userService->updateUser($user, $request->validated());

        return redirect()
            ->route('admin.users.index')
            ->with('status', "Updated staff account for {$user->name}.");
    }

    public function deactivate(DeactivateUserRequest $request, User $user): RedirectResponse
    {
        $this->userService->deactivate($user, $request->user(), $request->validated('reason'));

        return redirect()
            ->route('admin.users.index')
            ->with('status', "Account for {$user->name} has been deactivated and active sessions terminated.");
    }

    public function reactivate(Request $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $this->userService->reactivate($user, $request->user());

        return redirect()
            ->route('admin.users.index')
            ->with('status', "Account for {$user->name} has been reactivated.");
    }

    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        $this->authorize('resetPassword', $user);

        $temporaryPassword = $this->userService->resetPassword($user, $request->user());

        return redirect()
            ->route('admin.users.index')
            ->with('status', "Password reset for {$user->name}. Temporary password (shown once): {$temporaryPassword}");
    }

    public function forceLogout(Request $request, User $user): RedirectResponse
    {
        $this->authorize('forceLogout', $user);

        $count = $this->userService->forceLogout($user, $request->user());

        return redirect()
            ->route('admin.users.index')
            ->with('status', "Forced logout executed for {$user->name} ({$count} active session(s) terminated).");
    }
}
