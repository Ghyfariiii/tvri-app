@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center align-items-center">
        <div class="col-11 col-sm-9 col-md-7 col-lg-5 col-xl-4">
            
            <!-- LOGO & BRANDING -->
            <div class="text-center mb-4">
                <div class="d-inline-flex align-items-center justify-content-center mb-3 p-3 rounded-4 shadow-lg" 
                     style="background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.2);">
                    <img src="{{ asset('images/logo.png') }}" 
                         alt="Logo TVRI" 
                         style="height: 54px; object-fit: contain;" 
                         onerror="this.onerror=null; this.src='https://upload.wikimedia.org/wikipedia/commons/thumb/2/23/TVRI_2023.svg/1200px-TVRI_2023.svg.png';">
                </div>
                <h4 class="fw-bold text-white mb-1 letter-spacing-wide">SIMS TVRI</h4>
                <p class="text-white-50 small mb-0">Stasiun Penyiaran TVRI Gorontalo</p>
            </div>

            <!-- LOGIN CARD -->
            <div class="card border-0 rounded-4 shadow-2-strong overflow-hidden" 
                 style="background: #ffffff; box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25);">
                <div class="card-body p-4 p-sm-5">
                    
                    <div class="mb-4 text-center">
                        <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-1 rounded-pill small fw-semibold mb-2">
                            <i class="fas fa-lock me-1"></i> Portal Autentikasi
                        </span>
                        <h5 class="fw-bold text-dark mb-1">Masuk ke Sistem</h5>
                        <small class="text-muted">Gunakan akun resmi TVRI Gorontalo Anda</small>
                    </div>

                    @if(session('info'))
                        <div class="alert alert-info border-0 rounded-3 small mb-3 shadow-sm d-flex align-items-center">
                            <i class="fas fa-shield-alt me-2 fa-lg"></i>
                            <div>{{ session('info') }}</div>
                        </div>
                    @endif
                    @if(session('success'))
                        <div class="alert alert-success border-0 rounded-3 small mb-3 shadow-sm d-flex align-items-center">
                            <i class="fas fa-check-circle me-2 fa-lg"></i>
                            <div>{{ session('success') }}</div>
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger border-0 rounded-3 small mb-3 shadow-sm d-flex align-items-center">
                            <i class="fas fa-exclamation-circle me-2 fa-lg"></i>
                            <div>{{ session('error') }}</div>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}" id="loginForm">
                        @csrf

                        <!-- EMAIL INPUT -->
                        <div class="mb-3">
                            <label for="email" class="form-label fw-bold small text-secondary">Alamat Email</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted ps-3">
                                    <i class="fas fa-envelope"></i>
                                </span>
                                <input id="email" type="email" class="form-control border-start-0 py-2 @error('email') is-invalid @enderror" 
                                       name="email" value="{{ old('email') }}" required autocomplete="email" autofocus 
                                       placeholder="nama@tvri.go.id">
                            </div>
                            @error('email')
                                <span class="text-danger small mt-1 d-block fw-semibold">
                                    <i class="fas fa-exclamation-circle me-1"></i>{{ $message }}
                                </span>
                            @enderror
                        </div>

                        <!-- PASSWORD INPUT -->
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label for="password" class="form-label fw-bold small text-secondary mb-0">Password</label>
                            </div>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted ps-3">
                                    <i class="fas fa-lock"></i>
                                </span>
                                <input id="password" type="password" class="form-control border-start-0 border-end-0 py-2 @error('password') is-invalid @enderror" 
                                       name="password" required autocomplete="current-password" 
                                       placeholder="••••••••">
                                <button class="btn btn-outline-secondary bg-white border-start-0 py-2 toggle-password" type="button" data-target="password" onclick="togglePasswordVisibility(this)" style="border-color: #dee2e6;">
                                    <i class="fas fa-eye text-muted"></i>
                                </button>
                            </div>
                            @error('password')
                                <span class="text-danger small mt-1 d-block fw-semibold">
                                    <i class="fas fa-exclamation-circle me-1"></i>{{ $message }}
                                </span>
                            @enderror
                        </div>

                        <!-- REMEMBER ME -->
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                <label class="form-check-label small text-muted user-select-none" for="remember">
                                    Ingat sesi saya
                                </label>
                            </div>
                        </div>

                        <!-- SUBMIT BUTTON -->
                        <div class="d-grid">
                            <button type="submit" id="btnSubmit" class="btn text-white fw-bold py-2 rounded-pill shadow-sm" 
                                    style="background: linear-gradient(135deg, #1a3b70 0%, #25529c 100%);">
                                <span id="textNormal">
                                    MASUK KE SISTEM <i class="fas fa-arrow-right ms-2"></i>
                                </span>
                                <span id="textLoading" class="d-none">
                                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                    Memverifikasi Akun...
                                </span>
                            </button>
                        </div>

                    </form>
                </div>
            </div>
            
            <!-- FOOTER BRANDING -->
            <div class="text-center mt-4 text-white-50 small">
                &copy; {{ date('Y') }} TVRI Gorontalo &bull; SIMS Hub<br>
                <span style="font-size: 0.75rem; opacity: 0.7;">Sistem Informasi Manajemen Studio & Siaran</span>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const loginForm = document.getElementById('loginForm');
        const btnSubmit = document.getElementById('btnSubmit');
        const textNormal = document.getElementById('textNormal');
        const textLoading = document.getElementById('textLoading');

        if (loginForm) {
            loginForm.addEventListener('submit', function () {
                sessionStorage.setItem('sims_tab_auth', 'active');
                btnSubmit.disabled = true;
                textNormal.classList.add('d-none');
                textLoading.classList.remove('d-none');
            });
        }
    });
</script>
@endsection

