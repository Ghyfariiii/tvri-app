<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LaporanExport;
use Barryvdh\DomPDF\Facade\Pdf;

class MonitoringController extends Controller
{
    // ============================================
    // HELPER: Cek role yang diperbolehkan
    // ============================================
    private function authorizeRoles(array $roles)
    {
        if (!Auth::check()) {
            abort(403, 'Akses ditolak. Silakan login.');
        }

        $userRole = Auth::user()->role;
        // Izinkan TD dan PD saling menggantikan
        if ($userRole === 'pd' && in_array('td', $roles)) {
            return;
        }
        if ($userRole === 'td' && in_array('pd', $roles)) {
            return;
        }

        if (!in_array($userRole, $roles, true)) {
            abort(403, 'Akses ditolak. Anda tidak memiliki izin untuk tindakan ini.');
        }
    }

    // ============================================
    // INDEX: Semua role yang login bisa lihat arsip
    // ============================================
    public function index(Request $request)
    {
        $this->authorizeRoles(['td', 'pd', 'katim', 'admin', 'kru']);

        $query = DB::table('monitoring_harians')
            ->leftJoin('spos', 'monitoring_harians.spo_id', '=', 'spos.id')
            ->leftJoin('users', 'monitoring_harians.created_by', '=', 'users.id')
            ->select('monitoring_harians.*', 'spos.no_surat', 'spos.katim_id', 'users.name as pelapor');

        if ($request->filled('tanggal_dari')) {
            $query->where('monitoring_harians.tanggal', '>=', $request->tanggal_dari);
        }

        if ($request->filled('tanggal_sampai')) {
            $query->where('monitoring_harians.tanggal', '<=', $request->tanggal_sampai);
        }

        if ($request->filled('status_siaran') && $request->status_siaran !== 'semua') {
            $query->where('monitoring_harians.status_siaran', $request->status_siaran);
        }

        if ($request->filled('status_revisi') && $request->status_revisi !== 'semua') {
            $query->where('monitoring_harians.status_revisi', $request->status_revisi);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('spos.no_surat', 'LIKE', "%{$search}%")
                  ->orWhere('users.name', 'LIKE', "%{$search}%");
            });
        }

        $monitorings = $query->orderBy('monitoring_harians.tanggal', 'desc')->get();

        // VALIDASI KETAT: Hitung pending revisi sesuai hak akses Katim / Admin
        $revisiPendingCount = 0;
        if (Auth::user()->role === 'katim') {
            $revisiPendingCount = DB::table('monitoring_harians')
                ->join('spos', 'monitoring_harians.spo_id', '=', 'spos.id')
                ->where('monitoring_harians.status_revisi', 'pending')
                ->where('spos.katim_id', Auth::id())
                ->count();
        } elseif (Auth::user()->role === 'admin') {
            $revisiPendingCount = DB::table('monitoring_harians')
                ->where('status_revisi', 'pending')
                ->count();
        }

        return view('monitoring.index', compact('monitorings', 'revisiPendingCount'));
    }

    // ============================================
    // CREATE: Khusus TD (Pengarah Teknik)
    // Hanya menampilkan SPO yang menugaskan TD login sebagai Pengarah Teknik
    // ============================================
    public function create()
    {
        $this->authorizeRoles(['td', 'pd']);
        $user = Auth::user();

        $spoSudahDilaporkan = DB::table('monitoring_harians')
            ->pluck('spo_id')
            ->toArray();

        $query = DB::table('spos')
            ->where('status_persetujuan', 'approved')
            ->whereNotIn('id', $spoSudahDilaporkan)
            ->orderBy('tanggal_pelaksanaan', 'desc');

        $sposSemua = $query->get();

        // Saring hanya SPO di mana Pengarah Teknik = TD yang sedang login
        $nipUser = trim($user->nip ?? '');
        $namaUser = trim(strtolower($user->name));

        $pegawaiUser = !empty($nipUser) ? DB::table('pegawais')->where('nip', $nipUser)->first() : null;
        $namaPegawai = $pegawaiUser ? trim(strtolower($pegawaiUser->nama_lengkap)) : null;

        $spos = $sposSemua->filter(function($spo) use ($namaUser, $namaPegawai) {
            $petugas = json_decode($spo->petugas_penyiaran, true) ?? [];
            foreach ($petugas as $p) {
                $jabatan = strtolower(trim($p['jabatan'] ?? ''));
                if ($jabatan === 'pengarah teknik') {
                    $namaPetugas = strtolower(trim($p['nama'] ?? ''));
                    // Cocokkan nama akun atau nama pegawai ber-NIP sama
                    if ($namaPetugas === $namaUser || ($namaPegawai && $namaPetugas === $namaPegawai)) {
                        return true;
                    }
                }
            }
            return false;
        })->values();

        // Ambil data Master Alat untuk pilihan dropdown saat terjadi kendala alat
        $alats = DB::table('alats')->orderBy('nama_alat', 'asc')->get();

        return view('monitoring.create', compact('spos', 'alats'));
    }

    // ============================================
    // STORE: Simpan Laporan Header & Detail Acara Per-Jam
    // ============================================
    public function store(Request $request)
    {
        $this->authorizeRoles(['td']);

        $request->validate([
            'tanggal' => 'required|date',
            'spo_id' => 'required',
            'details' => 'required|array|min:1',
            'details.*.jam' => 'required',
            'details.*.kegiatan' => 'required|string',
            'details.*.status' => 'required|in:lancar,kendala,gagal',
        ], [
            'details.required' => 'Wajib mengisi minimal 1 baris jadwal/acara siaran.',
            'details.min' => 'Wajib mengisi minimal 1 baris jadwal/acara siaran.',
            'details.*.jam.required' => 'Jam acara wajib diisi.',
            'details.*.kegiatan.required' => 'Nama kegiatan/acara wajib diisi.',
        ]);

        // Hitung kesimpulan status pelaksanaan siaran otomatis dari rincian rincian acara
        $statuses = collect($request->details)->pluck('status')->map(fn($s) => strtolower(trim($s)));
        if ($statuses->contains('gagal')) {
            $statusSiaran = 'gagal';
        } elseif ($statuses->contains('kendala')) {
            $statusSiaran = 'kendala';
        } else {
            $statusSiaran = 'sukses';
        }

        $kruTugas = [];
        if ($request->has('kru_bertugas') && is_array($request->kru_bertugas)) {
            foreach ($request->kru_bertugas as $kru) {
                if (!empty($kru['nama']) && !empty($kru['jabatan'])) {
                    $kruTugas[] = ['nama' => $kru['nama'], 'jabatan' => $kru['jabatan']];
                }
            }
        }

        // Transaksi Database (ACID) untuk menjamin keutuhan data header & detail
        DB::transaction(function () use ($request, $statusSiaran, $kruTugas) {
            // 1. Simpan Header Laporan
            $monitoringId = DB::table('monitoring_harians')->insertGetId([
                'tanggal' => $request->tanggal,
                'spo_id' => $request->spo_id,
                'created_by' => Auth::id(),
                'kru_bertugas' => json_encode($kruTugas),
                'kondisi_audio' => null,
                'kondisi_video' => null,
                'catatan_khusus' => null,
                'status_siaran' => $statusSiaran,
                'status_revisi' => 'none',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // 2. Simpan Baris Log Acara Per-Jam ke tabel monitoring_details
            if ($request->has('details') && is_array($request->details)) {
                foreach ($request->details as $d) {
                    if (!empty($d['jam']) && !empty($d['kegiatan'])) {
                        DB::table('monitoring_details')->insert([
                            'monitoring_harian_id' => $monitoringId,
                            'jam' => $d['jam'],
                            'kegiatan' => $d['kegiatan'],
                            'status' => $d['status'] ?? 'lancar',
                            'jenis_kendala' => $d['jenis_kendala'] ?? null,
                            'is_mendesak' => false,
                            'alat_rusak_id' => (!empty($d['alat_rusak_id']) && in_array($d['jenis_kendala'] ?? '', ['video', 'audio'])) ? $d['alat_rusak_id'] : null,
                            'deskripsi_kendala' => $d['deskripsi_kendala'] ?? null,
                            'solusi' => $d['solusi'] ?? null,
                            'catatan' => $d['catatan'] ?? null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        });

        return redirect()->route('monitoring.index')->with('success', 'Laporan monitoring siaran dan log kegiatan acara berhasil disimpan.');
    }

    // ============================================
    // SHOW: Tampilkan Detail Laporan, Kru & Log Acara
    // ============================================
    public function show($id)
    {
        $this->authorizeRoles(['td', 'pd', 'katim', 'admin', 'kru']);

        $laporan = DB::table('monitoring_harians')
            ->leftJoin('spos', 'monitoring_harians.spo_id', '=', 'spos.id')
            ->leftJoin('users as u_katim', 'spos.katim_id', '=', 'u_katim.id')
            ->leftJoin('users', 'monitoring_harians.created_by', '=', 'users.id')
            ->select(
                'monitoring_harians.*', 
                'spos.no_surat', 
                'spos.tanggal_pelaksanaan', 
                'spos.katim_id',
                'u_katim.name as nama_katim',
                'users.name as penginput',
                'users.nip as nip_penginput'
            )
            ->where('monitoring_harians.id', $id)
            ->first();

        if (!$laporan) {
            abort(404);
        }

        $kruBertugas = json_decode($laporan->kru_bertugas, true) ?? [];

        // Ambil rincian kegiatan siaran per-jam
        $details = DB::table('monitoring_details')
            ->leftJoin('alats', 'monitoring_details.alat_rusak_id', '=', 'alats.id')
            ->select('monitoring_details.*', 'alats.nama_alat')
            ->where('monitoring_details.monitoring_harian_id', $id)
            ->orderBy('monitoring_details.jam', 'asc')
            ->get();

        // Ambil data pengajuan revisi terbaru jika ada
        $revisi = DB::table('revisi_requests')
            ->leftJoin('users as pemohon', 'revisi_requests.diajukan_oleh', '=', 'pemohon.id')
            ->leftJoin('users as penyetuju', 'revisi_requests.disetujui_oleh', '=', 'penyetuju.id')
            ->where('revisi_requests.jenis_dokumen', 'monitoring')
            ->where('revisi_requests.dokumen_id', $id)
            ->select('revisi_requests.*', 'pemohon.name as nama_pemohon', 'penyetuju.name as nama_penyetuju')
            ->latest('revisi_requests.id')
            ->first();

        return view('monitoring.show', compact('laporan', 'kruBertugas', 'details', 'revisi'));
    }

    // ============================================
    // EDIT: Khusus TD Pembuat / Admin, hanya jika revisi approved
    // ============================================
    public function edit($id)
    {
        $this->authorizeRoles(['td', 'pd', 'admin']);

        $laporan = DB::table('monitoring_harians')
            ->leftJoin('spos', 'monitoring_harians.spo_id', '=', 'spos.id')
            ->select('monitoring_harians.*', 'spos.no_surat', 'spos.tanggal_pelaksanaan', 'spos.petugas_penyiaran', 'spos.petugas_penunjang')
            ->where('monitoring_harians.id', $id)
            ->first();

        if (!$laporan) {
            abort(404);
        }

        if (Auth::id() !== $laporan->created_by && Auth::user()->role !== 'admin') {
            abort(403);
        }

        if ($laporan->status_revisi !== 'approved') {
            return redirect()->route('monitoring.show', $id)->with('error', 'Laporan harus melalui proses persetujuan revisi terlebih dahulu sebelum dapat diedit.');
        }

        $spoSudahDilaporkan = DB::table('monitoring_harians')
            ->where('id', '!=', $id)
            ->pluck('spo_id')
            ->toArray();

        $spos = DB::table('spos')
            ->where('status_persetujuan', 'approved')
            ->whereNotIn('id', $spoSudahDilaporkan)
            ->orderBy('tanggal_pelaksanaan', 'desc')
            ->get();

        $spoSaatIni = DB::table('spos')->where('id', $laporan->spo_id)->first();
        if ($spoSaatIni && !$spos->contains('id', $spoSaatIni->id)) {
            $spos->prepend($spoSaatIni);
        }

        $kruBertugas = json_decode($laporan->kru_bertugas, true) ?? [];

        // Ambil rincian kegiatan acara
        $details = DB::table('monitoring_details')
            ->where('monitoring_harian_id', $id)
            ->orderBy('jam', 'asc')
            ->get();

        // Ambil Master Alat
        $alats = DB::table('alats')->orderBy('nama_alat', 'asc')->get();

        return view('monitoring.edit', compact('laporan', 'spos', 'kruBertugas', 'details', 'alats'));
    }

    // ============================================
    // UPDATE: Simpan Perubahan Header & Detail Acara
    // ============================================
    public function update(Request $request, $id)
    {
        $this->authorizeRoles(['td', 'pd', 'admin']);

        $laporan = DB::table('monitoring_harians')->where('id', $id)->first();
        if (!$laporan) {
            abort(404);
        }

        if (Auth::id() !== $laporan->created_by && Auth::user()->role !== 'admin') {
            abort(403);
        }

        if ($laporan->status_revisi !== 'approved') {
            return back()->with('error', 'Laporan harus melalui proses persetujuan revisi terlebih dahulu.');
        }

        $request->validate([
            'tanggal' => 'required|date',
            'spo_id' => 'required',
            'details' => 'required|array|min:1',
            'details.*.jam' => 'required',
            'details.*.kegiatan' => 'required|string',
            'details.*.status' => 'required|in:lancar,kendala,gagal',
        ]);

        // Hitung kesimpulan status pelaksanaan siaran otomatis dari rincian rincian acara
        $statuses = collect($request->details)->pluck('status')->map(fn($s) => strtolower(trim($s)));
        if ($statuses->contains('gagal')) {
            $statusSiaran = 'gagal';
        } elseif ($statuses->contains('kendala')) {
            $statusSiaran = 'kendala';
        } else {
            $statusSiaran = 'sukses';
        }

        $kruTugas = [];
        if ($request->has('kru_bertugas') && is_array($request->kru_bertugas)) {
            foreach ($request->kru_bertugas as $kru) {
                if (!empty($kru['nama']) && !empty($kru['jabatan'])) {
                    $kruTugas[] = ['nama' => $kru['nama'], 'jabatan' => $kru['jabatan']];
                }
            }
        }

        // 1. Update Header Laporan
        $updateData = [
            'tanggal' => $request->tanggal,
            'spo_id' => $request->spo_id,
            'kru_bertugas' => json_encode($kruTugas),
            'kondisi_audio' => null,
            'kondisi_video' => null,
            'catatan_khusus' => null,
            'status_siaran' => $statusSiaran,
            'updated_at' => now()
        ];

        // Reset status revisi ke none setelah revisi disimpan agar terkunci kembali
        if ($laporan->status_revisi === 'approved') {
            $updateData['status_revisi'] = 'none';
        }

        // Transaksi Database (ACID) untuk menjamin atomic update & mencegah data loss
        DB::transaction(function () use ($id, $updateData, $request) {
            // 1. Update Header Laporan
            DB::table('monitoring_harians')->where('id', $id)->update($updateData);

            // 2. Sinkronkan Detail Kegiatan Acara
            DB::table('monitoring_details')->where('monitoring_harian_id', $id)->delete();
            if ($request->has('details') && is_array($request->details)) {
                foreach ($request->details as $d) {
                    if (!empty($d['jam']) && !empty($d['kegiatan'])) {
                        DB::table('monitoring_details')->insert([
                            'monitoring_harian_id' => $id,
                            'jam' => $d['jam'],
                            'kegiatan' => $d['kegiatan'],
                            'status' => $d['status'] ?? 'lancar',
                            'jenis_kendala' => $d['jenis_kendala'] ?? null,
                            'is_mendesak' => false,
                            'alat_rusak_id' => (!empty($d['alat_rusak_id']) && in_array($d['jenis_kendala'] ?? '', ['video', 'audio'])) ? $d['alat_rusak_id'] : null,
                            'deskripsi_kendala' => $d['deskripsi_kendala'] ?? null,
                            'solusi' => $d['solusi'] ?? null,
                            'catatan' => $d['catatan'] ?? null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        });

        return redirect()->route('monitoring.index')->with('success', 'Laporan monitoring siaran dan log kegiatan acara berhasil diperbarui.');
    }

    // ============================================
    // DESTROY: Hapus Laporan
    // ============================================
    public function destroy($id)
    {
        $this->authorizeRoles(['td', 'pd', 'admin']);

        $laporan = DB::table('monitoring_harians')->where('id', $id)->first();
        if (!$laporan) {
            abort(404);
        }

        if (Auth::id() !== $laporan->created_by && Auth::user()->role !== 'admin') {
            abort(403);
        }

        if ($laporan->status_revisi === 'pending') {
            return back()->with('error', 'Laporan sedang dalam proses peninjauan revisi.');
        }

        // Hapus detail terkait, riwayat pengajuan revisi & header dengan transaksi DB
        DB::transaction(function () use ($id) {
            DB::table('revisi_requests')
                ->where('jenis_dokumen', 'monitoring')
                ->where('dokumen_id', $id)
                ->delete();

            DB::table('monitoring_details')->where('monitoring_harian_id', $id)->delete();
            DB::table('monitoring_harians')->where('id', $id)->delete();
        });

        return redirect()->route('monitoring.index')->with('success', 'Laporan monitoring siaran berhasil dihapus.');
    }

    // ============================================
    // CETAK SINGLE LAPORAN PDF (1 LAPORAN KHUSUS)
    // ============================================
    public function downloadSinglePdf($id)
    {
        $this->authorizeRoles(['td', 'pd', 'katim', 'admin', 'kru']);

        $laporan = DB::table('monitoring_harians')
            ->leftJoin('spos', 'monitoring_harians.spo_id', '=', 'spos.id')
            ->leftJoin('users as u_katim', 'spos.katim_id', '=', 'u_katim.id')
            ->leftJoin('users', 'monitoring_harians.created_by', '=', 'users.id')
            ->select(
                'monitoring_harians.*', 
                'spos.no_surat', 
                'spos.tanggal_pelaksanaan', 
                'spos.hari',
                'spos.petugas_penyiaran',
                'spos.petugas_penunjang',
                'spos.katim_id',
                'u_katim.name as nama_katim',
                'u_katim.nip as nip_katim',
                'users.name as penginput',
                'users.nip as nip_penginput'
            )
            ->where('monitoring_harians.id', $id)
            ->first();

        if (!$laporan) {
            abort(404);
        }

        $kruBertugas = json_decode($laporan->kru_bertugas, true) ?? [];
        if (empty($kruBertugas)) {
            $penyiaran = json_decode($laporan->petugas_penyiaran ?? '[]', true) ?? [];
            $penunjang = json_decode($laporan->petugas_penunjang ?? '[]', true) ?? [];
            $kruBertugas = array_merge($penyiaran, $penunjang);
        }

        $details = DB::table('monitoring_details')
            ->leftJoin('alats', 'monitoring_details.alat_rusak_id', '=', 'alats.id')
            ->select('monitoring_details.*', 'alats.nama_alat')
            ->where('monitoring_details.monitoring_harian_id', $id)
            ->orderBy('monitoring_details.jam', 'asc')
            ->get();

        $pdf = Pdf::loadView('monitoring.single_pdf', compact('laporan', 'kruBertugas', 'details'))
            ->setPaper('a4', 'portrait');

        $namaFile = 'Laporan-TD-' . $laporan->tanggal . '.pdf';
        return $pdf->stream($namaFile);
    }

    // ============================================
    // EXPORT EXCEL MASSAL (SEMUA / RENTANG TANGGAL)
    // ============================================
    public function export(Request $request)
    {
        $this->authorizeRoles(['td', 'pd', 'katim', 'admin', 'kru']);

        return Excel::download(new LaporanExport($request), 'laporan-monitoring-rekap-' . date('Y-m-d') . '.xlsx');
    }

    // ============================================
    // EXPORT PDF REKAPITULASI MASSAL
    // ============================================
    public function exportPdf(Request $request)
    {
        $this->authorizeRoles(['td', 'pd', 'katim', 'admin', 'kru']);

        $query = DB::table('monitoring_harians')
            ->leftJoin('spos', 'monitoring_harians.spo_id', '=', 'spos.id')
            ->leftJoin('users', 'monitoring_harians.created_by', '=', 'users.id')
            ->select('monitoring_harians.*', 'spos.no_surat', 'users.name as pelapor', 'users.name as penginput');

        if ($request->filled('tanggal_dari')) {
            $query->where('monitoring_harians.tanggal', '>=', $request->tanggal_dari);
        }

        if ($request->filled('tanggal_sampai')) {
            $query->where('monitoring_harians.tanggal', '<=', $request->tanggal_sampai);
        }

        if ($request->filled('status_siaran') && $request->status_siaran !== 'semua') {
            $query->where('monitoring_harians.status_siaran', $request->status_siaran);
        }

        if ($request->filled('status_revisi') && $request->status_revisi !== 'semua') {
            $query->where('monitoring_harians.status_revisi', $request->status_revisi);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('spos.no_surat', 'LIKE', "%{$search}%")
                  ->orWhere('users.name', 'LIKE', "%{$search}%");
            });
        }

        $monitorings = $query->orderBy('monitoring_harians.tanggal', 'desc')->get();

        $pdf = Pdf::loadView('monitoring.export_pdf', compact('monitorings'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('rekapitulasi-monitoring-' . date('Y-m-d') . '.pdf');
    }

    // ============================================
    // REQUEST REVISION: Pengajuan Revisi oleh TD
    // ============================================
    public function requestRevision(Request $request, $id)
    {
        $monitoring = DB::table('monitoring_harians')->where('id', $id)->first();
        if (!$monitoring) abort(404);

        if ($monitoring->created_by != Auth::id() && Auth::user()->role !== 'admin') {
            return back()->with('error', 'Anda tidak berwenang mengajukan revisi monitoring ini.');
        }

        if ($monitoring->status_revisi === 'pending') {
            return back()->with('error', 'Permintaan revisi untuk laporan ini sedang menunggu persetujuan Katim.');
        }

        $request->validate([
            'alasan_revisi' => 'required|string|max:500',
        ]);

        $revisiId = DB::table('revisi_requests')->insertGetId([
            'diajukan_oleh' => Auth::id(),
            'jenis_dokumen' => 'monitoring',
            'dokumen_id' => $monitoring->id,
            'alasan_revisi' => $request->alasan_revisi,
            'status' => 'pending',
            'tanggal_pengajuan' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 1. UPDATE status_revisi di monitoring_harians agar terbaca di website
        DB::table('monitoring_harians')->where('id', $id)->update([
            'status_revisi' => 'pending',
            'updated_at' => now(),
        ]);

        // 2. Cari Katim terkait berdasarkan SPO
        $spo = DB::table('spos')->where('id', $monitoring->spo_id)->first();
        $katim = null;
        if ($spo && $spo->katim_id) {
            $katim = DB::table('users')->where('id', $spo->katim_id)->first();
        }

        // Fallback: Jika Katim di SPO tidak ditemukan/tidak punya chat_id, cari Katim aktif lainnya
        if (!$katim || empty($katim->telegram_chat_id)) {
            $katim = DB::table('users')
                ->where('role', 'katim')
                ->whereNotNull('telegram_chat_id')
                ->first();
        }

        // 3. Kirim Notifikasi Telegram ke Katim
        try {
            $token = env('TELEGRAM_BOT_TOKEN');
            if ($token && $katim && !empty($katim->telegram_chat_id)) {
                $noSurat = $spo ? $spo->no_surat : '-';
                $tglSiaran = \Carbon\Carbon::parse($monitoring->tanggal)->format('d M Y');

                $pesan = "⚠️ *PERMINTAAN REVISI MONITORING SIARAN*\n\n" .
                         "📄 *No. SPO*: {$noSurat}\n" .
                         "📅 *Tgl Siaran*: {$tglSiaran}\n" .
                         "👤 *Diajukan Oleh*: " . Auth::user()->name . " (Pengarah Teknik / TD)\n" .
                         "📝 *Alasan Revisi*:\n_{$request->alasan_revisi}_\n\n" .
                         "Silakan tinjau dan berikan persetujuan:";

                $urlDetail = route('monitoring.show', $monitoring->id);
                $keyboard = [
                    'inline_keyboard' => [
                        [['text' => '📄 LIHAT LAPORAN', 'url' => $urlDetail]],
                        [
                            ['text' => '✅ SETUJUI REVISI', 'callback_data' => 'accept_rev_' . $revisiId],
                            ['text' => '❌ TOLAK REVISI', 'callback_data' => 'decline_rev_' . $revisiId]
                        ]
                    ]
                ];

                Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
                    'chat_id' => $katim->telegram_chat_id,
                    'text' => $pesan,
                    'parse_mode' => 'Markdown',
                    'reply_markup' => json_encode($keyboard)
                ]);
            }
        } catch (\Exception $e) {
            Log::error("EXCEPTION TELEGRAM REVISI MONITORING: " . $e->getMessage());
        }

        $namaPenyetuju = $katim ? $katim->name : 'Ketua Tim';
        return back()->with('success', "Permintaan revisi monitoring berhasil dikirim. Menunggu persetujuan dari {$namaPenyetuju}.");
    }

    public function approveRevision($id)
    {
        if (!in_array(Auth::user()->role, ['katim', 'admin'])) abort(403);
        
        $revisi = DB::table('revisi_requests')
            ->where(function($q) use ($id) {
                $q->where('id', $id)
                  ->orWhere(function($sub) use ($id) {
                      $sub->where('jenis_dokumen', 'monitoring')
                          ->where('dokumen_id', $id);
                  });
            })
            ->where('status', 'pending')
            ->latest('id')
            ->first();

        if (!$revisi) {
            return back()->with('error', 'Tidak ada permintaan revisi pending yang ditemukan.');
        }

        $monitoringId = $revisi->dokumen_id;
        $monitoring = DB::table('monitoring_harians')->where('id', $monitoringId)->first();
        $spo = $monitoring ? DB::table('spos')->where('id', $monitoring->spo_id)->first() : null;

        // VALIDASI KETAT: Hanya Katim pemilik SPO (atau Admin) yang berhak menyetujui
        if (Auth::user()->role !== 'admin') {
            if (!$spo || $spo->katim_id != Auth::id()) {
                return back()->with('error', 'Akses Ditolak: Anda tidak berwenang menyetujui revisi monitoring milik tim Katim lain.');
            }
        }

        // 1. Update status di tabel revisi_requests
        DB::table('revisi_requests')->where('id', $revisi->id)->update([
            'status' => 'disetujui',
            'disetujui_oleh' => Auth::id(),
            'tanggal_persetujuan' => now(),
            'updated_at' => now()
        ]);

        // 2. Update status_revisi di tabel monitoring_harians
        DB::table('monitoring_harians')->where('id', $monitoringId)->update([
            'status_revisi' => 'approved',
            'updated_at' => now()
        ]);

        // 3. Notifikasi balik ke TD via Telegram
        try {
            $token = env('TELEGRAM_BOT_TOKEN');
            $td = DB::table('users')->where('id', $revisi->diajukan_oleh)->first();
            $monitoring = DB::table('monitoring_harians')->where('id', $monitoringId)->first();
            $spo = $monitoring ? DB::table('spos')->where('id', $monitoring->spo_id)->first() : null;

            if ($token && $td && !empty($td->telegram_chat_id)) {
                $noSurat = $spo ? $spo->no_surat : '-';
                $tglSiaran = $monitoring ? \Carbon\Carbon::parse($monitoring->tanggal)->format('d M Y') : '-';
                $pesanTD = "🎉 *PERMINTAAN REVISI MONITORING DISETUJUI*\n\n" .
                           "📄 *No. SPO*: {$noSurat}\n" .
                           "📅 *Tanggal Siaran*: {$tglSiaran}\n" .
                           "👤 *Disetujui Oleh*: " . Auth::user()->name . " (Katim)\n\n" .
                           "Silakan buka menu Monitoring Siaran di website untuk mulai mengedit dan memperbaiki data laporan.";

                Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
                    'chat_id' => $td->telegram_chat_id,
                    'text' => $pesanTD,
                    'parse_mode' => 'Markdown'
                ]);
            }
        } catch (\Exception $e) {
            Log::error("EXCEPTION TELEGRAM APPROVE MONITORING: " . $e->getMessage());
        }

        return back()->with('success', 'Permintaan revisi monitoring disetujui. Pengarah Teknik (TD) terkait dapat mengedit laporan.');
    }

    public function rejectRevision(Request $request, $id)
    {
        if (!in_array(Auth::user()->role, ['katim', 'admin'])) abort(403);
        
        $revisi = DB::table('revisi_requests')
            ->where(function($q) use ($id) {
                $q->where('id', $id)
                  ->orWhere(function($sub) use ($id) {
                      $sub->where('jenis_dokumen', 'monitoring')
                          ->where('dokumen_id', $id);
                  });
            })
            ->where('status', 'pending')
            ->latest('id')
            ->first();

        if (!$revisi) {
            return back()->with('error', 'Tidak ada permintaan revisi pending yang ditemukan.');
        }

        $monitoringId = $revisi->dokumen_id;
        $monitoring = DB::table('monitoring_harians')->where('id', $monitoringId)->first();
        $spo = $monitoring ? DB::table('spos')->where('id', $monitoring->spo_id)->first() : null;

        // VALIDASI KETAT: Hanya Katim pemilik SPO (atau Admin) yang berhak menolak
        if (Auth::user()->role !== 'admin') {
            if (!$spo || $spo->katim_id != Auth::id()) {
                return back()->with('error', 'Akses Ditolak: Anda tidak berwenang menolak revisi monitoring milik tim Katim lain.');
            }
        }

        $alasanPenolakan = $request->input('alasan_penolakan');
        if (empty(trim($alasanPenolakan ?? ''))) {
            $alasanPenolakan = 'Permintaan revisi monitoring tidak disetujui.';
        }

        // 1. Update status di tabel revisi_requests
        DB::table('revisi_requests')->where('id', $revisi->id)->update([
            'status' => 'ditolak',
            'alasan_penolakan' => $alasanPenolakan,
            'disetujui_oleh' => Auth::id(),
            'tanggal_persetujuan' => now(),
            'updated_at' => now()
        ]);

        // 2. Update status_revisi di tabel monitoring_harians
        DB::table('monitoring_harians')->where('id', $monitoringId)->update([
            'status_revisi' => 'rejected',
            'updated_at' => now()
        ]);

        // 3. Notifikasi balik ke TD via Telegram
        try {
            $token = env('TELEGRAM_BOT_TOKEN');
            $td = DB::table('users')->where('id', $revisi->diajukan_oleh)->first();
            $monitoring = DB::table('monitoring_harians')->where('id', $monitoringId)->first();
            $spo = $monitoring ? DB::table('spos')->where('id', $monitoring->spo_id)->first() : null;

            if ($token && $td && !empty($td->telegram_chat_id)) {
                $noSurat = $spo ? $spo->no_surat : '-';
                $tglSiaran = $monitoring ? \Carbon\Carbon::parse($monitoring->tanggal)->format('d M Y') : '-';
                $alasanClean = str_replace(['_', '*', '`', '['], ['\\_', '\\*', '\\`', '\\['], $alasanPenolakan);
                $pesanTD = "❌ *PERMINTAAN REVISI MONITORING DITOLAK*\n\n" .
                           "📄 *No. SPO*: {$noSurat}\n" .
                           "📅 *Tanggal Siaran*: {$tglSiaran}\n" .
                           "👤 *Ditolak Oleh*: " . Auth::user()->name . " (Katim)\n";
                if ($alasanPenolakan) {
                    $pesanTD .= "📝 *Alasan Penolakan*:\n_{$alasanClean}_\n\n";
                } else {
                    $pesanTD .= "\n";
                }
                $pesanTD .= "Permintaan revisi monitoring Anda tidak disetujui. Laporan tetap menggunakan data sebelumnya.";

                $resp = Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
                    'chat_id' => $td->telegram_chat_id,
                    'text' => $pesanTD,
                    'parse_mode' => 'Markdown'
                ]);
                if (!$resp->successful()) {
                    Log::error("TELEGRAM REJECT MONITORING FAILED: " . $resp->body());
                }
            }
        } catch (\Exception $e) {
            Log::error("EXCEPTION TELEGRAM REJECT MONITORING: " . $e->getMessage());
        }

        return back()->with('error', 'Permintaan revisi monitoring berhasil ditolak dan alasan telah dikirimkan ke Pengarah Teknik (TD).');
    }
}
