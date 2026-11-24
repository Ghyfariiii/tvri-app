@extends('layouts.backend')

@section('content')
<div class="container-fluid pb-5">

    <!-- PAGE HEADER -->
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-3">
        <div>
            <div class="d-inline-flex align-items-center gap-2 mb-1">
                <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-1 rounded-pill small fw-bold">
                    <i class="fas fa-users-cog me-1"></i> MANAJEMEN PENGGUNA
                </span>
                <span class="badge role-{{ $user->role }} rounded-pill text-uppercase px-3 py-1" style="font-size: 11px;">
                    Role: {{ $user->role }}
                </span>
            </div>
            <h3 class="fw-bold text-dark mb-1">Edit Pengguna: {{ $user->name }}</h3>
            <p class="text-muted small mb-0">Perbarui profil identitas, hak akses tugas, atau setel ulang kata sandi pengguna.</p>
        </div>
        <div>
            <a href="{{ route('users.index') }}" class="btn btn-outline-secondary rounded-pill px-4 shadow-sm btn-sm py-2">
                <i class="fas fa-arrow-left me-2"></i>Kembali ke Daftar User
            </a>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-9 col-xl-8">
            
            {{-- BLOK NOTIFIKASI ERROR VALIDASI --}}
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-4 border-0 rounded-4 p-3 d-flex align-items-start gap-3" role="alert">
                    <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center p-2 mt-1" style="width: 32px; height: 32px; flex-shrink: 0;">
                        <i class="fas fa-exclamation-triangle small"></i>
                    </div>
                    <div class="flex-grow-1">
                        <strong class="d-block text-danger mb-1">Pemeriksaan Validasi Gagal:</strong>
                        <ul class="mb-0 ps-3 small text-dark">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- MAIN FORM CARD -->
            <div class="card card-modern rounded-4 border-0 shadow-sm overflow-hidden">
                
                <div class="card-header bg-white border-bottom p-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-3">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=1a3b70&color=fff&size=100" 
                                 class="rounded-circle shadow-sm" width="46" height="46" alt="{{ $user->name }}">
                            <div>
                                <h5 class="fw-bold text-dark mb-0">{{ $user->name }}</h5>
                                <small class="text-muted">NIP: {{ $user->nip ?? '-' }} &bull; {{ $user->email }}</small>
                            </div>
                        </div>
                        <span class="badge role-{{ $user->role }} rounded-pill px-3 py-2 fw-bold text-uppercase d-none d-sm-inline-block">
                            {{ $user->role }}
                        </span>
                    </div>
                </div>
                
                <div class="card-body p-4 p-md-5">
                    <form action="{{ route('users.update', $user->id) }}" method="POST" id="userEditForm">
                        @csrf
                        @method('PUT')
                        
                        <!-- SECTION 1: PROFIL PEGAWAI -->
                        <div class="mb-4">
                            <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                                <span class="badge bg-primary bg-opacity-10 text-primary rounded-circle p-2" style="width: 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center; font-size: 11px;">1</span>
                                <h6 class="fw-bold text-dark m-0">Identitas & Informasi Pegawai</h6>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label-modern">
                                        <i class="fas fa-user text-primary small"></i> Nama Lengkap <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group input-group-modern">
                                        <span class="input-group-text"><i class="fas fa-user text-muted"></i></span>
                                        <input type="text" name="name" class="form-control form-control-modern @error('name') is-invalid @enderror" 
                                               value="{{ old('name', $user->name) }}" required>
                                    </div>
                                    @error('name')
                                        <div class="text-danger small mt-1"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label-modern">
                                        <i class="fas fa-id-card text-primary small"></i> NIP / Nomor Identitas Pegawai <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group input-group-modern">
                                        <span class="input-group-text"><i class="fas fa-id-badge text-muted"></i></span>
                                        <input type="text" name="nip" class="form-control form-control-modern @error('nip') is-invalid @enderror" 
                                               value="{{ old('nip', $user->nip) }}" required>
                                    </div>
                                    @error('nip')
                                        <div class="text-danger small mt-1"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- SECTION 2: AKSES & ROLE -->
                        <div class="mb-4">
                            <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                                <span class="badge bg-primary bg-opacity-10 text-primary rounded-circle p-2" style="width: 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center; font-size: 11px;">2</span>
                                <h6 class="fw-bold text-dark m-0">Alamat Email & Hak Akses (Role)</h6>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label-modern">
                                        <i class="fas fa-envelope text-primary small"></i> Alamat Email Resmi <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group input-group-modern">
                                        <span class="input-group-text"><i class="fas fa-at text-muted"></i></span>
                                        <input type="email" name="email" class="form-control form-control-modern @error('email') is-invalid @enderror" 
                                               value="{{ old('email', $user->email) }}" required>
                                    </div>
                                    <div class="form-text text-muted small" style="font-size: 11px;">Alamat email login akun.</div>
                                    @error('email')
                                        <div class="text-danger small mt-1"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label-modern">
                                        <i class="fas fa-user-shield text-primary small"></i> Hak Akses (Role) <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group input-group-modern">
                                        <span class="input-group-text"><i class="fas fa-user-tag text-muted"></i></span>
                                        <select name="role" class="form-select form-select-modern @error('role') is-invalid @enderror" required>
                                            <option value="kru" {{ old('role', $user->role) == 'kru' ? 'selected' : '' }}>Kru — Staf Penyiaran & Tim Lapangan</option>
                                            <option value="katim" {{ old('role', $user->role) == 'katim' ? 'selected' : '' }}>Katim — Ketua Tim / Persetujuan Dokumen</option>
                                            <option value="td" {{ old('role', $user->role) == 'td' || old('role', $user->role) == 'pd' ? 'selected' : '' }}>TD — Technical Director / Pengarah Teknik</option>
                                            <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Admin — Administrator & IT Support</option>
                                        </select>
                                    </div>
                                    @error('role')
                                        <div class="text-danger small mt-1"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-12">
                                    <div class="p-3 rounded-4 d-flex align-items-center justify-content-between" style="background: rgba(26,59,112,0.04); border: 1px solid rgba(26,59,112,0.08);">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="rounded-circle p-2 bg-white text-info shadow-sm d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                                                <i class="fab fa-telegram-plane fa-lg"></i>
                                            </div>
                                            <div>
                                                <span class="d-block fw-bold text-dark small">Integrasi Bot Telegram:</span>
                                                <small class="text-muted" style="font-size: 11.5px;">Status penautan akun Telegram dinas pengguna dengan sistem.</small>
                                            </div>
                                        </div>
                                        <div>
                                            @if($user->telegram_chat_id)
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 rounded-pill px-3 py-2 font-monospace" style="font-size: 0.8rem;">
                                                        <i class="fas fa-check-circle me-1 text-success"></i> ID: {{ $user->telegram_chat_id }} (Terhubung)
                                                    </span>
                                                    <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3 fw-semibold shadow-sm" onclick="if(confirm('Putuskan sambungan Telegram untuk pengguna {{ $user->name }}?')) { document.getElementById('disconnect-telegram-form').submit(); }">
                                                        <i class="fas fa-unlink me-1"></i> Putuskan
                                                    </button>
                                                </div>
                                            @else
                                                <span class="badge bg-secondary bg-opacity-10 text-muted rounded-pill px-3 py-2" style="font-size: 0.78rem;">
                                                    <i class="fas fa-times-circle me-1 text-danger opacity-50"></i> Belum Terhubung
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- SECTION 3: KATA SANDI (OPSIONAL) -->
                        <div class="mb-4">
                            <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                                <span class="badge bg-primary bg-opacity-10 text-primary rounded-circle p-2" style="width: 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center; font-size: 11px;">3</span>
                                <h6 class="fw-bold text-dark m-0">Ganti Kata Sandi (Opsional)</h6>
                            </div>

                            <div class="p-3 rounded-4 mb-3" style="background: #fffbeb; border: 1px dashed #fcd34d;">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <i class="fas fa-key text-warning"></i>
                                    <span class="fw-bold text-dark small">Petunjuk Perubahan Password:</span>
                                </div>
                                <div class="small text-muted" style="font-size: 11.5px;">
                                    <strong>Kosongkan</strong> kolom password jika tidak ingin mengubah password saat ini. Jika ingin mengubahnya, password baru minimal 8 karakter (kombinasi huruf besar, kecil, dan angka).
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label-modern">
                                        <i class="fas fa-lock text-primary small"></i> Password Baru
                                    </label>
                                    <div class="input-group input-group-modern has-validation">
                                        <span class="input-group-text"><i class="fas fa-lock text-muted"></i></span>
                                        <input type="password" name="password" id="password" 
                                               class="form-control form-control-modern @error('password') is-invalid @enderror" 
                                               placeholder="Kosongkan jika tidak diubah">
                                        <button class="btn btn-outline-secondary border bg-white toggle-password" type="button" data-target="password" title="Lihat/Sembunyikan Password" style="border-radius: 0 10px 10px 0; border-color: #e2e8f0 !important;">
                                            <i class="fas fa-eye text-muted"></i>
                                        </button>
                                    </div>
                                    @error('password')
                                        <div class="text-danger small mt-1"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label-modern">
                                        <i class="fas fa-check-double text-primary small"></i> Ulangi Password Baru
                                    </label>
                                    <div class="input-group input-group-modern">
                                        <span class="input-group-text"><i class="fas fa-lock text-muted"></i></span>
                                        <input type="password" name="password_confirmation" id="password_confirmation" 
                                               class="form-control form-control-modern" 
                                               placeholder="Ketik ulang password baru">
                                        <button class="btn btn-outline-secondary border bg-white toggle-password" type="button" data-target="password_confirmation" title="Lihat/Sembunyikan Password" style="border-radius: 0 10px 10px 0; border-color: #e2e8f0 !important;">
                                            <i class="fas fa-eye text-muted"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- BUTTON ACTIONS -->
                        <div class="pt-4 border-top d-flex flex-column flex-sm-row align-items-center justify-content-between gap-3">
                            <a href="{{ route('users.index') }}" class="btn btn-light text-secondary rounded-pill px-4 fw-semibold border">
                                <i class="fas fa-times me-2"></i>Batal
                            </a>
                            <button type="submit" class="btn btn-gradient-primary rounded-pill px-5 py-2 fw-bold shadow">
                                <i class="fas fa-save me-2"></i>Perbarui Data Pengguna
                            </button>
                        </div>

                    </form>

                    @if($user->telegram_chat_id)
                        <form id="disconnect-telegram-form" action="{{ route('users.disconnect_telegram', $user->id) }}" method="POST" style="display: none;">
                            @csrf
                            @method('DELETE')
                        </form>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
