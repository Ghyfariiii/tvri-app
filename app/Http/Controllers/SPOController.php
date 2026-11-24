<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Gate;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class SPOController extends Controller
{
    public function index()
    {
        $role = Auth::user()->role;
        $userId = Auth::id();

        $spos = collect(); 
        $sposGlobal = collect();
        
        $revisiPending = DB::table('revisi_requests')
            ->where('status', 'pending')
            ->where('jenis_dokumen', 'SPO')
            ->pluck('id', 'dokumen_id')
            ->toArray();

        $revisiDisetujui = DB::table('revisi_requests')
            ->where('status', 'disetujui')
            ->where('jenis_dokumen', 'SPO')
            ->pluck('id', 'dokumen_id')
            ->toArray();

        if ($role === 'kru') {
            $spos = DB::table('spos')
                ->join('users', 'spos.created_by', '=', 'users.id')
                ->leftJoin('users as u_katim', 'spos.katim_id', '=', 'u_katim.id')
                ->select('spos.*', 'users.name as nama_pembuat', 'u_katim.name as nama_katim')
                ->where('spos.created_by', $userId)
                ->orderBy('spos.tanggal_pelaksanaan', 'desc')
                ->get();
        } elseif ($role === 'katim') {
            $spos = DB::table('spos')
                ->join('users', 'spos.created_by', '=', 'users.id')
                ->leftJoin('users as u_katim', 'spos.katim_id', '=', 'u_katim.id')
                ->select('spos.*', 'users.name as nama_pembuat', 'u_katim.name as nama_katim')
                ->where('spos.katim_id', $userId)
                ->orderBy('spos.status_persetujuan', 'desc')
                ->orderBy('spos.tanggal_pelaksanaan', 'desc')
                ->get();

            $sposGlobal = DB::table('spos')
                ->join('users', 'spos.created_by', '=', 'users.id')
                ->leftJoin('users as u_katim', 'spos.katim_id', '=', 'u_katim.id')
                ->select('spos.*', 'users.name as nama_pembuat', 'u_katim.name as nama_katim')
                ->where('spos.status_persetujuan', 'approved')
                ->orderBy('spos.tanggal_pelaksanaan', 'desc')
                ->get();
        } elseif ($role === 'admin' || $role === 'td' || $role === 'pd') {
            $sposGlobal = DB::table('spos')
                ->join('users', 'spos.created_by', '=', 'users.id')
                ->leftJoin('users as u_katim', 'spos.katim_id', '=', 'u_katim.id')
                ->select('spos.*', 'users.name as nama_pembuat', 'u_katim.name as nama_katim')
                ->orderBy('spos.tanggal_pelaksanaan', 'desc')
                ->get();
        }

        foreach($spos as $spo) {
            $penyiaran = json_decode($spo->petugas_penyiaran, true) ?? [];
            $pd_nama = '-';
            foreach($penyiaran as $p) {
                if($p['jabatan'] == 'Pengarah Teknik') { $pd_nama = $p['nama']; break; }
            }
            $spo->pengarah_teknik = $pd_nama; 
        }

        if ($role === 'katim' || $role === 'admin' || $role === 'td' || $role === 'pd') {
            foreach($sposGlobal as $spo) {
                $penyiaran = json_decode($spo->petugas_penyiaran, true) ?? [];
                $pd_nama = '-';
                foreach($penyiaran as $p) {
                    if($p['jabatan'] == 'Pengarah Teknik') { $pd_nama = $p['nama']; break; }
                }
                $spo->pengarah_teknik = $pd_nama; 
            }
        }

        return view('spo.index', compact('spos', 'sposGlobal', 'role', 'revisiPending', 'revisiDisetujui'));
    }

    public function create()
    {
        if (!Gate::allows('kru')) abort(403); 

        $pegawais = DB::table('pegawais')->orderBy('nama_lengkap', 'asc')->get();
        $katims = DB::table('users')->where('role', 'katim')->orderBy('name', 'asc')->get();
        
        $lastSPO = DB::table('spos')->latest('id')->first();
        $nextNo = '001'; 
        if ($lastSPO) {
            $parts = explode('/', $lastSPO->no_surat);
            if (isset($parts[0]) && is_numeric($parts[0])) {
                $nextNo = str_pad(intval($parts[0]) + 1, 3, '0', STR_PAD_LEFT); 
            }
        }

        return view('spo.create', compact('pegawais', 'katims', 'nextNo'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nomor_urut' => 'required',
            'format_surat' => 'required',
            'hari' => 'required',
            'tanggal_pelaksanaan' => 'required|date',
            'katim_id' => 'required',
        ]);

        $no_surat_final = $request->nomor_urut . $request->format_surat;

        // Validasi keunikan nomor surat agar tidak terjadi duplikasi nomor SPO
        if (DB::table('spos')->where('no_surat', $no_surat_final)->exists()) {
            return back()->withInput()->with('error', "Nomor surat {$no_surat_final} sudah pernah dibuat. Silakan gunakan nomor urut yang berbeda.");
        }

        $data_penyiaran = [];
        if($request->has('penyiaran') && is_array($request->penyiaran)){
            foreach($request->penyiaran as $item){
                $jabatan = ($item['jabatan'] == 'Lainnya') ? ($item['custom_jabatan'] ?? '-') : ($item['jabatan'] ?? '');
                $nama = ($item['nama'] == 'Lainnya') ? ($item['custom_nama'] ?? '-') : ($item['nama'] ?? '');
                if(!empty($jabatan)) $data_penyiaran[] = ['jabatan' => $jabatan, 'nama' => $nama];
            }
        }

        $data_penunjang = [];
        if($request->has('penunjang') && is_array($request->penunjang)){
            foreach($request->penunjang as $item){
                $jabatan = ($item['jabatan'] == 'Lainnya') ? ($item['custom_jabatan'] ?? '-') : ($item['jabatan'] ?? '');
                $nama = ($item['nama'] == 'Lainnya') ? ($item['custom_nama'] ?? '-') : ($item['nama'] ?? '');
                if(!empty($jabatan)) $data_penunjang[] = ['jabatan' => $jabatan, 'nama' => $nama];
            }
        }

        $spoId = DB::table('spos')->insertGetId([
            'katim_id' => $request->katim_id,
            'no_surat' => $no_surat_final, 
            'hari' => $request->hari,
            'tanggal_pelaksanaan' => $request->tanggal_pelaksanaan,
            'petugas_penyiaran' => json_encode($data_penyiaran),
            'petugas_penunjang' => json_encode($data_penunjang),
            'created_by' => Auth::id(),
            'status_persetujuan' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        try {
            $token = env('TELEGRAM_BOT_TOKEN');
            $katimPilihan = DB::table('users')->where('id', $request->katim_id)->first();

            if ($token && $katimPilihan && !empty($katimPilihan->telegram_chat_id)) {
                $pesan = "🔔 *PENGAJUAN SPO BARU*\nNo: $no_surat_final\nOleh: " . Auth::user()->name;
                
                // PENAMBAHAN TOMBOL URL LIHAT DOKUMEN
                $urlSPO = route('spo.verify', $spoId);
                $keyboard = [
                    'inline_keyboard' => [
                        [['text' => '📄 LIHAT DOKUMEN', 'url' => $urlSPO]],
                        [['text' => '✅ SETUJUI', 'callback_data' => 'approve_' . $spoId], ['text' => '❌ TOLAK', 'callback_data' => 'reject_' . $spoId]]
                    ]
                ];

                Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
                    'chat_id' => $katimPilihan->telegram_chat_id, 
                    'text' => $pesan, 
                    'parse_mode' => 'Markdown', 
                    'reply_markup' => json_encode($keyboard)
                ]);
            }
        } catch (\Exception $e) {
            \Log::error("EXCEPTION TELEGRAM: " . $e->getMessage());
        }

        return redirect()->route('spo.index')->with('success', 'SPO berhasil diajukan!');
    }

    public function edit($id)
    {
        $spo = DB::table('spos')->where('id', $id)->first();
        if (!$spo) abort(404);


        $izinRevisi = DB::table('revisi_requests')
            ->where('jenis_dokumen', 'SPO')
            ->where('dokumen_id', $id)
            ->where('status', 'disetujui')
            ->first();

        if ($spo->status_persetujuan === 'approved' && !$izinRevisi) {
            return back()->with('error', 'SPO yang sudah disetujui tidak dapat diubah tanpa pengajuan revisi.');
        }

        if ($spo->created_by != Auth::id() && Auth::user()->role !== 'admin') {
            return back()->with('error', 'Anda tidak berwenang mengubah SPO ini.');
        }

        $pegawais = DB::table('pegawais')->orderBy('nama_lengkap', 'asc')->get();
        $katims = DB::table('users')->where('role', 'katim')->orderBy('name', 'asc')->get();

        $petugas_penyiaran = json_decode($spo->petugas_penyiaran, true) ?? [];
        $petugas_penunjang = json_decode($spo->petugas_penunjang, true) ?? [];

        return view('spo.edit', compact('spo', 'pegawais', 'katims', 'petugas_penyiaran', 'petugas_penunjang'));
    }

    public function update(Request $request, $id)
    {
        $spo = DB::table('spos')->where('id', $id)->first();
        if (!$spo) abort(404);

        // Otorisasi ketat: Hanya kru pembuat SPO atau Admin yang berwenang mengubah SPO ini
        if ($spo->created_by != Auth::id() && Auth::user()->role !== 'admin') {
            return back()->with('error', 'Anda tidak berwenang mengubah SPO ini.');
        }

        $izinRevisi = DB::table('revisi_requests')
            ->where('jenis_dokumen', 'SPO')
            ->where('dokumen_id', $id)
            ->where('status', 'disetujui')
            ->first();

        if ($spo->status_persetujuan === 'approved' && !$izinRevisi) {
            return back()->with('error', 'SPO yang sudah disetujui tidak dapat diubah.');
        }

        $request->validate([
            'hari' => 'required',
            'tanggal_pelaksanaan' => 'required|date',
            'katim_id' => 'required',
        ]);

        $no_surat_final = $spo->no_surat;

        $data_penyiaran = [];
        if($request->has('penyiaran') && is_array($request->penyiaran)){
            foreach($request->penyiaran as $item){
                $jabatan = ($item['jabatan'] == 'Lainnya') ? ($item['custom_jabatan'] ?? '-') : ($item['jabatan'] ?? '');
                $nama = ($item['nama'] == 'Lainnya') ? ($item['custom_nama'] ?? '-') : ($item['nama'] ?? '');
                if(!empty($jabatan)) $data_penyiaran[] = ['jabatan' => $jabatan, 'nama' => $nama];
            }
        }

        $data_penunjang = [];
        if($request->has('penunjang') && is_array($request->penunjang)){
            foreach($request->penunjang as $item){
                $jabatan = ($item['jabatan'] == 'Lainnya') ? ($item['custom_jabatan'] ?? '-') : ($item['jabatan'] ?? '');
                $nama = ($item['nama'] == 'Lainnya') ? ($item['custom_nama'] ?? '-') : ($item['nama'] ?? '');
                if(!empty($jabatan)) $data_penunjang[] = ['jabatan' => $jabatan, 'nama' => $nama];
            }
        }

        $statusLama = $spo->status_persetujuan;

        DB::table('spos')->where('id', $id)->update([
            'katim_id' => $request->katim_id,
            'hari' => $request->hari,
            'tanggal_pelaksanaan' => $request->tanggal_pelaksanaan,
            'petugas_penyiaran' => json_encode($data_penyiaran),
            'petugas_penunjang' => json_encode($data_penunjang),
            'status_persetujuan' => 'pending', 
            'updated_at' => now(),
        ]);

        $katimPilihan = DB::table('users')->where('id', $request->katim_id)->first();
        $token = env('TELEGRAM_BOT_TOKEN');

        if ($izinRevisi) {
            DB::table('revisi_requests')
                ->where('jenis_dokumen', 'SPO')
                ->where('dokumen_id', $id)
                ->delete();
            
            try {
                if ($token && $katimPilihan && !empty($katimPilihan->telegram_chat_id)) {
                    $pesan = "🔄 *SPO HASIL REVISI TELAH DIKIRIM*\nKru telah selesai memperbaiki SPO sesuai permintaan revisi Anda.\n\nNo: $no_surat_final\nOleh: " . Auth::user()->name;
                    
                    // PENAMBAHAN TOMBOL URL LIHAT DOKUMEN
                    $urlSPO = route('spo.verify', $id);
                    $keyboard = [
                        'inline_keyboard' => [
                            [['text' => '📄 LIHAT DOKUMEN', 'url' => $urlSPO]],
                            [['text' => '✅ SETUJUI', 'callback_data' => 'approve_' . $id], ['text' => '❌ TOLAK', 'callback_data' => 'reject_' . $id]]
                        ]
                    ];
                    
                    Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
                        'chat_id' => $katimPilihan->telegram_chat_id, 
                        'text' => $pesan, 
                        'parse_mode' => 'Markdown', 
                        'reply_markup' => json_encode($keyboard)
                    ]);
                }
            } catch (\Exception $e) { \Log::error("TELEGRAM: " . $e->getMessage()); }
            
            return redirect()->route('spo.index')->with('success', 'SPO hasil revisi berhasil disimpan dan diajukan ulang ke Katim!');
        }

        if ($statusLama === 'rejected') {
            try {
                if ($token && $katimPilihan && !empty($katimPilihan->telegram_chat_id)) {
                    $pesan = "🔄 *PENGAJUAN ULANG SPO*\nSPO yang sebelumnya ditolak telah diperbarui.\n\nNo: $no_surat_final\nOleh: " . Auth::user()->name;
                    
                    // PENAMBAHAN TOMBOL URL LIHAT DOKUMEN
                    $urlSPO = route('spo.verify', $id);
                    $keyboard = [
                        'inline_keyboard' => [
                            [['text' => '📄 LIHAT DOKUMEN', 'url' => $urlSPO]],
                            [['text' => '✅ SETUJUI', 'callback_data' => 'approve_' . $id], ['text' => '❌ TOLAK', 'callback_data' => 'reject_' . $id]]
                        ]
                    ];

                    Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
                        'chat_id' => $katimPilihan->telegram_chat_id, 
                        'text' => $pesan, 
                        'parse_mode' => 'Markdown', 
                        'reply_markup' => json_encode($keyboard)
                    ]);
                }
            } catch (\Exception $e) {
                \Log::error("EXCEPTION TELEGRAM UPDATE: " . $e->getMessage());
            }
        }

        return redirect()->route('spo.index')->with('success', 'SPO berhasil diperbarui dan diajukan ulang ke Katim!');
    }

    public function destroy($id)
    {
        $spo = DB::table('spos')->where('id', $id)->first();
        if (!$spo) abort(404);


        // Otorisasi: Hanya pembuat SPO atau Admin yang boleh menghapus
        if ($spo->created_by != Auth::id() && Auth::user()->role !== 'admin') {
            abort(403, 'Anda tidak berwenang menghapus SPO ini.');
        }

        if ($spo->status_persetujuan === 'approved') {
            return back()->with('error', 'SPO yang sudah disetujui tidak dapat dihapus.');
        }

        // Cegah penghapusan jika SPO sudah memiliki laporan monitoring siaran
        if (DB::table('monitoring_harians')->where('spo_id', $id)->exists()) {
            return back()->with('error', 'SPO tidak dapat dihapus karena sudah memiliki laporan monitoring siaran terkait.');
        }

        DB::transaction(function () use ($id) {
            // Hapus juga riwayat revisi SPO terkait jika ada
            DB::table('revisi_requests')
                ->where('jenis_dokumen', 'SPO')
                ->where('dokumen_id', $id)
                ->delete();

            DB::table('spos')->where('id', $id)->delete();
        });

        return redirect()->route('spo.index')->with('success', 'SPO berhasil dihapus.');
    }

    public function approve($id) 
    { 
        if (!in_array(Auth::user()->role, ['katim', 'admin'])) abort(403); 
        $spo = DB::table('spos')->where('id', $id)->first();
        if (!$spo) abort(404);


        if (Auth::user()->role !== 'admin' && $spo->katim_id != Auth::id()) {
            return back()->with('error', 'Anda tidak berwenang menyetujui dokumen milik Katim lain.');
        }

        DB::table('spos')->where('id', $id)->update([
            'status_persetujuan' => 'approved', 
            'disetujui_oleh_nip' => Auth::user()->nip ?? '-', 
            'tanggal_disetujui' => now(), 
            'updated_at' => now()
        ]); 

        // Notifikasi ke Kru via Telegram
        $token = env('TELEGRAM_BOT_TOKEN');
        if ($token && $spo && $spo->created_by) {
            $kru = DB::table('users')->where('id', $spo->created_by)->first();
            if ($kru && !empty($kru->telegram_chat_id)) {
                $penyetujuNama = Auth::user()->name;
                $pesanKru = "🎉 *SPO ANDA TELAH DISETUJUI!*\n\n" .
                            "Nomor Surat: {$spo->no_surat}\n" .
                            "Status: *Disetujui oleh {$penyetujuNama}*\n\n" .
                            "Silakan buka website Sistem Operasional TVRI untuk mengunduh dokumen SPO dan QR Code.";
                try {
                    Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
                        'chat_id' => $kru->telegram_chat_id,
                        'text' => $pesanKru,
                        'parse_mode' => 'Markdown',
                    ]);
                } catch (\Exception $e) { \Log::error("TELEGRAM APPROVE SPO: " . $e->getMessage()); }
            }
        }

        return back()->with('success', 'SPO Disetujui.'); 
    }

    public function reject(Request $request, $id) 
    { 
        if (!in_array(Auth::user()->role, ['katim', 'admin'])) abort(403); 
        $spo = DB::table('spos')->where('id', $id)->first();
        if (!$spo) abort(404);


        if (Auth::user()->role !== 'admin' && $spo->katim_id != Auth::id()) {
            return back()->with('error', 'Anda tidak berwenang menolak dokumen milik Katim lain.');
        }

        $alasanPenolakan = $request->input('alasan_penolakan');
        if (empty(trim($alasanPenolakan ?? ''))) {
            $alasanPenolakan = 'Ditolak tanpa catatan tambahan.';
        }

        DB::table('spos')->where('id', $id)->update([
            'status_persetujuan' => 'rejected', 
            'alasan_penolakan' => $alasanPenolakan,
            'disetujui_oleh_nip' => Auth::user()->nip ?? '-',
            'updated_at' => now()
        ]); 

        // Notifikasi ke Kru via Telegram
        $token = env('TELEGRAM_BOT_TOKEN');
        if ($token && $spo && $spo->created_by) {
            $kru = DB::table('users')->where('id', $spo->created_by)->first();
            if ($kru && !empty($kru->telegram_chat_id)) {
                $penolakNama = Auth::user()->name;
                $alasanClean = str_replace(['_', '*', '`', '['], ['\\_', '\\*', '\\`', '\\['], $alasanPenolakan);
                $pesanKru = "❌ *SPO ANDA DITOLAK*\n\n" .
                            "📄 *Nomor Surat*: {$spo->no_surat}\n" .
                            "👤 *Ditolak Oleh*: {$penolakNama} (Katim)\n" .
                            "📝 *Alasan Penolakan*:\n_{$alasanClean}_\n\n" .
                            "Silakan periksa kembali dan perbaiki pengajuan Anda pada sistem.";
                try {
                    $resp = Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
                        'chat_id' => $kru->telegram_chat_id,
                        'text' => $pesanKru,
                        'parse_mode' => 'Markdown',
                    ]);
                    if (!$resp->successful()) {
                        \Log::error("TELEGRAM REJECT SPO FAILED: " . $resp->body());
                    }
                } catch (\Exception $e) { \Log::error("TELEGRAM REJECT SPO: " . $e->getMessage()); }
            }
        }

        return back()->with('error', 'SPO berhasil ditolak dan alasan telah dikirimkan ke kru.'); 
    }

    public function downloadPDF($id) 
    { 
        $spo = DB::table('spos')->where('id', $id)->first(); 
        if (!$spo || $spo->status_persetujuan !== 'approved') abort(404); 
        $this->mapJsonToProperties($spo); 

        $katim = DB::table('users')->where('id', $spo->katim_id)->first();
        $qrcode = base64_encode(QrCode::format('svg')->size(100)->generate(route('spo.verify', $spo->id))); 
        
        $pdf = Pdf::loadView('spo.print', ['spo' => $spo, 'qrcode' => $qrcode, 'katim' => $katim]); 
        $pdf->setPaper('a4', 'portrait'); 
        return $pdf->stream('SPO.pdf'); 
    }

    public function verify($id) 
    { 
        $spo = DB::table('spos')->where('id', $id)->first(); 
        if($spo) $this->mapJsonToProperties($spo); 
        $penyetuju = ($spo && $spo->katim_id) ? DB::table('users')->where('id', $spo->katim_id)->first() : null; 
        return view('spo.verify', compact('spo', 'penyetuju')); 
    }

    private function mapJsonToProperties($spo) 
    { 
        $fields = [
            'Pengarah Teknik' => 'pengarah_teknik', 'Maintenance' => 'maintenance', 
            'Operator Komputer' => 'operator_komputer', 'Penata Aksara' => 'penata_aksara', 
            'Pemadu Gambar' => 'pemadu_gambar', 'Kamerawan 1' => 'kamerawan1', 
            'Kamerawan 2' => 'kamerawan2', 'Kamerawan 3' => 'kamerawan3', 
            'Penata Suara' => 'penata_suara', 'Dokumentasi Digital & VTR' => 'dokumentasi', 
            'Petugas IT' => 'it_support'
        ]; 
        foreach($fields as $k => $v) $spo->$v = '-'; 
        foreach(json_decode($spo->petugas_penyiaran, true) ?? [] as $p) 
            if(isset($fields[$p['jabatan']])) $spo->{$fields[$p['jabatan']]} = $p['nama']; 
        foreach(json_decode($spo->petugas_penunjang, true) ?? [] as $p) 
            if(isset($fields[$p['jabatan']])) $spo->{$fields[$p['jabatan']]} = $p['nama']; 
    }

    public function requestRevision(Request $request, $id)
    {
        $spo = DB::table('spos')->where('id', $id)->first();
        if (!$spo) abort(404);


        if ($spo->status_persetujuan !== 'approved') {
            return back()->with('error', 'Hanya SPO yang sudah disetujui yang dapat diajukan revisinya.');
        }

        if ($spo->created_by != Auth::id() && Auth::user()->role !== 'admin') {
            return back()->with('error', 'Anda tidak berwenang mengajukan revisi SPO ini.');
        }

        // Cegah spam/duplikasi pengajuan revisi yang masih berstatus pending
        $adaPending = DB::table('revisi_requests')
            ->where('jenis_dokumen', 'SPO')
            ->where('dokumen_id', $spo->id)
            ->where('status', 'pending')
            ->exists();

        if ($adaPending) {
            return back()->with('error', 'Permintaan revisi untuk SPO ini sudah diajukan dan sedang menunggu tanggapan Katim.');
        }

        $request->validate([
            'alasan_revisi' => 'required|string|max:500',
        ]);

        $revisiId = DB::table('revisi_requests')->insertGetId([
            'diajukan_oleh' => Auth::id(),
            'jenis_dokumen' => 'SPO',
            'dokumen_id' => $spo->id,
            'alasan_revisi' => $request->alasan_revisi,
            'status' => 'pending',
            'tanggal_pengajuan' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $katimPilihan = DB::table('users')->where('id', $spo->katim_id)->first();
        $namaKatim = $katimPilihan ? $katimPilihan->name : 'Ketua Tim';

        try {
            $token = env('TELEGRAM_BOT_TOKEN');

            if ($token && $katimPilihan && !empty($katimPilihan->telegram_chat_id)) {
                $pesan = "⚠️ *PERMINTAAN REVISI SPO*\nNo: {$spo->no_surat}\nOleh: " . Auth::user()->name . "\nAlasan: {$request->alasan_revisi}";
                
                // PENAMBAHAN TOMBOL URL LIHAT DOKUMEN
                $urlSPO = route('spo.verify', $spo->id);
                $keyboard = [
                    'inline_keyboard' => [
                        [['text' => '📄 LIHAT DOKUMEN', 'url' => $urlSPO]],
                        [
                            ['text' => '✅ SETUJUI REVISI', 'callback_data' => 'accept_rev_' . $revisiId], 
                            ['text' => '❌ TOLAK REVISI', 'callback_data' => 'decline_rev_' . $revisiId]
                        ]
                    ]
                ];

                Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
                    'chat_id' => $katimPilihan->telegram_chat_id, 
                    'text' => $pesan, 
                    'parse_mode' => 'Markdown', 
                    'reply_markup' => json_encode($keyboard)
                ]);
            }
        } catch (\Exception $e) {
            \Log::error("EXCEPTION TELEGRAM REVISI: " . $e->getMessage());
        }

        return back()->with('success', "Permintaan revisi SPO berhasil dikirim. Menunggu persetujuan dari {$namaKatim}.");
    }

    public function approveRevision($revisiId)
    {
        if (!in_array(Auth::user()->role, ['katim', 'admin'])) abort(403);
        
        $revisi = DB::table('revisi_requests')
            ->where('id', $revisiId)
            ->where('jenis_dokumen', 'SPO')
            ->first();
        if (!$revisi) abort(404);

        $spo = DB::table('spos')->where('id', $revisi->dokumen_id)->first();
        if (!$spo) abort(404);

        // VALIDASI KETAT: Hanya Katim pemilik SPO atau Admin
        if (Auth::user()->role !== 'admin' && $spo->katim_id != Auth::id()) {
            return back()->with('error', 'Akses Ditolak: Anda tidak berwenang menyetujui revisi SPO milik Katim lain.');
        }

        DB::table('revisi_requests')->where('id', $revisiId)->update([
            'status' => 'disetujui',
            'disetujui_oleh' => Auth::id(),
            'tanggal_persetujuan' => now(),
            'updated_at' => now()
        ]);

        // Notifikasi ke Kru via Telegram
        $token = env('TELEGRAM_BOT_TOKEN');
        if ($token && $revisi->diajukan_oleh) {
            $kru = DB::table('users')->where('id', $revisi->diajukan_oleh)->first();
            if ($kru && !empty($kru->telegram_chat_id)) {
                $penyetujuNama = Auth::user()->name;
                $pesanKru = "🎉 *PERMINTAAN REVISI DISETUJUI*\n\n" .
                            "Nomor Surat: {$spo->no_surat}\n" .
                            "Status: *Disetujui oleh {$penyetujuNama}*\n\n" .
                            "Silakan buka halaman Arsip SPO di website untuk mulai mengedit dan memperbaiki data SPO tersebut.";
                try {
                    Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
                        'chat_id' => $kru->telegram_chat_id,
                        'text' => $pesanKru,
                        'parse_mode' => 'Markdown',
                    ]);
                } catch (\Exception $e) { \Log::error("TELEGRAM APPROVE REVISI SPO: " . $e->getMessage()); }
            }
        }

        return back()->with('success', 'Permintaan revisi disetujui. Kru terkait sekarang dapat mengubah dokumen tersebut.');
    }

    public function rejectRevision(Request $request, $revisiId)
    {
        if (!in_array(Auth::user()->role, ['katim', 'admin'])) abort(403);
        
        $revisi = DB::table('revisi_requests')
            ->where('id', $revisiId)
            ->where('jenis_dokumen', 'SPO')
            ->first();
        if (!$revisi) abort(404);

        $spo = DB::table('spos')->where('id', $revisi->dokumen_id)->first();
        if (!$spo) abort(404);

        // VALIDASI KETAT: Hanya Katim pemilik SPO atau Admin
        if (Auth::user()->role !== 'admin' && $spo->katim_id != Auth::id()) {
            return back()->with('error', 'Akses Ditolak: Anda tidak berwenang menolak revisi SPO milik Katim lain.');
        }

        $alasanPenolakan = $request->input('alasan_penolakan');
        if (empty(trim($alasanPenolakan ?? ''))) {
            $alasanPenolakan = 'Permintaan revisi tidak disetujui.';
        }

        DB::table('revisi_requests')->where('id', $revisiId)->update([
            'status' => 'ditolak',
            'alasan_penolakan' => $alasanPenolakan,
            'disetujui_oleh' => Auth::id(),
            'tanggal_persetujuan' => now(),
            'updated_at' => now()
        ]);

        // Notifikasi ke Kru via Telegram
        $token = env('TELEGRAM_BOT_TOKEN');
        if ($token && $revisi->diajukan_oleh) {
            $kru = DB::table('users')->where('id', $revisi->diajukan_oleh)->first();
            if ($kru && !empty($kru->telegram_chat_id)) {
                $penolakNama = Auth::user()->name;
                $alasanClean = str_replace(['_', '*', '`', '['], ['\\_', '\\*', '\\`', '\\['], $alasanPenolakan);
                $pesanKru = "❌ *PERMINTAAN REVISI SPO DITOLAK*\n\n" .
                            "📄 *Nomor Surat*: {$spo->no_surat}\n" .
                            "👤 *Ditolak Oleh*: {$penolakNama} (Katim)\n" .
                            "📝 *Alasan Penolakan*:\n_{$alasanClean}_\n\n" .
                            "Permintaan Anda untuk merevisi SPO tersebut tidak disetujui. Dokumen tetap pada versi aslinya.";
                try {
                    $resp = Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
                        'chat_id' => $kru->telegram_chat_id,
                        'text' => $pesanKru,
                        'parse_mode' => 'Markdown',
                    ]);
                    if (!$resp->successful()) {
                        \Log::error("TELEGRAM REJECT REVISI SPO FAILED: " . $resp->body());
                    }
                } catch (\Exception $e) { \Log::error("TELEGRAM REJECT REVISI SPO: " . $e->getMessage()); }
            }
        }

        return back()->with('error', 'Permintaan revisi berhasil ditolak dan alasan telah dikirimkan ke kru.');
    }
}