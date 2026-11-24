<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    protected function authenticated(Request $request, $user)
    {
        $request->session()->flash('fresh_login', true);

        // 1. ADMIN -> Dashboard Admin
        if ($user->role == 'admin') {
            return redirect()->route('admin.dashboard');
        } 
        
        // 2. KRU -> Dashboard Kru
        if ($user->role == 'kru') {
            return redirect()->route('kru.dashboard');
        }

        // 3. KATIM -> Dashboard Admin (Agar bisa lihat grafik)
        if ($user->role == 'katim') {
            return redirect()->route('admin.dashboard');
        }

        // 4. TD / PD -> Dashboard
        if ($user->role == 'td' || $user->role == 'pd') {
            return redirect()->route('dashboard');
        }

        return redirect()->route('dashboard');
    }
}