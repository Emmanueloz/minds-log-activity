<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;


class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = $request->user();

        $sessionData = [
            'ip'=> $request->ip(),
            'user_agent'=> $request->userAgent(),
            'session_id'=> $request->session()->getId(),
            'login_at'=> now()
        ];

        activity()
            ->causedBy($user)
            ->withProperties($sessionData)
            ->event('Login')
            ->log('Inicio de sesión');

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        $sessionData = [
            'ip'=> $request->ip(),
            'user_agent'=> $request->userAgent(),
            'session_id'=> $request->session()->getId(),
            'logout_at'=> now()
        ];

        activity()
            ->causedBy($user)
            ->withProperties($sessionData)
            ->event('Logout')
            ->log('Cierre de sesión');

        return redirect('/');
    }
}
