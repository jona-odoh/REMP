<?php

namespace App\Http\Controllers\Auth;

use App\Enums\AuditAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForcePasswordChangeRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        if ($request->user()->must_change_password) {
            return redirect()->route('password.force-change');
        }

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'You have been signed out.');
    }

    public function showForcePasswordChange(): View
    {
        return view('auth.force-password-change');
    }

    public function updateForcePasswordChange(ForcePasswordChangeRequest $request, AuditService $auditService): RedirectResponse
    {
        $user = $request->user();

        $user->forceFill([
            'password' => $request->validated('password'),
            'must_change_password' => false,
        ])->saveQuietly();

        $auditService->record(
            action: AuditAction::PASSWORD_RESET,
            entity: $user,
            after: ['must_change_password' => false],
            metadata: ['event' => 'user_completed_forced_password_change'],
            actor: $user,
        );

        return redirect()->route('dashboard')->with('status', 'Your password has been updated.');
    }
}
