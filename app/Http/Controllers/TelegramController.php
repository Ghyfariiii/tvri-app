<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Models\User; 

class TelegramController extends Controller
{
    public function handle(Request $request)
    {
        // 0. VALIDASI KEAMANAN WEBHOOK TELEGRAM (Secret Token)
        $secretToken = env('TELEGRAM_WEBHOOK_SECRET');
        if (!empty($secretToken)) {
            $incomingToken = $request->header('X-Telegram-Bot-Api-Secret-Token');
            if ($incomingToken !== $secretToken) {
                Log::warning('Percobaan akses webhook Telegram tidak sah dari IP: ' . $request->ip());
                return response()->json(['error' => 'Unauthorized: Invalid secret token'], 401);
            }
        }

        try {
            $data = $request->all();

            // 1. CEK PESAN TEKS MASUK
            if (isset($data['message']['text'])) {
                $chat_id = $data['message']['chat']['id'];
                $text = trim($data['message']['text']);

                // A. Fitur Account Binding
                if (str_starts_with($text, '/bind')) {
                    $this->prosesBinding($text, $chat_id);
                    return response('OK', 200);
                }

                // B. Batalkan Aksi Penolakan jika mengetik /batal
                if ($text === '/batal' || strcasecmp($text, 'batal') === 0) {
                    if (Cache::has('telegram_pending_' . $chat_id)) {
                        Cache::forget('telegram_pending_' . $chat_id);
                        $this->kirimPesan($chat_id, "ℹ️ Tindakan penolakan telah dibatalkan.");
                        return response('OK', 200);
                    }
                }

                // C. Cek apakah user sedang dalam sesi menunggu input alasan penolakan
                if (Cache::has('telegram_pending_' . $chat_id)) {
                    $pending = Cache::get('telegram_pending_' . $chat_id);
                    Cache::forget('telegram_pending_' . $chat_id);

                    if (($pending['action'] ?? '') === 'reject_spo') {
                        $this->selesaikanRejectSPO($pending, $text, $chat_id);
                        return response('OK', 200);
                    } elseif (($pending['action'] ?? '') === 'decline_rev') {
                        $this->selesaikanDeclineRevision($pending, $text, $chat_id);
                        return response('OK', 200);
                    }
                }
            }

            // 2. CEK KLIK TOMBOL INLINE
            if (isset($data['callback_query'])) {
                $callback = $data['callback_query'];
                $chat_id = $callback['message']['chat']['id'];
                $data_tombol = $callback['data']; 
                
                // Hentikan animasi loading memutar pada tombol Telegram
                $token = env('TELEGRAM_BOT_TOKEN');
                Http::post("https://api.telegram.org/bot{$token}/answerCallbackQuery", [
                    'callback_query_id' => $callback['id']
                ]);

                // Bersihkan sesi penolakan lama jika Katim mengklik tombol baru
                Cache::forget('telegram_pending_' . $chat_id);
                
                if (str_starts_with($data_tombol, 'approve_')) {
                    $id = str_replace('approve_', '', $data_tombol);
                    $this->prosesApprove($id, $chat_id);
                } elseif (str_starts_with($data_tombol, 'reject_')) {
                    $id = str_replace('reject_', '', $data_tombol);
                    $this->mintaAlasanRejectSPO($id, $chat_id);
                } elseif (str_starts_with($data_tombol, 'accept_rev_')) {
                    $id = str_replace('accept_rev_', '', $data_tombol);
                    $this->prosesAcceptRevision($id, $chat_id);
                } elseif (str_starts_with($data_tombol, 'decline_rev_')) {
                    $id = str_replace('decline_rev_', '', $data_tombol);
                    $this->mintaAlasanDeclineRevision($id, $chat_id);
                }
            }

            return response('OK', 200);

        } catch (\Exception $e) {
            Log::error('TELEGRAM WEBHOOK ERROR: ' . $e->getMessage());
            return response('Error Captured', 200); 
        }
    }

