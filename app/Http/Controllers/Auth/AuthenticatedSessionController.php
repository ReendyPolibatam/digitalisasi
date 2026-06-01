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
     * Display login form.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle login.
     */
    public function store(LoginRequest $request): RedirectResponse
{
    $request->authenticate();

    $request->session()->regenerate();

    $user = Auth::user();

    if (strtolower($user->role) === 'admin') {
        return redirect('/admin');
    }

    if (strtolower($user->role) === 'staff') {
        return redirect('/staff');
    }

    Auth::logout();

    return redirect('/login')
        ->withErrors([
            'email' => 'Role tidak valid.'
        ]);
}

    /**
     * Logout.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}