@extends('layouts.backend')

@section('content')
<div class="container-fluid">

    {{-- HEADER HALAMAN --}}
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge badge-soft-primary px-3 py-1 rounded-pill small fw-semibold">
                    <i class="fas fa-user-shield me-1"></i> Hak Akses
                </span>
            </div>
            <h1 class="h3 mb-1 mt-2 text-gray-800 fw-bold">Manajemen Pengguna</h1>
            <p class="text-muted small mb-0">Kelola akun, penugasan hak akses, dan peranan pengguna sistem SIMS TVRI.</p>
        </div>
        <div class="mt-3 mt-sm-0">
            <a href="{{ route('users.create') }}" class="btn btn-gradient-primary rounded-pill px-4 shadow-sm">
                <i class="fas fa-user-plus me-2"></i> Tambah User Baru
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 rounded-4 shadow-sm mb-4 d-flex align-items-center p-3" style="background: rgba(16,185,129,0.1); color: #047857;" role="alert">
            <i class="fas fa-check-circle fa-lg me-3"></i>
            <div>
                <h6 class="fw-bold mb-0">Sukses</h6>
                <span class="small">{{ session('success') }}</span>
            </div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 rounded-4 shadow-sm mb-4 d-flex align-items-center p-3" style="background: rgba(239,68,68,0.1); color: #b91c1c;" role="alert">
            <i class="fas fa-exclamation-triangle fa-lg me-3"></i>
            <div>
                <h6 class="fw-bold mb-0">Peringatan</h6>
                <span class="small">{{ session('error') }}</span>
            </div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card card-modern overflow-hidden">
        <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <div class="rounded-3 p-2 me-3" style="background: rgba(26,59,112,0.08); color: var(--primary-navy);">
                    <i class="fas fa-users"></i>
                </div>
                <div>
                    <h6 class="m-0 fw-bold text-dark">Daftar Akun Pengguna</h6>
                    <small class="text-muted">Total <strong>{{ count($users) }}</strong> pengguna terdaftar</small>
                </div>
            </div>
            <span class="badge badge-soft-primary rounded-pill px-3 py-2 fw-semibold">
                {{ count($users) }} Pengguna
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-modern align-middle mb-0">
                    <thead>
                        <tr>
                            <th width="5%" class="text-center">No</th>
                            <th width="26%">Pengguna</th>
                            <th width="15%">NIP / ID</th>
                            <th width="18%">Email</th>
                            <th width="14%" class="text-center">Telegram ID</th>
                            <th width="12%" class="text-center">Peranan</th>
                            <th width="10%" class="text-end pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $index => $user)
                        <tr>
                            <td class="text-center fw-bold text-muted small">{{ $index + 1 }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=1a3b70&color=fff&size=38" 
                                         class="rounded-circle me-3 shadow-sm" width="38" height="38" alt="Avatar">
                                    <div>
                                        <div class="fw-bold text-dark small">{{ $user->name }}</div>
                                        <small class="text-muted" style="font-size: 0.72rem;">
                                            <i class="fas fa-calendar-alt me-1"></i> Sejak {{ $user->created_at->format('d M Y') }}
                                        </small>
                                    </div>
                                </div>
                            </td>
                            <td><span class="font-monospace text-secondary small">{{ $user->nip ?? '-' }}</span></td>
                            <td><span class="small text-dark">{{ $user->email }}</span></td>
                            <td class="text-center">
                                @if($user->telegram_chat_id)
                                    <div class="d-inline-flex align-items-center gap-1">
                                        <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 rounded-pill px-2 py-1 font-monospace" style="font-size: 0.75rem;" title="Chat ID: {{ $user->telegram_chat_id }}">
                                            <i class="fab fa-telegram-plane me-1"></i> {{ $user->telegram_chat_id }}
                                        </span>
                                        <form action="{{ route('users.disconnect_telegram', $user->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Putuskan sambungan Telegram untuk pengguna {{ $user->name }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger p-0 px-1 border-0" title="Putuskan Sambungan Telegram" style="font-size: 0.75rem;">
                                                <i class="fas fa-unlink text-danger"></i>
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <span class="badge bg-secondary bg-opacity-10 text-muted rounded-pill px-2 py-1" style="font-size: 0.72rem;">
                                        <i class="fas fa-times-circle me-1 text-danger opacity-50"></i> Belum Terhubung
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($user->role == 'admin')
                                    <span class="badge role-admin rounded-pill px-3 py-1 fw-semibold"><i class="fas fa-shield-alt me-1"></i> Admin</span>
                                @elseif($user->role == 'katim')
                                    <span class="badge role-katim rounded-pill px-3 py-1 fw-semibold"><i class="fas fa-user-tie me-1"></i> Katim</span>
                                @elseif($user->role == 'td' || $user->role == 'pd')
                                    <span class="badge role-td rounded-pill px-3 py-1 fw-semibold"><i class="fas fa-video me-1"></i> TD</span>
                                @else
                                    <span class="badge role-kru rounded-pill px-3 py-1 fw-semibold"><i class="fas fa-user me-1"></i> Kru</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus user {{ $user->name }}?');">
                                    <a href="{{ route('users.edit', $user->id) }}" class="btn btn-soft-warning btn-action-icon me-1" title="Edit Data Pengguna">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-soft-danger btn-action-icon" title="Hapus User">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center p-4 mb-3">
                                    <i class="fas fa-users-slash fa-3x text-muted opacity-50"></i>
                                </div>
                                <h6 class="fw-bold text-dark mb-1">Belum Ada Data Pengguna</h6>
                                <p class="small text-muted mb-0">Silakan tambahkan akun pengguna baru ke dalam sistem.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
