<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Gate;

class UserController extends Controller
{
    // 1. DAFTAR USER
    public function index()
    {
        if (!Gate::allows('admin')) abort(403);
        $users = User::orderBy('role', 'asc')->orderBy('name', 'asc')->get();
        
        return view('users.index', compact('users'));
    }

    // 2. FORM TAMBAH
    public function create()
    {
        if (!Gate::allows('admin')) abort(403);
        
        return view('users.create');
    }

    // 3. SIMPAN USER BARU
    public function store(Request $request)
    {
        if (!Gate::allows('admin')) abort(403);

        // Menambahkan pesan error khusus agar mudah dimengerti
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'nip' => 'required|string|unique:users',
            'role' => 'required|in:admin,kru,katim,td,pd', 
            'password' => 'required|string|min:8|confirmed|regex:/[A-Z]/|regex:/[a-z]/|regex:/[0-9]/',
        ], [
            'email.unique' => 'Alamat email ini sudah digunakan oleh user lain.',
            'nip.unique' => 'NIP ini sudah terdaftar dalam sistem.',
            'password.min' => 'Password terlalu pendek, minimal 8 karakter.',
            'password.regex' => 'Password harus mengandung huruf besar, huruf kecil, dan angka.',
            'password.confirmed' => 'Konfirmasi password tidak sama.'
        ]);

        try {
            User::create([
                'name' => $request->name,
                'email' => $request->email,
                'nip' => $request->nip,
                'role' => $request->role,
                'password' => Hash::make($request->password),
            ]);

            return redirect()->route('users.index')->with('success', 'User baru berhasil ditambahkan ke dalam sistem.');
        } catch (\Exception $e) {
            // Menangkap error sistem (misal database putus) agar tidak menampilkan halaman error abu-abu
            return back()->withInput()->with('error', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage());
        }
    }

    // 4. FORM EDIT
    public function edit($id)
    {
        if (!Gate::allows('admin')) abort(403);
        $user = User::findOrFail($id);
        
        return view('users.edit', compact('user'));
    }

    // 5. UPDATE USER
    public function update(Request $request, $id)
    {
        if (!Gate::allows('admin')) abort(403);
        
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$id,
            'nip' => 'required|string|unique:users,nip,'.$id,
            'role' => 'required|in:admin,kru,katim,td,pd',
            'password' => 'nullable|string|min:8|confirmed|regex:/[A-Z]/|regex:/[a-z]/|regex:/[0-9]/',
        ], [
            'email.unique' => 'Alamat email ini sudah digunakan oleh user lain.',
            'nip.unique' => 'NIP ini sudah terdaftar dalam sistem.',
            'password.min' => 'Password terlalu pendek, minimal 8 karakter.',
            'password.regex' => 'Password harus mengandung huruf besar, huruf kecil, dan angka.',
            'password.confirmed' => 'Konfirmasi password tidak sama.'
        ]);

        try {
            $user->name = $request->name;
            $user->email = $request->email;
            $user->nip = $request->nip;
            $user->role = $request->role;

            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }

            $user->save();

            return redirect()->route('users.index')->with('success', 'Data user berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Terjadi kesalahan saat mengupdate data: ' . $e->getMessage());
        }
    }

    // 6. HAPUS USER
    public function destroy($id)
    {
        if (!Gate::allows('admin')) abort(403);
        $user = User::findOrFail($id);
        if ($user->id == auth()->id()) return back()->with('error', 'Tidak bisa hapus akun sendiri!');
        
        try {
            $user->delete();
            return redirect()->route('users.index')->with('success', 'Data user berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus user: ' . $e->getMessage());
        }
    }

    // 7. PUTUSKAN SAMBUNGAN TELEGRAM USER TERTENTU (KHUSUS ADMIN)
    public function disconnectTelegram($id)
    {
        if (!Gate::allows('admin')) abort(403);
        
        $user = User::findOrFail($id);
        $user->telegram_chat_id = null;
        $user->telegram_bind_token = null;
        $user->save();

        return redirect()->back()->with('success', "Sambungan Telegram untuk pengguna {$user->name} berhasil diputuskan!");
    }
}