    private function prosesBinding($text, $chat_id)
    {
        $token = trim(str_replace('/bind', '', $text));
        $user = User::where('telegram_bind_token', $token)->first();

        if ($user) {
            $user->update([
                'telegram_chat_id' => $chat_id,
                'telegram_bind_token' => null
            ]);
            
            $this->kirimPesan($chat_id, "✅ *BERHASIL!* Akun Anda ({$user->name}) telah terhubung dengan Sistem Operasional TVRI.");
        } else {
            $this->kirimPesan($chat_id, "❌ *GAGAL.* Token tidak valid atau sudah kadaluarsa. Silakan periksa kembali profil Anda di website.");
        }
    }

    private function prosesApprove($id, $chat_id)
    {
        $spo = DB::table('spos')->where('id', $id)->first();
        if (!$spo) {
            $this->kirimPesan($chat_id, "❌ *ERROR:* Dokumen SPO tidak ditemukan.");
            return;
        }

        $katim = DB::table('users')->where('telegram_chat_id', $chat_id)->first();
        if (!$katim || !in_array($katim->role, ['katim', 'admin'])) {
            $this->kirimPesan($chat_id, "❌ *AKSES DITOLAK.* Akun Telegram Anda tidak terdaftar sebagai Katim atau Administrator.");
            return;
        }

        // VALIDASI KETAT: Hanya Katim pemilik SPO atau Admin
        if ($katim->role !== 'admin' && $spo->katim_id != $katim->id) {
            $this->kirimPesan($chat_id, "❌ *AKSES DITOLAK.* Anda tidak berwenang menyetujui dokumen SPO milik tim Katim lain.");
            return;
        }

        if ($spo->status_persetujuan === 'approved') {
            $this->kirimPesan($chat_id, "ℹ️ SPO (*{$spo->no_surat}*) sudah disetujui sebelumnya.");
            return;
        }
        
        $nip_katim = !empty($katim->nip) ? $katim->nip : '-';

        DB::table('spos')->where('id', $id)->update([
            'status_persetujuan' => 'approved',
            'disetujui_oleh_nip' => $nip_katim,
            'tanggal_disetujui' => now(),
            'updated_at' => now(),
        ]);

        $this->kirimPesan($chat_id, "✅ *BERHASIL!* SPO ({$spo->no_surat}) telah DISETUJUI. Dokumen siap dicetak.");

        if ($spo->created_by) {
            $kru = DB::table('users')->where('id', $spo->created_by)->first();
            if ($kru && !empty($kru->telegram_chat_id)) {
                $pesanKru = "🎉 *SPO ANDA TELAH DISETUJUI!*\n\n" .
                            "Nomor Surat: {$spo->no_surat}\n" .
                            "Status: *Disetujui oleh {$katim->name}*\n\n" .
                            "Silakan buka website Sistem Operasional TVRI untuk mengunduh dokumen SPO dan QR Code.";
                
                $this->kirimPesan($kru->telegram_chat_id, $pesanKru);
            }
        }
    }

    // ============================================
    // FLOW 1: TOLAK SPO DENGAN MENANYAKAN ALASAN
    // ============================================

