<?php

namespace App\Http\Controllers\Technician;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function show()
    {
        return Auth::check() ? redirect('/technicien') : view('technician.login');
    }

    public function login(Request $request)
    {
        $cred = $request->validate(['email' => ['required', 'email'], 'password' => ['required', 'string']]);

        // Même message dans tous les cas : on ne révèle pas si l'adresse existe. (La route est limitée en débit.)
        if (! Auth::attempt($cred) || ! (Auth::user()->is_technician || Auth::user()->is_admin)) {
            Auth::logout();
            return back()->withErrors(['email' => 'Identifiants incorrects.'])->onlyInput('email');
        }

        $request->session()->regenerate();
        return redirect('/technicien');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/technicien/connexion');
    }
}
