<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str; // PENTING: Tambahkan ini untuk fungsi pembuatan teks acak

class ProfileController extends Controller
{
    // 1. TAMPILKAN FORM PROFIL
    public function edit()
    {
        $user = Auth::user(); 
        return view('profile.edit', compact('user'));
    }

    // 2. UPDATE DATA PROFIL
    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'nip' => 'required|string|unique:users,nip,'.$user->id,
            'password' => 'nullable|string|min:8|confirmed|regex:/[A-Z]/|regex:/[a-z]/|regex:/[0-9]/',
        ], [
            'password.min' => 'Password terlalu pendek, minimal 8 karakter.',
            'password.regex' => 'Password harus mengandung huruf besar, huruf kecil, dan angka.',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->nip = $request->nip;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        /** @var \App\Models\User $user */
        $user->save();

        return redirect()->back()->with('success', 'Profil berhasil diperbarui!');
    }

    // 3. FUNGSI BARU: GENERATE TOKEN TELEGRAM
    public function generateToken()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Menghasilkan teks acak 6 karakter (Contoh: TVRI-8A9B2C)
        $tokenAcak = 'TVRI-' . strtoupper(Str::random(6));
        
        $user->telegram_bind_token = $tokenAcak;
        $user->save();

        return redirect()->back()->with('success', 'Token Telegram berhasil dibuat! Silakan salin dan kirim ke Bot.');
    }

    // 4. PUTUSKAN SAMBUNGAN TELEGRAM (PENGGUNA SENDIRI)
    public function disconnectTelegram()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->telegram_chat_id = null;
        $user->telegram_bind_token = null;
        $user->save();

        return redirect()->back()->with('success', 'Sambungan akun Telegram berhasil diputuskan!');
    }
}