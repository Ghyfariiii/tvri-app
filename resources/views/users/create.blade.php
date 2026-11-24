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
            </div>
            <h3 class="fw-bold text-dark mb-1">Tambah User Baru</h3>
            <p class="text-muted small mb-0">Buat akun pengguna baru dengan hak akses dan kredensial terstandarisasi SIMS TVRI.</p>
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

            {{-- BLOK NOTIFIKASI ERROR SISTEM --}}
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4 rounded-4 p-3 d-flex align-items-center gap-3" role="alert">
                    <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center p-2" style="width: 32px; height: 32px; flex-shrink: 0;">
                        <i class="fas fa-times"></i>
                    </div>
                    <div class="flex-grow-1 text-dark small">
                        <strong>Perhatian:</strong> {{ session('error') }}
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- MAIN FORM CARD -->
            <div class="card card-modern rounded-4 border-0 shadow-sm overflow-hidden">
                
                <div class="card-header bg-white border-bottom p-4">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-3 d-flex align-items-center justify-content-center" 
                             style="width: 44px; height: 44px; background: linear-gradient(135deg, var(--primary-navy) 0%, var(--primary-navy-light) 100%); color: #fff;">
                            <i class="fas fa-user-plus fa-lg"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold text-dark mb-0">Form Registrasi Akun Pegawai</h5>
                            <small class="text-muted">Isi identitas lengkap dan tetapkan role penugasan yang sesuai</small>
                        </div>
                    </div>
                </div>
                
                <div class="card-body p-4 p-md-5">
                    <form action="{{ route('users.store') }}" method="POST" id="userCreateForm">
                        @csrf
                        
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
                                               placeholder="Contoh: Ghifari Irzai, S.Kom" value="{{ old('name') }}" required autofocus>
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
                                               placeholder="Contoh: 199503152020121001" value="{{ old('nip') }}" required>
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
                                               placeholder="nama@tvri.go.id" value="{{ old('email') }}" required>
                                    </div>
                                    <div class="form-text text-muted small" style="font-size: 11px;">Digunakan untuk masuk ke sistem & notifikasi dinas.</div>
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
                                        <select name="role" id="roleSelect" class="form-select form-select-modern @error('role') is-invalid @enderror" required>
                                            <option value="" disabled {{ old('role') ? '' : 'selected' }}>-- Pilih Hak Akses --</option>
                                            <option value="kru" {{ old('role') == 'kru' ? 'selected' : '' }}>Kru — Staf Penyiaran & Tim Lapangan</option>
                                            <option value="katim" {{ old('role') == 'katim' ? 'selected' : '' }}>Katim — Ketua Tim / Persetujuan Dokumen</option>
                                            <option value="td" {{ old('role') == 'td' ? 'selected' : '' }}>TD — Technical Director / Pengarah Teknik</option>
                                            <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin — Administrator & IT Support</option>
                                        </select>
                                    </div>
                                    <div class="form-text text-muted small" style="font-size: 11px;">Hak akses menentukan fitur dan otorisasi dokumen di aplikasi.</div>
                                    @error('role')
                                        <div class="text-danger small mt-1"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- SECTION 3: KATA SANDI -->
                        <div class="mb-4">
                            <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                                <span class="badge bg-primary bg-opacity-10 text-primary rounded-circle p-2" style="width: 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center; font-size: 11px;">3</span>
                                <h6 class="fw-bold text-dark m-0">Keamanan & Kata Sandi Akun</h6>
                            </div>

                            <div class="p-3 rounded-4 mb-3" style="background: #f8fafc; border: 1px dashed #cbd5e1;">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <i class="fas fa-shield-alt text-primary"></i>
                                    <span class="fw-bold text-dark small">Ketentuan Keamanan Sandi:</span>
                                </div>
                                <div class="small text-muted" style="font-size: 11.5px;">
                                    Password wajib minimal <strong>8 karakter</strong> dan harus mengandung kombinasi <strong>huruf besar (A-Z)</strong>, <strong>huruf kecil (a-z)</strong>, dan <strong>angka (0-9)</strong>.
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label-modern">
                                        <i class="fas fa-key text-primary small"></i> Kata Sandi (Password) <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group input-group-modern has-validation">
                                        <span class="input-group-text"><i class="fas fa-lock text-muted"></i></span>
                                        <input type="password" name="password" id="password" 
                                               class="form-control form-control-modern @error('password') is-invalid @enderror" 
                                               placeholder="Minimal 8 karakter" required>
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
                                        <i class="fas fa-check-double text-primary small"></i> Konfirmasi Kata Sandi <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group input-group-modern">
                                        <span class="input-group-text"><i class="fas fa-lock text-muted"></i></span>
                                        <input type="password" name="password_confirmation" id="password_confirmation" 
                                               class="form-control form-control-modern" 
                                               placeholder="Ulangi kata sandi" required>
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
                                <i class="fas fa-save me-2"></i>Simpan User Baru
                            </button>
                        </div>

                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