    private function mintaAlasanRejectSPO($id, $chat_id)
    {
        $spo = DB::table('spos')
            ->leftJoin('users as pembuat', 'spos.created_by', '=', 'pembuat.id')
            ->select('spos.*', 'pembuat.name as nama_pembuat')
            ->where('spos.id', $id)
            ->first();

        if (!$spo) {
            $this->kirimPesan($chat_id, "❌ *ERROR:* Dokumen SPO tidak ditemukan.");
            return;
        }

        if ($spo->status_persetujuan !== 'pending') {
            $statusLabel = $spo->status_persetujuan === 'approved' ? 'Disetujui' : 'Ditolak';
            $this->kirimPesan($chat_id, "ℹ️ SPO (*{$spo->no_surat}*) sudah berstatus *{$statusLabel}* sebelumnya.");
            return;
        }

        $katim = DB::table('users')->where('telegram_chat_id', $chat_id)->first();
        if (!$katim || !in_array($katim->role, ['katim', 'admin'])) {
            $this->kirimPesan($chat_id, "❌ *AKSES DITOLAK.* Akun Telegram Anda tidak terdaftar sebagai Katim atau Administrator.");
            return;
        }

        // VALIDASI KETAT: Hanya Katim pemilik SPO atau Admin
        if ($katim->role !== 'admin' && $spo->katim_id != $katim->id) {
            $this->kirimPesan($chat_id, "❌ *AKSES DITOLAK.* Anda tidak berwenang menolak dokumen SPO milik tim Katim lain.");
            return;
        }

        // Simpan sesi pending ke cache
        Cache::put('telegram_pending_' . $chat_id, [
            'action' => 'reject_spo',
            'id' => $id,
            'katim_id' => $katim->id,
            'katim_name' => $katim->name,
            'katim_nip' => !empty($katim->nip) ? $katim->nip : '-',
            'no_surat' => $spo->no_surat,
            'created_by' => $spo->created_by,
        ], now()->addMinutes(30));

        $pembuatNama = $spo->nama_pembuat ?? 'Kru TVRI';

        $pesan = "❓ *ALASAN PENOLAKAN SPO*\n\n" .
                 "📄 *No. Surat*: {$spo->no_surat}\n" .
                 "👤 *Diajukan Oleh*: {$pembuatNama}\n\n" .
                 "Silakan ketik atau balas pesan ini dengan mencantumkan *alasan mengapa SPO ini ditolak* agar kru dapat memperbaikinya.\n\n" .
                 "_(Ketik */batal* jika tidak jadi menolak)_";

        $forceReply = [
            'force_reply' => true,
            'input_field_placeholder' => 'Ketik alasan penolakan di sini...'
        ];

        $this->kirimPesan($chat_id, $pesan, $forceReply);
    }

    private function selesaikanRejectSPO(array $pending, string $alasan, $chat_id)
    {
        $id = $pending['id'];
        $spo = DB::table('spos')->where('id', $id)->first();
        if (!$spo) {
            $this->kirimPesan($chat_id, "❌ *ERROR:* Dokumen SPO sudah tidak ditemukan.");
            return;
        }

        if ($spo->status_persetujuan !== 'pending') {
            $statusLabel = $spo->status_persetujuan === 'approved' ? 'Disetujui' : 'Ditolak';
            $this->kirimPesan($chat_id, "ℹ️ Dokumen SPO ({$spo->no_surat}) sudah berstatus *{$statusLabel}* sebelumnya.");
            return;
        }

        DB::table('spos')->where('id', $id)->update([
            'status_persetujuan' => 'rejected',
            'alasan_penolakan' => $alasan,
            'disetujui_oleh_nip' => $pending['katim_nip'] ?? '-',
            'updated_at' => now(),
        ]);

        $alasanClean = str_replace(['_', '*', '`', '['], ['\\_', '\\*', '\\`', '\\['], $alasan);

        // Notifikasi konfirmasi ke Katim
        $this->kirimPesan($chat_id, "❌ *SPO BERHASIL DITOLAK*\n\n" .
                                     "📄 *No. Surat*: {$spo->no_surat}\n" .
                                     "📝 *Alasan*: _{$alasanClean}_\n\n" .
                                     "Status SPO telah diperbarui dan alasan telah dikirimkan ke kru.");

        // Notifikasi ke Kru
        if ($spo->created_by) {
            $kru = DB::table('users')->where('id', $spo->created_by)->first();
            if ($kru && !empty($kru->telegram_chat_id)) {
                $katimNama = $pending['katim_name'];
                $pesanKru = "❌ *SPO ANDA DITOLAK*\n\n" .
                            "📄 *Nomor Surat*: {$spo->no_surat}\n" .
                            "👤 *Ditolak Oleh*: {$katimNama} (Katim)\n" .
                            "📝 *Alasan Penolakan*:\n_{$alasanClean}_\n\n" .
                            "Silakan periksa kembali dan perbaiki pengajuan Anda pada sistem.";
                
                $this->kirimPesan($kru->telegram_chat_id, $pesanKru);
            }
        }
    }

