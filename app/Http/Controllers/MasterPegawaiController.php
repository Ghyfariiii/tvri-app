<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class MasterPegawaiController extends Controller
{
    public function index()
    {
        if (!Gate::allows('admin')) abort(403);
        $pegawais = DB::table('pegawais')->orderBy('nama_lengkap', 'asc')->get();
        return view('master.pegawai.index', compact('pegawais'));
    }

    public function create()
    {
        if (!Gate::allows('admin')) abort(403);
        return view('master.pegawai.create');
    }

    public function store(Request $request)
    {
        if (!Gate::allows('admin')) abort(403);
        
        $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'posisi_default' => 'required|string|max:255',
        ]);

        DB::table('pegawais')->insert([
            'nama_lengkap' => $request->nama_lengkap,
            'nip' => $request->nip,
            'posisi_default' => $request->posisi_default,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('master.pegawai.index')->with('success', 'Pegawai berhasil ditambahkan.');
    }

    public function edit($id)
    {
        if (!Gate::allows('admin')) abort(403);
        $pegawai = DB::table('pegawais')->where('id', $id)->first();
        return view('master.pegawai.edit', compact('pegawai'));
    }

    public function update(Request $request, $id)
    {
        if (!Gate::allows('admin')) abort(403);

        DB::table('pegawais')->where('id', $id)->update([
            'nama_lengkap' => $request->nama_lengkap,
            'nip' => $request->nip,
            'posisi_default' => $request->posisi_default,
            'updated_at' => now(),
        ]);

        return redirect()->route('master.pegawai.index')->with('success', 'Data pegawai diperbarui.');
    }

    public function destroy($id)
    {
        if (!Gate::allows('admin')) abort(403);
        DB::table('pegawais')->where('id', $id)->delete();
        return redirect()->route('master.pegawai.index')->with('success', 'Pegawai dihapus.');
    }
}