<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    // === FUNGSI UTAMA (INI YANG HILANG) ===
    public function index()
    {
        $user = Auth::user();

        // 1. DATA UMUM (Hitung-hitungan dasar)
        $totalSPO = DB::table('spos')->count();
        $totalLaporan = DB::table('monitoring_harians')->count();
        
        // 2. DATA KHUSUS ADMIN / KATIM (Statistik Lengkap)
        $spoPendingQuery = DB::table('spos')->where('status_persetujuan', 'pending');
        $monitoringRevisiQuery = DB::table('monitoring_harians')
            ->join('spos', 'monitoring_harians.spo_id', '=', 'spos.id')
            ->where('monitoring_harians.status_revisi', 'pending');
        $spoRevisiQuery = DB::table('revisi_requests')
            ->join('spos', 'revisi_requests.dokumen_id', '=', 'spos.id')
            ->where('revisi_requests.jenis_dokumen', 'SPO')
            ->where('revisi_requests.status', 'pending');

        if ($user->role === 'katim') {
            $spoPendingQuery->where('spos.katim_id', $user->id);
            $monitoringRevisiQuery->where('spos.katim_id', $user->id);
            $spoRevisiQuery->where('spos.katim_id', $user->id);
        }

        $stats = [
            'spo_pending' => $spoPendingQuery->count(),
            'spo_approved' => DB::table('spos')->where('status_persetujuan', 'approved')->when($user->role === 'katim', fn($q) => $q->where('katim_id', $user->id))->count(),
            'spo_rejected' => DB::table('spos')->where('status_persetujuan', 'rejected')->when($user->role === 'katim', fn($q) => $q->where('katim_id', $user->id))->count(),
            'laporan_kendala' => DB::table('monitoring_harians')
                                ->whereIn('status_siaran', ['kendala', 'gagal'])
                                ->count(),
            'monitoring_revisi_pending' => $monitoringRevisiQuery->count(),
            'spo_revisi_pending' => $spoRevisiQuery->count(),
        ];

        // 2b. DATA DIAGRAM STATISTIK (DASHBOARD ADMIN)
        $chartSPO = [
            'approved' => DB::table('spos')->where('status_persetujuan', 'approved')->count(),
            'pending' => DB::table('spos')->where('status_persetujuan', 'pending')->count(),
            'rejected' => DB::table('spos')->where('status_persetujuan', 'rejected')->count(),
        ];

        $chartMonitoring = [
            'sukses' => DB::table('monitoring_harians')->where('status_siaran', 'sukses')->count(),
            'kendala' => DB::table('monitoring_harians')->where('status_siaran', 'kendala')->count(),
            'gagal' => DB::table('monitoring_harians')->where('status_siaran', 'gagal')->count(),
        ];

        // 3. DATA REVISI / PENOLAKAN UNTUK KRU, TD & PD
        $spoDitolak = [];
        $revisiDitolak = [];
        if ($user->role == 'kru' || $user->role == 'td' || $user->role == 'pd') {
            $spoDitolak = DB::table('spos')
                ->leftJoin('users as katim', 'spos.katim_id', '=', 'katim.id')
                ->where('spos.created_by', $user->id)
                ->where('spos.status_persetujuan', 'rejected')
                ->select('spos.*', 'katim.name as nama_katim')
                ->latest('spos.updated_at')
                ->take(5)
                ->get();

            $revisiDitolak = DB::table('revisi_requests')
                ->leftJoin('users as katim', 'revisi_requests.disetujui_oleh', '=', 'katim.id')
                ->where('revisi_requests.diajukan_oleh', $user->id)
                ->where('revisi_requests.status', 'ditolak')
                ->select('revisi_requests.*', 'katim.name as nama_katim')
                ->latest('revisi_requests.updated_at')
                ->take(5)
                ->get();
        }

        // 4. DATA KHUSUS KRU (Jadwal Tugas Saya)
        $tugasSaya = [];
        
        if ($user->role == 'kru') {
            $today = date('Y-m-d');
            
            // Ambil seluruh SPO mendatang yang sudah Approved untuk dicari jadwal kru
            $allSPO = DB::table('spos')
                ->where('tanggal_pelaksanaan', '>=', $today)
                ->where('status_persetujuan', 'approved')
                ->orderBy('tanggal_pelaksanaan', 'asc')
                ->get();

            foreach($allSPO as $spo) {
                // PENGAMAN JSON (Biar gak error kalau data kosong)
                $kruList = json_decode($spo->petugas_penyiaran, true);
                if (!is_array($kruList)) $kruList = [];

                $kruList2 = json_decode($spo->petugas_penunjang, true);
                if (!is_array($kruList2)) $kruList2 = [];
                
                $gabung = array_merge($kruList, $kruList2);

                foreach($gabung as $petugas) {
                    // Cek Nama Petugas
                    $namaPetugas = $petugas['nama'] ?? ''; 
                    
                    if (!empty($namaPetugas) && str_contains(strtolower($namaPetugas), strtolower($user->name))) {
                        $spo->peran_saya = $petugas['jabatan'] ?? 'Anggota';
                        $tugasSaya[] = $spo;
                        break; 
                    }
                }

                if (count($tugasSaya) >= 5) {
                    break;
                }
            }
        }

        // 5. DATA KHUSUS TD / PENGARAH TEKNIK
        $tdStats = [
            'total_spo' => 0,
            'laporan_selesai' => 0,
            'menunggu_laporan' => 0,
            'revisi_pending' => 0,
        ];
        $tugasTD = [];
        $laporanTerbaruTD = [];

        if ($user->role === 'td' || $user->role === 'pd') {
            $nipUser = trim($user->nip ?? '');
            $namaUser = trim(strtolower($user->name));
            $pegawaiUser = !empty($nipUser) ? DB::table('pegawais')->where('nip', $nipUser)->first() : null;
            $namaPegawai = $pegawaiUser ? trim(strtolower($pegawaiUser->nama_lengkap)) : null;

            // Mapping SPO yang sudah ada laporannya [spo_id => monitoring_id]
            $laporanMap = DB::table('monitoring_harians')
                ->pluck('id', 'spo_id')
                ->toArray();

            // Ambil seluruh SPO yang berstatus Approved
            $allApprovedSPO = DB::table('spos')
                ->where('status_persetujuan', 'approved')
                ->orderBy('tanggal_pelaksanaan', 'desc')
                ->get();

            foreach ($allApprovedSPO as $spo) {
                $petugas = json_decode($spo->petugas_penyiaran, true);
                if (!is_array($petugas)) $petugas = [];

                $isMyTD = false;
                foreach ($petugas as $p) {
                    $jabatan = strtolower(trim($p['jabatan'] ?? ''));
                    if ($jabatan === 'pengarah teknik') {
                        $namaPetugas = strtolower(trim($p['nama'] ?? ''));
                        if (
                            $namaPetugas === $namaUser ||
                            ($namaPegawai && $namaPetugas === $namaPegawai) ||
                            (!empty($namaPetugas) && !empty($namaUser) && (str_contains($namaPetugas, $namaUser) || str_contains($namaUser, $namaPetugas)))
                        ) {
                            $isMyTD = true;
                            break;
                        }
                    }
                }

                if ($isMyTD) {
                    $spo->has_laporan = isset($laporanMap[$spo->id]);
                    $spo->monitoring_id = $laporanMap[$spo->id] ?? null;
                    $tugasTD[] = $spo;
                }
            }

            $tdStats['total_spo'] = count($tugasTD);
            $tdStats['spo_aktif'] = collect($tugasTD)->where('has_laporan', false)->count();
            $tdStats['menunggu_laporan'] = $tdStats['spo_aktif'];
            $tdStats['laporan_selesai'] = DB::table('monitoring_harians')->where('created_by', $user->id)->count();
            $tdStats['laporan_kendala'] = DB::table('monitoring_harians')
                ->where('created_by', $user->id)
                ->whereIn('status_siaran', ['kendala', 'gagal'])
                ->count();
            $tdStats['revisi_pending'] = DB::table('monitoring_harians')
                ->where('created_by', $user->id)
                ->where('status_revisi', 'pending')
                ->count();

            // 5 Laporan Monitoring Terakhir Buatan TD Ini
            $laporanTerbaruTD = DB::table('monitoring_harians')
                ->leftJoin('spos', 'monitoring_harians.spo_id', '=', 'spos.id')
                ->where('monitoring_harians.created_by', $user->id)
                ->select('monitoring_harians.*', 'spos.no_surat', 'spos.hari', 'spos.tanggal_pelaksanaan as tgl_spo')
                ->latest('monitoring_harians.created_at')
                ->take(5)
                ->get();
        }

        // Panggil View Dashboard Baru
        return view('dashboard.index', compact(
            'totalSPO',
            'totalLaporan',
            'stats',
            'chartSPO',
            'chartMonitoring',
            'tugasSaya',
            'spoDitolak',
            'revisiDitolak',
            'tdStats',
            'tugasTD',
            'laporanTerbaruTD'
        ));
    }

    // === METODE SINKRONISASI OTOMATIS REAL-TIME (AUTO-REFRESH DETECTOR) ===
    public function syncCheck()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['status' => 'unauthenticated'], 401);
        }

        // Ambil timestamp pembaruan terakhir dan jumlah baris data utama
        $spoMax = DB::table('spos')->max('updated_at') ?? '';
        $monMax = DB::table('monitoring_harians')->max('updated_at') ?? '';
        $revMax = DB::table('revisi_requests')->max('updated_at') ?? '';
        $userMax = DB::table('users')->max('updated_at') ?? '';
        $alatMax = DB::table('alats')->max('updated_at') ?? '';
        $pegawaiMax = DB::table('pegawais')->max('updated_at') ?? '';

        $spoCount = DB::table('spos')->count();
        $monCount = DB::table('monitoring_harians')->count();
        $revCount = DB::table('revisi_requests')->count();
        $userCount = DB::table('users')->count();

        $stateString = implode('|', [
            $spoMax, $monMax, $revMax, $userMax, $alatMax, $pegawaiMax,
            $spoCount, $monCount, $revCount, $userCount
        ]);

        $hash = md5($stateString);

        return response()->json([
            'status' => 'ok',
            'hash' => $hash,
            'timestamp' => now()->timestamp
        ]);
    }
}