@extends('layouts.backend')

@section('content')
<div class="container-fluid">
    {{-- HEADER HALAMAN --}}
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge badge-soft-primary px-3 py-1 rounded-pill small fw-semibold">
                    <i class="fas fa-user-cog me-1"></i> Pengaturan Akun
                </span>
            </div>
            <h1 class="h3 mb-1 mt-2 text-gray-800 fw-bold">Profil Pengguna</h1>
            <p class="text-muted small mb-0">Kelola identitas personal, keamanan kata sandi, dan integrasi notifikasi Telegram.</p>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-9">
            
            {{-- KARTU FORMULIR PROFIL --}}
            <div class="card card-modern overflow-hidden mb-4">
                <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <div class="rounded-3 p-2 me-3" style="background: rgba(26,59,112,0.08); color: var(--primary-navy);">
                            <i class="fas fa-user-edit"></i>
                        </div>
                        <div>
                            <h6 class="m-0 fw-bold text-dark">Formulir Data Diri & Keamanan</h6>
                            <small class="text-muted">Perbarui data profil dan kata sandi login Anda</small>
                        </div>
                    </div>
                    <span class="badge badge-soft-{{ Auth::user()->role === 'admin' ? 'danger' : (Auth::user()->role === 'katim' ? 'primary' : 'success') }} rounded-pill px-3 py-2 fw-semibold text-uppercase">
                        {{ Auth::user()->role }}
                    </span>
                </div>
                <div class="card-body p-4">

                    @if (session('success'))
                        <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4 d-flex align-items-center p-3" style="background: rgba(16,185,129,0.1); color: #047857;">
                            <i class="fas fa-check-circle fa-lg me-3"></i>
                            <div>
                                <h6 class="fw-bold mb-0">Berhasil</h6>
                                <span class="small">{{ session('success') }}</span>
                            </div>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('profile.update') }}">
                        @csrf
                        @method('PUT') 
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-secondary">Nama Lengkap</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted border-end-0"><i class="fas fa-user"></i></span>
                                <input type="text" name="name" class="form-control border-start-0 @error('name') is-invalid @enderror" 
                                       value="{{ old('name', $user->name) }}" required>
                            </div>
                            @error('name')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold small text-secondary">NIP / ID Pegawai</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted border-end-0"><i class="fas fa-id-card"></i></span>
                                    <input type="text" name="nip" class="form-control border-start-0 @error('nip') is-invalid @enderror" 
                                           value="{{ old('nip', $user->nip) }}" required>
                                </div>
                                @error('nip')
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold small text-secondary">Alamat Email</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted border-end-0"><i class="fas fa-envelope"></i></span>
                                    <input type="email" name="email" class="form-control border-start-0 @error('email') is-invalid @enderror" 
                                           value="{{ old('email', $user->email) }}" required>
                                </div>
                                @error('email')
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <hr class="my-4 text-muted opacity-25">
                        
                        <div class="alert alert-info border-0 rounded-4 p-3 small mb-4" style="background: rgba(6,182,212,0.08); color: #0e7490;">
                            <i class="fas fa-shield-alt me-1"></i> Kosongkan bagian kata sandi di bawah jika Anda tidak bermaksud mengganti password. Jika diubah, minimal 8 karakter (kombinasi huruf besar, kecil & angka).
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold small text-secondary">Password Baru</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted border-end-0"><i class="fas fa-lock"></i></span>
                                    <input type="password" id="password" name="password" class="form-control border-start-0 border-end-0 password-input @error('password') is-invalid @enderror" 
                                           placeholder="Minimal 8 karakter...">
                                    <button type="button" class="btn btn-outline-secondary toggle-password bg-white border-start-0" data-target="password" onclick="togglePasswordVisibility(this)" tabindex="-1" title="Lihat/Sembunyikan Password">
                                        <i class="fas fa-eye text-muted"></i>
                                    </button>
                                </div>
                                @error('password')
                                    <span class="text-danger small mt-1 d-block">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold small text-secondary">Konfirmasi Password Baru</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted border-end-0"><i class="fas fa-check-double"></i></span>
                                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-control border-start-0 border-end-0 password-input" 
                                           placeholder="Ulangi password baru...">
                                    <button type="button" class="btn btn-outline-secondary toggle-password bg-white border-start-0" data-target="password_confirmation" onclick="togglePasswordVisibility(this)" tabindex="-1" title="Lihat/Sembunyikan Password">
                                        <i class="fas fa-eye text-muted"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('dashboard') }}" class="btn btn-light rounded-pill px-4 border">Batal</a>
                            <button type="submit" class="btn btn-gradient-primary rounded-pill px-4 shadow-sm">
                                <i class="fas fa-save me-2"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- KARTU INTEGRASI TELEGRAM --}}
            <div class="card card-modern overflow-hidden">
                <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <div class="rounded-3 p-2 me-3" style="background: rgba(6,182,212,0.1); color: #0891b2;">
                            <i class="fab fa-telegram-plane fa-lg"></i>
                        </div>
                        <div>
                            <h6 class="m-0 fw-bold text-dark">Integrasi Notifikasi Telegram</h6>
                            <small class="text-muted">Terima pemberitahuan dan respon persetujuan instan langsung di Telegram</small>
                        </div>
                    </div>
                    @if($user->telegram_chat_id)
                        <span class="badge badge-soft-success rounded-pill px-3 py-2 fw-semibold">
                            <i class="fas fa-link me-1"></i> Terhubung
                        </span>
                    @else
                        <span class="badge badge-soft-warning rounded-pill px-3 py-2 fw-semibold">
                            <i class="fas fa-unlink me-1"></i> Belum Terhubung
                        </span>
                    @endif
                </div>
                <div class="card-body p-4">
                    @if($user->telegram_chat_id)
                        <!-- Jika Sudah Terhubung -->
                        <div class="alert alert-success border-0 rounded-4 shadow-sm mb-3 d-flex align-items-center justify-content-between p-3" style="background: rgba(16,185,129,0.1); color: #047857;">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-success text-white p-2 me-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; min-width: 44px;">
                                    <i class="fas fa-check fa-lg"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1">Status: Terhubung Aktif</h6>
                                    <p class="mb-0 small">Chat ID: <strong class="font-monospace text-dark">{{ $user->telegram_chat_id }}</strong> &bull; Akun Anda siap menerima dan merespons notifikasi tugas siaran dari Bot Telegram.</p>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 p-3 bg-light rounded-4 border">
                            <div>
                                <span class="d-block fw-bold text-dark small"><i class="fas fa-exclamation-circle text-warning me-1"></i> Putuskan Sambungan Telegram</span>
                                <small class="text-muted" style="font-size: 11.5px;">Jika Anda berganti akun Telegram, putuskan sambungan saat ini terlebih dahulu untuk menghubungkan akun baru.</small>
                            </div>
                            <form method="POST" action="{{ route('profile.disconnect_telegram') }}" onsubmit="return confirm('Apakah Anda yakin ingin memutuskan sambungan akun Telegram ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger rounded-pill px-3 py-2 btn-sm fw-semibold shadow-sm text-nowrap">
                                    <i class="fas fa-unlink me-1"></i> Putuskan Sambungan
                                </button>
                            </form>
                        </div>
                    @else
                        <!-- Jika Belum Terhubung -->
                        @if($user->telegram_bind_token)
                            <div class="card border-warning border-0 rounded-4 p-4 mb-3" style="background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%); border-left: 5px solid #f59e0b !important;">
                                <h6 class="fw-bold text-dark mb-2"><i class="fas fa-qrcode me-2 text-warning"></i> Menunggu Verifikasi Bot Telegram</h6>
                                <p class="mb-3 small text-muted">Buka aplikasi Telegram dan cari bot <strong>SENADA TVRI</strong>, lalu kirimkan pesan dengan kode di bawah ini:</p>
                                <div class="bg-white p-3 rounded-3 border text-center fw-bold fs-5 mb-2 font-monospace text-primary user-select-all shadow-sm">
                                    /bind {{ $user->telegram_bind_token }}
                                </div>
                                <p class="mb-0 small text-muted"><i class="fas fa-info-circle me-1"></i> Klik atau salin teks di atas. Token ini otomatis hangus setelah akun berhasil diverifikasi.</p>
                            </div>
                        @else
                            <p class="text-muted small mb-3">Hubungkan akun Anda dengan Telegram untuk menerima peringatan jadwal tugas siaran, permohonan revisi, dan menyetujui pengajuan SPO kapan saja langsung dari ponsel Anda.</p>
                        @endif
                        
                        <form method="POST" action="{{ route('profile.generate_token') }}">
                            @csrf
                            <button type="submit" class="btn btn-soft-info rounded-pill px-4 fw-semibold shadow-sm">
                                <i class="fas fa-sync-alt me-2"></i> {{ $user->telegram_bind_token ? 'Buat Ulang Token' : 'Generate Token Telegram Sekarang' }}
                            </button>
                        </form>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
