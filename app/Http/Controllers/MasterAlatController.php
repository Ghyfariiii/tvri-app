<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class MasterAlatController extends Controller
{
    public function index()
    {
        if (!Gate::allows('admin')) abort(403);
        $alats = DB::table('alats')->orderBy('nama_alat', 'asc')->get();
        return view('master.alat.index', compact('alats'));
    }

    public function create()
    {
        if (!Gate::allows('admin')) abort(403);
        return view('master.alat.create');
    }

    public function store(Request $request)
    {
        if (!Gate::allows('admin')) abort(403);
        
        $request->validate([
            'nama_alat' => 'required|string|max:255',
        ]);

        DB::table('alats')->insert([
            'nama_alat' => $request->nama_alat,
            'deskripsi' => $request->deskripsi,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('master.alat.index')->with('success', 'Alat berhasil ditambahkan.');
    }

    public function edit($id)
    {
        if (!Gate::allows('admin')) abort(403);
        $alat = DB::table('alats')->where('id', $id)->first();
        return view('master.alat.edit', compact('alat'));
    }

    public function update(Request $request, $id)
    {
        if (!Gate::allows('admin')) abort(403);

        DB::table('alats')->where('id', $id)->update([
            'nama_alat' => $request->nama_alat,
            'deskripsi' => $request->deskripsi,
            'updated_at' => now(),
        ]);

        return redirect()->route('master.alat.index')->with('success', 'Data alat diperbarui.');
    }

    public function destroy($id)
    {
        if (!Gate::allows('admin')) abort(403);
        DB::table('alats')->where('id', $id)->delete();
        return redirect()->route('master.alat.index')->with('success', 'Alat dihapus.');
    }
}