    // ============================================
    // FLOW 2: PERMINTAAN REVISI DISETUJUI
    // ============================================

    private function prosesAcceptRevision($id, $chat_id)
    {
        $revisi = DB::table('revisi_requests')->where('id', $id)->first();
        if (!$revisi || $revisi->status !== 'pending') {
            $this->kirimPesan($chat_id, "ℹ️ Permintaan revisi ini sudah diproses sebelumnya.");
            return;
        }

        $katim = DB::table('users')->where('telegram_chat_id', $chat_id)->first();
        $katim_id = $katim ? $katim->id : $revisi->diajukan_oleh;

        if (($revisi->jenis_dokumen ?? '') === 'monitoring') {
            $monitoring = DB::table('monitoring_harians')->where('id', $revisi->dokumen_id)->first();
            $spo = $monitoring ? DB::table('spos')->where('id', $monitoring->spo_id)->first() : null;

            // VALIDASI KETAT: Hanya Katim pemilik SPO (atau Admin) yang berhak menyetujui
            if ($katim && $katim->role !== 'admin') {
                if (!$spo || $spo->katim_id != $katim->id) {
                    $this->kirimPesan($chat_id, "❌ *AKSES DITOLAK.* Anda tidak berwenang menyetujui revisi monitoring milik tim Katim lain.");
                    return;
                }
            }

            DB::table('revisi_requests')->where('id', $id)->update([
                'status' => 'disetujui',
                'disetujui_oleh' => $katim_id,
                'tanggal_persetujuan' => now(),
                'updated_at' => now(),
            ]);

            DB::table('monitoring_harians')->where('id', $revisi->dokumen_id)->update([
                'status_revisi' => 'approved',
                'updated_at' => now(),
            ]);

            $this->kirimPesan($chat_id, "✅ *REVISI MONITORING DISETUJUI!* Pengarah Teknik (TD) terkait sekarang dapat mengubah laporan di website.");

            if ($revisi->diajukan_oleh) {
                $pd = DB::table('users')->where('id', $revisi->diajukan_oleh)->first();
                if ($pd && !empty($pd->telegram_chat_id)) {
                    $no_surat = $spo ? $spo->no_surat : '-';
                    $tglSiaran = $monitoring ? \Carbon\Carbon::parse($monitoring->tanggal)->format('d M Y') : '-';

                    $pesanPD = "🎉 *PERMINTAAN REVISI MONITORING DISETUJUI*\n\n" .
                               "📄 *No. SPO*: {$no_surat}\n" .
                               "📅 *Tanggal Siaran*: {$tglSiaran}\n" .
                               "Status: *Disetujui oleh Katim*\n\n" .
                               "Silakan buka menu Monitoring Siaran di website untuk mulai mengedit dan memperbaiki data laporan.";

                    $this->kirimPesan($pd->telegram_chat_id, $pesanPD);
                }
            }
            return;
        }

        // Dokumen SPO
        $spo = DB::table('spos')->where('id', $revisi->dokumen_id)->first();
        if ($katim && $katim->role !== 'admin') {
            if (!$spo || $spo->katim_id != $katim->id) {
                $this->kirimPesan($chat_id, "❌ *AKSES DITOLAK.* Anda tidak berwenang menyetujui revisi SPO milik tim Katim lain.");
                return;
            }
        }

        DB::table('revisi_requests')->where('id', $id)->update([
            'status' => 'disetujui',
            'disetujui_oleh' => $katim_id,
            'tanggal_persetujuan' => now(),
            'updated_at' => now(),
        ]);

        $this->kirimPesan($chat_id, "✅ *REVISI DISETUJUI!* Kru terkait sekarang dapat mengubah dokumen tersebut di website.");

        if ($revisi->diajukan_oleh) {
            $kru = DB::table('users')->where('id', $revisi->diajukan_oleh)->first();
            if ($kru && !empty($kru->telegram_chat_id)) {
                $no_surat = $spo ? $spo->no_surat : 'Tidak diketahui';
                $pesanKru = "🎉 *PERMINTAAN REVISI DISETUJUI*\n\n" .
                            "Nomor Surat: {$no_surat}\n" .
                            "Status: *Disetujui oleh Katim*\n\n" .
                            "Silakan buka halaman Arsip SPO di website untuk mulai mengedit dan memperbaiki data SPO tersebut.";
                
                $this->kirimPesan($kru->telegram_chat_id, $pesanKru);
            }
        }
    }

    // ============================================
    // FLOW 3: TOLAK REVISI DENGAN MENANYAKAN ALASAN
    // ============================================

    private function mintaAlasanDeclineRevision($id, $chat_id)
    {
        $revisi = DB::table('revisi_requests')->where('id', $id)->first();
        if (!$revisi || $revisi->status !== 'pending') {
            $this->kirimPesan($chat_id, "ℹ️ Permintaan revisi ini sudah tidak berlaku atau telah diproses sebelumnya.");
            return;
        }

        $katim = DB::table('users')->where('telegram_chat_id', $chat_id)->first();
        if (!$katim || !in_array($katim->role, ['katim', 'admin'])) {
            $this->kirimPesan($chat_id, "❌ *AKSES DITOLAK.* Akun Telegram Anda tidak terdaftar sebagai Katim atau Administrator.");
            return;
        }

        $no_surat = '-';
        if (($revisi->jenis_dokumen ?? '') === 'monitoring') {
            $monitoring = DB::table('monitoring_harians')->where('id', $revisi->dokumen_id)->first();
            $spo = $monitoring ? DB::table('spos')->where('id', $monitoring->spo_id)->first() : null;
            if ($katim->role !== 'admin' && (!$spo || $spo->katim_id != $katim->id)) {
                $this->kirimPesan($chat_id, "❌ *AKSES DITOLAK.* Anda tidak berwenang menolak revisi monitoring milik tim Katim lain.");
                return;
            }
            $no_surat = $spo ? $spo->no_surat : '-';
        } else {
            $spo = DB::table('spos')->where('id', $revisi->dokumen_id)->first();
            if ($katim->role !== 'admin' && (!$spo || $spo->katim_id != $katim->id)) {
                $this->kirimPesan($chat_id, "❌ *AKSES DITOLAK.* Anda tidak berwenang menolak revisi SPO milik tim Katim lain.");
                return;
            }
            $no_surat = $spo ? $spo->no_surat : ('SPO #' . $revisi->dokumen_id);
        }

        Cache::put('telegram_pending_' . $chat_id, [
            'action' => 'decline_rev',
            'id' => $id,
            'katim_id' => $katim->id,
            'katim_name' => $katim->name,
            'jenis_dokumen' => $revisi->jenis_dokumen,
            'dokumen_id' => $revisi->dokumen_id,
            'diajukan_oleh' => $revisi->diajukan_oleh,
            'no_surat' => $no_surat,
        ], now()->addMinutes(30));

        $jenisLabel = strtoupper($revisi->jenis_dokumen ?? 'DOKUMEN');
        $pesan = "❓ *ALASAN PENOLAKAN REVISI {$jenisLabel}*\n\n" .
                 "📄 *No. Dokumen*: {$no_surat}\n\n" .
                 "Silakan ketik atau balas pesan ini dengan menyebutkan *alasan mengapa permintaan revisi ini ditolak*.\n\n" .
                 "_(Ketik */batal* jika tidak jadi menolak)_";

        $forceReply = [
            'force_reply' => true,
            'input_field_placeholder' => 'Ketik alasan penolakan revisi di sini...'
        ];

        $this->kirimPesan($chat_id, $pesan, $forceReply);
    }

    private function selesaikanDeclineRevision(array $pending, string $alasan, $chat_id)
    {
        $id = $pending['id'];
        $revisi = DB::table('revisi_requests')->where('id', $id)->first();
        if (!$revisi || $revisi->status !== 'pending') {
            $this->kirimPesan($chat_id, "ℹ️ Permintaan revisi ini sudah diproses sebelumnya.");
            return;
        }

        $alasanClean = str_replace(['_', '*', '`', '['], ['\\_', '\\*', '\\`', '\\['], $alasan);

        if (($pending['jenis_dokumen'] ?? '') === 'monitoring') {
            DB::table('revisi_requests')->where('id', $id)->update([
                'status' => 'ditolak',
                'alasan_penolakan' => $alasan,
                'disetujui_oleh' => $pending['katim_id'],
                'tanggal_persetujuan' => now(),
                'updated_at' => now(),
            ]);

            DB::table('monitoring_harians')->where('id', $pending['dokumen_id'])->update([
                'status_revisi' => 'rejected',
                'updated_at' => now(),
            ]);

            $this->kirimPesan($chat_id, "❌ *REVISI MONITORING DITOLAK.*\n\n" .
                                         "📄 *No. SPO*: {$pending['no_surat']}\n" .
                                         "📝 *Alasan*: _{$alasanClean}_\n\n" .
                                         "Pemberitahuan telah diteruskan ke Pengarah Teknik (TD).");

            if ($revisi->diajukan_oleh) {
                $pd = DB::table('users')->where('id', $revisi->diajukan_oleh)->first();
                if ($pd && !empty($pd->telegram_chat_id)) {
                    $pesanPD = "❌ *PERMINTAAN REVISI MONITORING DITOLAK*\n\n" .
                               "📄 *No. SPO*: {$pending['no_surat']}\n" .
                               "👤 *Ditolak Oleh*: {$pending['katim_name']} (Katim)\n" .
                               "📝 *Alasan Penolakan*:\n_{$alasanClean}_\n\n" .
                               "Permintaan Anda untuk merevisi laporan monitoring tidak disetujui. Laporan tetap pada versi aslinya.";

                    $this->kirimPesan($pd->telegram_chat_id, $pesanPD);
                }
            }
            return;
        }

        // Dokumen SPO
        DB::table('revisi_requests')->where('id', $id)->update([
            'status' => 'ditolak',
            'alasan_penolakan' => $alasan,
            'disetujui_oleh' => $pending['katim_id'],
            'tanggal_persetujuan' => now(),
            'updated_at' => now(),
        ]);

        $this->kirimPesan($chat_id, "❌ *REVISI SPO DITOLAK.*\n\n" .
                                     "📄 *No. Surat*: {$pending['no_surat']}\n" .
                                     "📝 *Alasan*: _{$alasanClean}_\n\n" .
                                     "Pemberitahuan telah diteruskan ke kru.");

        if ($revisi->diajukan_oleh) {
            $kru = DB::table('users')->where('id', $revisi->diajukan_oleh)->first();
            if ($kru && !empty($kru->telegram_chat_id)) {
                $pesanKru = "❌ *PERMINTAAN REVISI DITOLAK*\n\n" .
                            "📄 *Nomor Surat*: {$pending['no_surat']}\n" .
                            "👤 *Ditolak Oleh*: {$pending['katim_name']} (Katim)\n" .
                            "📝 *Alasan Penolakan*:\n_{$alasanClean}_\n\n" .
                            "Permintaan Anda untuk merevisi SPO tersebut tidak disetujui. SPO tetap pada versi aslinya.";
                
                $this->kirimPesan($kru->telegram_chat_id, $pesanKru);
            }
        }
    }

    private function kirimPesan($chat_id, $text, $reply_markup = null)
    {
        $token = env('TELEGRAM_BOT_TOKEN');
        $payload = [
            'chat_id' => $chat_id,
            'text' => $text,
            'parse_mode' => 'Markdown',
        ];

        if ($reply_markup) {
            $payload['reply_markup'] = is_array($reply_markup) ? json_encode($reply_markup) : $reply_markup;
        }

        try {
            $response = Http::post("https://api.telegram.org/bot{$token}/sendMessage", $payload);
            if (!$response->successful()) {
                Log::error("TELEGRAM SEND MESSAGE FAILED: " . $response->body());
            }
        } catch (\Exception $e) {
            Log::error("TELEGRAM SEND EXCEPTION: " . $e->getMessage());
        }
    }
}
