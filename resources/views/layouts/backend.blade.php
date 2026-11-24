<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Informasi Manajemen Siaran - TVRI Gorontalo</title>
    
    <!-- PENGAMANAN SUPER KETAT: ISOLASI SESI PER-TAB -->
    <script>
        @if(session('fresh_login'))
            sessionStorage.setItem('sims_tab_auth', 'active');
        @endif

        if (!sessionStorage.getItem('sims_tab_auth')) {
            // Tab baru / URL ditempel dari luar tanpa otorisasi lokal di tab ini:
            // Wajibkan login ulang secara instan
            window.location.replace("{{ route('logout.get') }}?reason=new_tab");
        }
    </script>
    
    <link rel="icon" href="{{ asset('images/favicon.png') }}" type="image/png">
    
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5.3 & FontAwesome 6 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --primary-navy: #1a3b70;
            --primary-navy-dark: #0f244a;
            --primary-navy-light: #25529c;
            --accent-blue: #3b82f6;
            --bg-body: #f4f6fa;
            --card-radius: 1rem;
            --shadow-soft: 0 4px 20px -2px rgba(18, 38, 63, 0.06), 0 2px 6px -1px rgba(18, 38, 63, 0.03);
            --shadow-hover: 0 14px 28px -4px rgba(18, 38, 63, 0.12), 0 4px 10px -2px rgba(18, 38, 63, 0.04);
        }

        body { 
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; 
            background-color: var(--bg-body); 
            color: #2d3748;
            overflow-x: hidden; 
            letter-spacing: -0.01em;
        }

        /* CUSTOM SCROLLBAR */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        /* SIDEBAR STYLES */
        #sidebar-wrapper { 
            min-height: 100vh; 
            width: 17rem; 
            margin-left: 0; 
            background: linear-gradient(180deg, var(--primary-navy-dark) 0%, #153363 60%, var(--primary-navy) 100%);
            color: #ffffff; 
            transition: all 0.28s cubic-bezier(0.4, 0, 0.2, 1); 
            position: fixed; 
            z-index: 1040; 
            box-shadow: 4px 0 25px rgba(0, 0, 0, 0.08);
            display: flex;
            flex-direction: column;
        }

        #page-content-wrapper { 
            width: 100%; 
            margin-left: 17rem; 
            transition: all 0.28s cubic-bezier(0.4, 0, 0.2, 1); 
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        body.sb-sidenav-toggled #sidebar-wrapper { margin-left: -17rem; }
        body.sb-sidenav-toggled #page-content-wrapper { margin-left: 0; }

        /* SIDEBAR BRANDING */
        .sidebar-brand {
            padding: 1.5rem 1.25rem 1.25rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .sidebar-brand-logo {
            width: 42px;
            height: 42px;
            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
        }

        .sidebar-brand-text h5 {
            font-size: 1.05rem;
            font-weight: 800;
            letter-spacing: 0.5px;
            margin: 0;
            color: #ffffff;
        }

        .sidebar-brand-text small {
            font-size: 0.7rem;
            color: rgba(255, 255, 255, 0.6);
            letter-spacing: 1px;
            text-transform: uppercase;
            font-weight: 600;
            display: block;
        }

        /* SIDEBAR NAVIGATION ITEMS */
        .sidebar-menu {
            padding: 1rem 0;
            flex: 1;
            overflow-y: auto;
        }

        .sidebar-section-label {
            font-size: 0.7rem;
            letter-spacing: 1.2px;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.45);
            font-weight: 700;
            padding: 1rem 1.5rem 0.4rem;
        }

        #sidebar-wrapper .list-group-item { 
            background: transparent; 
            color: rgba(255, 255, 255, 0.78); 
            border: none; 
            padding: 11px 18px; 
            font-size: 0.92rem;
            font-weight: 500;
            margin: 3px 14px;
            border-radius: 10px;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
        }

        #sidebar-wrapper .list-group-item i.menu-icon {
            width: 22px;
            font-size: 1.05rem;
            margin-right: 12px;
            color: rgba(255, 255, 255, 0.65);
            transition: color 0.2s ease;
        }

        #sidebar-wrapper .list-group-item:hover { 
            background: rgba(255, 255, 255, 0.09); 
            color: #ffffff; 
            transform: translateX(3px);
        }

        #sidebar-wrapper .list-group-item:hover i.menu-icon {
            color: #60a5fa;
        }

        #sidebar-wrapper .list-group-item.active { 
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.18) 0%, rgba(255, 255, 255, 0.08) 100%); 
            color: #ffffff; 
            font-weight: 600; 
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
            border-left: 3px solid #60a5fa;
        }

        #sidebar-wrapper .list-group-item.active i.menu-icon {
            color: #93c5fd;
        }

        /* PULSING BADGE */
        .badge-pulse {
            position: relative;
            animation: pulse-soft 2s infinite;
        }
        @keyframes pulse-soft {
            0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.4); }
            70% { transform: scale(1.05); box-shadow: 0 0 0 6px rgba(245, 158, 11, 0); }
            100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(245, 158, 11, 0); }
        }

        /* SIDEBAR FOOTER (USER CARD) */
        .sidebar-footer {
            padding: 1rem 1.25rem;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            background: rgba(0, 0, 0, 0.15);
        }

        /* TOP NAVBAR */
        .top-navbar {
            backdrop-filter: blur(14px);
            background-color: rgba(255, 255, 255, 0.94);
            border-bottom: 1px solid rgba(226, 232, 240, 0.8);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.02);
            padding: 0.85rem 1.5rem;
        }

        #sidebarToggle {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #e2e8f0;
            background: #ffffff;
            color: var(--primary-navy);
            transition: all 0.2s ease;
        }
        #sidebarToggle:hover {
            background: #f1f5f9;
            transform: scale(1.03);
            border-color: #cbd5e1;
        }

        /* LIVE CLOCK CHIP */
        .status-chip {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            padding: 6px 14px;
            font-size: 0.82rem;
            font-weight: 600;
            color: #475569;
            box-shadow: 0 1px 3px rgba(0,0,0,0.03);
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .dot-indicator {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: #10b981;
            display: inline-block;
            box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2);
        }

        /* USER PROFILE DROPDOWN */
        .user-dropdown-btn {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 30px;
            padding: 4px 14px 4px 6px;
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .user-dropdown-btn:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
            box-shadow: 0 4px 12px rgba(0,0,0,0.04);
        }

        /* CARDS & MODERN UTILITIES */
        .card-modern {
            border: 1px solid rgba(226, 232, 240, 0.8);
            border-radius: var(--card-radius);
            box-shadow: var(--shadow-soft);
            background: #ffffff;
            transition: all 0.25s cubic-bezier(0.165, 0.84, 0.44, 1);
        }

        .card-hover:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-hover) !important;
        }

        .btn-gradient-primary {
            background: linear-gradient(135deg, var(--primary-navy) 0%, var(--primary-navy-light) 100%);
            border: none;
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(26, 59, 112, 0.25);
            transition: all 0.2s ease;
        }
        .btn-gradient-primary:hover {
            background: linear-gradient(135deg, var(--primary-navy-dark) 0%, var(--primary-navy) 100%);
            color: #ffffff;
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(26, 59, 112, 0.35);
        }

        /* SOFT BADGES */
        .badge-soft-primary { background: rgba(26, 59, 112, 0.1); color: var(--primary-navy); border: 1px solid rgba(26, 59, 112, 0.2); }
        .badge-soft-success { background: rgba(16, 185, 129, 0.1); color: #047857; border: 1px solid rgba(16, 185, 129, 0.2); }
        .badge-soft-warning { background: rgba(245, 158, 11, 0.1); color: #b45309; border: 1px solid rgba(245, 158, 11, 0.2); }
        .badge-soft-danger  { background: rgba(239, 68, 68, 0.1); color: #b91c1c; border: 1px solid rgba(239, 68, 68, 0.2); }
        .badge-soft-info    { background: rgba(6, 182, 212, 0.1); color: #0e7490; border: 1px solid rgba(6, 182, 212, 0.2); }

        /* ROLE CHIP COLORS */
        .role-admin  { background: #ede9fe; color: #6d28d9; }
        .role-katim  { background: #dbeafe; color: #1d4ed8; }
        .role-td     { background: #ccfbf1; color: #0f766e; }
        .role-pd     { background: #ccfbf1; color: #0f766e; }
        .role-kru    { background: #dcfce7; color: #15803d; }

        /* SOFT ACTION BUTTONS */
        .btn-soft-primary { background: rgba(26, 59, 112, 0.08); color: var(--primary-navy); border: 1px solid rgba(26, 59, 112, 0.15); }
        .btn-soft-primary:hover { background: var(--primary-navy); color: #ffffff; }
        .btn-soft-success { background: rgba(16, 185, 129, 0.1); color: #047857; border: 1px solid rgba(16, 185, 129, 0.2); }
        .btn-soft-success:hover { background: #10b981; color: #ffffff; }
        .btn-soft-warning { background: rgba(245, 158, 11, 0.12); color: #b45309; border: 1px solid rgba(245, 158, 11, 0.25); }
        .btn-soft-warning:hover { background: #f59e0b; color: #ffffff; }
        .btn-soft-danger  { background: rgba(239, 68, 68, 0.1); color: #b91c1c; border: 1px solid rgba(239, 68, 68, 0.2); }
        .btn-soft-danger:hover  { background: #ef4444; color: #ffffff; }
        .btn-soft-info    { background: rgba(6, 182, 212, 0.1); color: #0e7490; border: 1px solid rgba(6, 182, 212, 0.2); }
        .btn-soft-info:hover    { background: #06b6d4; color: #ffffff; }

        .toggle-password, .toggle-password-btn {
            cursor: pointer !important;
            user-select: none;
        }

        .btn-action-icon {
            width: 32px;
            height: 32px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            font-size: 0.85rem;
            transition: all 0.2s ease;
        }
        .btn-action-icon:hover {
            transform: translateY(-2px);
        }

        /* MODERN TABLE STYLING */
        .table-modern thead th {
            background: #f8fafc;
            color: #475569;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            border-bottom: 1px solid #e2e8f0;
            padding: 13px 16px;
            white-space: nowrap;
        }
        .table-modern tbody td {
            padding: 13px 16px;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
        }
        .table-modern tbody tr:last-child td {
            border-bottom: none;
        }
        .table-modern tbody tr:hover {
            background-color: rgba(248, 250, 252, 0.8);
        }

        /* MODERN FORM CONTROLS */
        .form-control-modern, .form-select-modern {
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            padding: 0.65rem 0.95rem;
            font-size: 0.88rem;
            color: #1e293b;
            background-color: #ffffff;
            transition: all 0.2s ease-in-out;
        }
        .form-control-modern:focus, .form-select-modern:focus {
            border-color: var(--primary-navy-light);
            box-shadow: 0 0 0 3px rgba(26, 59, 112, 0.12);
            background-color: #ffffff;
            outline: none;
        }
        .form-control-modern::placeholder {
            color: #94a3b8;
            font-size: 0.85rem;
        }
        .form-label-modern {
            font-weight: 600;
            font-size: 0.8rem;
            color: #475569;
            margin-bottom: 0.45rem;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .input-group-modern {
            display: flex;
            border-radius: 10px;
        }
        .input-group-modern .input-group-text {
            background-color: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-right: none;
            border-radius: 10px 0 0 10px;
            color: #64748b;
            font-size: 0.88rem;
            transition: all 0.2s ease;
        }
        .input-group-modern .form-control-modern {
            border-left: none;
            border-radius: 0 10px 10px 0;
        }
        .input-group-modern:focus-within .input-group-text {
            border-color: var(--primary-navy-light);
            color: var(--primary-navy);
            background-color: rgba(26, 59, 112, 0.04);
        }
        .input-group-modern:focus-within .form-control-modern {
            border-color: var(--primary-navy-light);
            box-shadow: 0 0 0 3px rgba(26, 59, 112, 0.12);
        }

        /* SIDEBAR BACKDROP OVERLAY */
        .sidebar-backdrop {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(15, 23, 42, 0.65);
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
            z-index: 1038;
            opacity: 0;
            transition: opacity 0.25s ease-in-out;
            cursor: pointer;
        }

        /* MOBILE RESPONSIVE CARD TABLES */
        @media (max-width: 767.98px) {
            .table-mobile-cards thead {
                display: none !important;
            }
            .table-mobile-cards tbody,
            .table-mobile-cards tr,
            .table-mobile-cards td {
                display: block !important;
                width: 100% !important;
            }
            .table-mobile-cards tr {
                position: relative;
                padding: 12px 14px;
                margin-bottom: 12px;
                background: #ffffff;
                border: 1.5px solid #e2e8f0;
                border-radius: 14px;
                box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
            }
            .table-mobile-cards tr:hover {
                background: #ffffff !important;
            }
            .table-mobile-cards td {
                padding: 4px 0 !important;
                border: none !important;
            }
            .table-mobile-cards td:first-child {
                padding-right: 46px !important; /* Ruang untuk tombol hapus di pojok kanan atas */
            }
            .table-mobile-cards td.td-actions {
                position: absolute;
                top: 10px;
                right: 10px;
                width: auto !important;
                padding: 0 !important;
            }
            .mobile-label {
                display: block !important;
                font-size: 0.74rem;
                font-weight: 700;
                color: #64748b;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                margin-bottom: 4px;
            }
        }
        @media (min-width: 768px) {
            .mobile-label {
                display: none !important;
            }
        }

        /* RESPONSIVE DESIGN */
        @media (max-width: 768px) { 
            #sidebar-wrapper { 
                margin-left: -17rem; 
                z-index: 1045; 
                box-shadow: none;
            } 
            #page-content-wrapper { margin-left: 0; } 
            body.sb-sidenav-toggled #sidebar-wrapper { 
                margin-left: 0; 
                box-shadow: 10px 0 35px rgba(0, 0, 0, 0.4);
            } 
            body.sb-sidenav-toggled #page-content-wrapper { margin-left: 0; } 
            body.sb-sidenav-toggled .sidebar-backdrop {
                display: block;
                opacity: 1;
            }
        }
    </style>
</head>
<body>

<div class="d-flex" id="wrapper">

    <!-- SIDEBAR WRAPPER -->
    <div id="sidebar-wrapper">
        
        <!-- SIDEBAR BRANDING -->
        <div class="sidebar-brand d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2 overflow-hidden">
                <div class="sidebar-brand-logo bg-white p-1 shadow-sm flex-shrink-0">
                    <img src="{{ asset('images/logo.png') }}" 
                         alt="Logo TVRI" 
                         style="max-width: 30px; max-height: 30px; object-fit: contain;" 
                         onerror="this.style.display='none'; document.getElementById('sidebar-fallback-icon').style.display='block';">
                    <i id="sidebar-fallback-icon" class="fas fa-broadcast-tower fa-lg text-primary" style="display: none;"></i>
                </div>
                <div class="sidebar-brand-text text-truncate">
                    <h5>SIMS TVRI</h5>
                    <small>Stasiun Gorontalo</small>
                </div>
            </div>
            <!-- TOMBOL TUTUP SIDEBAR KHUSUS MOBILE (X) -->
            <button type="button" id="sidebarCloseBtn" class="btn btn-sm text-white-50 p-2 d-md-none border-0" title="Tutup Menu">
                <i class="fas fa-times fa-lg"></i>
            </button>
        </div>

        <!-- SIDEBAR NAVIGATION MENU -->
        <div class="sidebar-menu">
            
            <a href="{{ route('dashboard') }}" class="list-group-item {{ Request::is('dashboard') || Request::is('home') ? 'active' : '' }}">
                <i class="fas fa-chart-pie menu-icon"></i>
                <span>Dashboard</span>
            </a>

            <div class="sidebar-section-label">Operasional Siaran</div>
            
            <a href="{{ route('spo.index') }}" class="list-group-item {{ Request::is('spo*') ? 'active' : '' }} justify-content-between">
                <div class="d-flex align-items-center">
                    <i class="fas fa-clipboard-list menu-icon"></i>
                    <span>Kelola SPO</span>
                </div>
                @if(Auth::check() && in_array(Auth::user()->role, ['katim', 'admin']))
                    @php
                        $spoPendingQuery = \Illuminate\Support\Facades\DB::table('spos')->where('status_persetujuan', 'pending');
                        $spoRevQuery = \Illuminate\Support\Facades\DB::table('revisi_requests')
                            ->join('spos', 'revisi_requests.dokumen_id', '=', 'spos.id')
                            ->where('revisi_requests.jenis_dokumen', 'SPO')
                            ->where('revisi_requests.status', 'pending');
                        if (Auth::user()->role === 'katim') {
                            $spoPendingQuery->where('spos.katim_id', Auth::id());
                            $spoRevQuery->where('spos.katim_id', Auth::id());
                        }
                        $totalPendingSPO = $spoPendingQuery->count() + $spoRevQuery->count();
                    @endphp
                    @if($totalPendingSPO > 0)
                        <span class="badge bg-warning text-dark rounded-pill fw-bold badge-pulse px-2 py-1 shadow-sm" title="{{ $totalPendingSPO }} tugas SPO menunggu persetujuan">{{ $totalPendingSPO }}</span>
                    @endif
                @endif
            </a>

            <a href="{{ route('monitoring.index') }}" class="list-group-item {{ Request::is('monitoring*') ? 'active' : '' }} justify-content-between">
                <div class="d-flex align-items-center">
                    <i class="fas fa-desktop menu-icon"></i>
                    <span>Monitoring Siaran</span>
                </div>
                @if(Auth::check() && in_array(Auth::user()->role, ['katim', 'admin']))
                    @php
                        $notifQuery = \Illuminate\Support\Facades\DB::table('monitoring_harians')
                            ->join('spos', 'monitoring_harians.spo_id', '=', 'spos.id')
                            ->where('monitoring_harians.status_revisi', 'pending');
                        if (Auth::user()->role === 'katim') {
                            $notifQuery->where('spos.katim_id', Auth::id());
                        }
                        $notifPendingRevisi = $notifQuery->count();
                    @endphp
                    @if($notifPendingRevisi > 0)
                        <span class="badge bg-warning text-dark rounded-pill fw-bold badge-pulse px-2 py-1 shadow-sm" title="{{ $notifPendingRevisi }} permohonan revisi laporan siaran">{{ $notifPendingRevisi }}</span>
                    @endif
                @endif
            </a>

            @if(Auth::user()->role == 'admin')
                <div class="sidebar-section-label">Master & Administrasi</div>
                <a href="{{ route('users.index') }}" class="list-group-item {{ Request::is('users*') ? 'active' : '' }}">
                    <i class="fas fa-user-shield menu-icon"></i>
                    <span>Manajemen User</span>
                </a>
                <a href="{{ route('master.pegawai.index') }}" class="list-group-item {{ Request::is('master/pegawai*') ? 'active' : '' }}">
                    <i class="fas fa-id-badge menu-icon"></i>
                    <span>Data Pegawai</span>
                </a>
                <a href="{{ route('master.alat.index') }}" class="list-group-item {{ Request::is('master/alat*') ? 'active' : '' }}">
                    <i class="fas fa-tools menu-icon"></i>
                    <span>Manajemen Alat</span>
                </a>
            @endif

            <div class="sidebar-section-label">Arsip & Rekapitulasi</div>
            <a href="{{ route('monitoring.export') }}" class="list-group-item">
                <i class="fas fa-file-excel menu-icon text-success"></i>
                <span>Export Laporan Excel</span>
            </a>
            <a href="{{ route('monitoring.export.pdf') }}" class="list-group-item">
                <i class="fas fa-file-pdf menu-icon text-danger"></i>
                <span>Cetak Rekap PDF</span>
            </a>
        </div>

        <!-- SIDEBAR FOOTER (USER PROFILE CHIP) -->
        <div class="sidebar-footer">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2 overflow-hidden">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=2563eb&color=fff&size=80" 
                         class="rounded-circle border border-white border-opacity-25" width="34" height="34" alt="Avatar">
                    <div class="overflow-hidden">
                        <div class="text-truncate fw-bold text-white small" style="max-width: 120px;">{{ Auth::user()->name }}</div>
                        <span class="badge role-{{ Auth::user()->role }} rounded-pill" style="font-size: 9px; padding: 2px 7px;">
                            {{ strtoupper(Auth::user()->role) }}
                        </span>
                    </div>
                </div>
                <a href="{{ route('logout') }}" onclick="event.preventDefault(); sessionStorage.removeItem('sims_tab_auth'); document.getElementById('sidebar-logout-form').submit();" class="text-white opacity-50 hover-opacity-100" title="Keluar">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
                <form id="sidebar-logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
            </div>
        </div>

    </div>

    <!-- BACKDROP OVERLAY KHUSUS MOBILE -->
    <div id="sidebarBackdrop" class="sidebar-backdrop"></div>

    <!-- MAIN CONTENT WRAPPER -->
    <div id="page-content-wrapper">
        
        <!-- TOP NAVBAR -->
        <nav class="navbar navbar-expand-lg top-navbar sticky-top">
            <div class="d-flex align-items-center justify-content-between w-100">
                
                <div class="d-flex align-items-center gap-3">
                    <button id="sidebarToggle" title="Buka/Tutup Menu Sidebar">
                        <i class="fas fa-bars"></i>
                    </button>
                    
                    <div class="d-none d-md-flex align-items-center gap-2">
                        <div class="status-chip">
                            <span class="dot-indicator"></span>
                            <span>SIMS Operasional TVRI</span>
                        </div>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-3">
                    
                    <!-- LIVE DATE & TIME WITA CHIP -->
                    <div class="status-chip d-none d-sm-inline-flex">
                        <i class="far fa-calendar-alt text-primary"></i>
                        <span id="liveClock">{{ date('d M Y') }} | <span id="witaTime">--:--:--</span> WITA</span>
                    </div>

                    <!-- LIVE AUTO-SYNC CHIP -->
                    <div class="status-chip d-none d-md-inline-flex" id="liveSyncStatus" style="cursor: pointer;" title="Sinkronisasi Data Otomatis: Aktif (Klik untuk jeda/aktifkan)">
                        <span class="dot-indicator bg-success" id="liveSyncDot" style="width: 8px; height: 8px; border-radius: 50%; display: inline-block; box-shadow: 0 0 8px #10b981;"></span>
                        <span class="small fw-semibold text-dark" id="liveSyncText">Live Sync: Aktif</span>
                    </div>

                    <!-- USER PROFILE DROPDOWN -->
                    <div class="dropdown">
                        <div class="user-dropdown-btn shadow-sm" id="userMenuDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=1a3b70&color=fff&size=100" 
                                 class="rounded-circle shadow-sm" width="32" height="32" alt="{{ Auth::user()->name }}">
                            <div class="d-none d-md-block text-start leading-none pe-1">
                                <div class="fw-bold text-dark small" style="font-size: 0.82rem; line-height: 1.1;">{{ Auth::user()->name }}</div>
                                <small class="text-muted text-uppercase" style="font-size: 0.68rem; font-weight: 600;">{{ Auth::user()->role }}</small>
                            </div>
                            <i class="fas fa-chevron-down text-muted small ms-1"></i>
                        </div>
                        <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 py-2 mt-2" aria-labelledby="userMenuDropdown" style="min-width: 220px;">
                            <li class="px-3 py-2 border-bottom mb-1 bg-light bg-opacity-50">
                                <span class="d-block text-muted small fw-bold">Masuk Sebagai:</span>
                                <span class="d-block fw-bold text-dark text-truncate">{{ Auth::user()->email }}</span>
                                <span class="badge role-{{ Auth::user()->role }} rounded-pill mt-1" style="font-size: 10px;">
                                    Role: {{ ucfirst(Auth::user()->role) }}
                                </span>
                            </li>
                            <li>
                                <a class="dropdown-item py-2 d-flex align-items-center gap-2" href="{{ route('profile.edit') }}">
                                    <i class="fas fa-user-circle text-primary" style="width: 18px;"></i>
                                    <span>Profil & Telegram Bind</span>
                                </a>
                            </li>
                            @if(Auth::user()->role === 'admin')
                            <li>
                                <a class="dropdown-item py-2 d-flex align-items-center gap-2" href="{{ route('users.index') }}">
                                    <i class="fas fa-users-cog text-secondary" style="width: 18px;"></i>
                                    <span>Manajemen Pengguna</span>
                                </a>
                            </li>
                            @endif
                            <li><hr class="dropdown-divider my-1"></li>
                            <li>
                                <a class="dropdown-item py-2 text-danger fw-bold d-flex align-items-center gap-2" href="{{ route('logout') }}" onclick="event.preventDefault(); sessionStorage.removeItem('sims_tab_auth'); document.getElementById('navbar-logout-form').submit();">
                                    <i class="fas fa-sign-out-alt" style="width: 18px;"></i>
                                    <span>Keluar Sistem</span>
                                </a>
                                <form id="navbar-logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
                            </li>
                        </ul>
                    </div>

                </div>

            </div>
        </nav>

        <!-- MAIN CONTAINER -->
        <div class="container-fluid px-3 px-md-4 py-4 flex-grow-1">
            
            <!-- FLASH MESSAGES WITH MODERN TOAST CARDS -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-4 p-3 mb-4 d-flex align-items-center gap-3" role="alert">
                    <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center p-2" style="width: 36px; height: 36px; flex-shrink: 0;">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="flex-grow-1">
                        <strong class="d-block text-success">Berhasil!</strong>
                        <span class="text-dark small">{{ session('success') }}</span>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-4 p-3 mb-4 d-flex align-items-center gap-3" role="alert">
                    <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center p-2" style="width: 36px; height: 36px; flex-shrink: 0;">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="flex-grow-1">
                        <strong class="d-block text-danger">Pemberitahuan:</strong>
                        <span class="text-dark small">{{ session('error') }}</span>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            
            @yield('content')

        </div>

        <!-- FOOTER -->
        <footer class="py-3 px-4 bg-white border-top text-center text-md-between d-md-flex align-items-center justify-content-between small text-muted">
            <div>
                &copy; {{ date('Y') }} <strong>LPP TVRI Stasiun Gorontalo</strong>. All rights reserved.
            </div>
            <div class="mt-2 mt-md-0">
                <span class="badge bg-light text-secondary border">SIMS v2.4 Enterprise</span>
            </div>
        </footer>

    </div>

</div>

<!-- JAVASCRIPT LIBRARIES -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // SIDEBAR TOGGLE & MOBILE DISMISS
    window.addEventListener('DOMContentLoaded', event => {
        const sidebarToggle = document.body.querySelector('#sidebarToggle');
        const sidebarCloseBtn = document.body.querySelector('#sidebarCloseBtn');
        const sidebarBackdrop = document.body.querySelector('#sidebarBackdrop');

        function openSidebar() {
            document.body.classList.add('sb-sidenav-toggled');
        }

        function closeSidebar() {
            document.body.classList.remove('sb-sidenav-toggled');
        }

        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', event => {
                event.preventDefault();
                document.body.classList.toggle('sb-sidenav-toggled');
            });
        }

        if (sidebarCloseBtn) {
            sidebarCloseBtn.addEventListener('click', event => {
                event.preventDefault();
                closeSidebar();
            });
        }

        if (sidebarBackdrop) {
            sidebarBackdrop.addEventListener('click', () => {
                closeSidebar();
            });
        }

        // Tutup otomatis jika tautan menu diklik pada layar mobile
        document.querySelectorAll('#sidebar-wrapper .list-group-item').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth <= 768) {
                    closeSidebar();
                }
            });
        });

        // Tutup sidebar jika pengguna menekan tombol Escape
        document.addEventListener('keydown', event => {
            if (event.key === 'Escape' && document.body.classList.contains('sb-sidenav-toggled')) {
                closeSidebar();
            }
        });

        // LIVE WITA CLOCK
        function updateWitaClock() {
            const now = new Date();
            // Offset WITA is UTC+8
            const utc = now.getTime() + (now.getTimezoneOffset() * 60000);
            const witaTime = new Date(utc + (3600000 * 8));
            
            const hours = String(witaTime.getHours()).padStart(2, '0');
            const minutes = String(witaTime.getMinutes()).padStart(2, '0');
            const seconds = String(witaTime.getSeconds()).padStart(2, '0');
            
            const clockEl = document.getElementById('witaTime');
            if (clockEl) {
                clockEl.textContent = `${hours}:${minutes}:${seconds}`;
            }
        }
        setInterval(updateWitaClock, 1000);
        updateWitaClock();

        // GLOBAL SUBMIT BUTTON SPINNER HELPER
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function() {
                const submitBtn = this.querySelector('button[type="submit"]:not(.no-spin)');
                if (submitBtn && !submitBtn.disabled) {
                    const originalHtml = submitBtn.innerHTML;
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Memproses...`;
                    
                    // Fallback to re-enable after 8 seconds in case validation halts submit
                    setTimeout(() => {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalHtml;
                    }, 8000);
                }
            });
        });

    // GLOBAL PASSWORD VISIBILITY TOGGLE FUNCTION
    window.togglePasswordVisibility = function(btn) {
        if (!btn) return;
        var targetId = btn.getAttribute('data-target');
        var input = targetId ? document.getElementById(targetId) : null;
        if (!input) {
            var group = btn.closest('.input-group');
            if (group) {
                input = group.querySelector('input[type="password"], input[type="text"]');
            }
        }
        if (!input) return;

        var icon = btn.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            if (icon) {
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            }
        } else {
            input.type = 'password';
            if (icon) {
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    };

    // FALLBACK DELEGATION FOR ANY BUTTON WITHOUT INLINE ONCLICK
    document.addEventListener('click', function(e) {
        var btn = e.target.closest('.toggle-password, .toggle-password-btn');
        if (!btn || btn.hasAttribute('onclick')) return;
        e.preventDefault();
        window.togglePasswordVisibility(btn);
    });
    });

    // ===================================================
    // REAL-TIME AUTO-REFRESH & DATA CHANGE DETECTION
    // ===================================================
    (function() {
        let initialHash = null;
        let isSyncActive = localStorage.getItem('tvri_auto_sync') !== 'false';
        const syncUrl = "{{ route('sync.check') }}";

        const syncChip = document.getElementById('liveSyncStatus');
        const syncDot = document.getElementById('liveSyncDot');
        const syncText = document.getElementById('liveSyncText');

        function updateSyncBadge() {
            if (!syncChip) return;
            if (isSyncActive) {
                if (syncDot) {
                    syncDot.className = 'dot-indicator bg-success';
                    syncDot.style.boxShadow = '0 0 8px #10b981';
                }
                if (syncText) syncText.textContent = 'Live Sync: Aktif';
                syncChip.setAttribute('title', 'Sinkronisasi Otomatis: Aktif (Klik untuk jeda)');
            } else {
                if (syncDot) {
                    syncDot.className = 'dot-indicator bg-secondary';
                    syncDot.style.boxShadow = 'none';
                }
                if (syncText) syncText.textContent = 'Live Sync: Jeda';
                syncChip.setAttribute('title', 'Sinkronisasi Otomatis: Dijeda (Klik untuk aktifkan)');
            }
        }

        if (syncChip) {
            syncChip.addEventListener('click', function() {
                isSyncActive = !isSyncActive;
                localStorage.setItem('tvri_auto_sync', isSyncActive ? 'true' : 'false');
                updateSyncBadge();
            });
            updateSyncBadge();
        }

        // Jangan auto-refresh jika pengguna sedang mengetik di input atau modal sedang terbuka
        function isUserBusy() {
            const activeEl = document.activeElement;
            const isTyping = activeEl && (
                activeEl.tagName === 'INPUT' || 
                activeEl.tagName === 'TEXTAREA' || 
                activeEl.tagName === 'SELECT' || 
                activeEl.isContentEditable
            );
            const isModalOpen = document.querySelector('.modal.show') !== null;
            return isTyping || isModalOpen;
        }

        function checkDataChanges() {
            if (!isSyncActive) return;

            fetch(syncUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(res => {
                if (!res.ok) throw new Error('Network error');
                return res.json();
            })
            .then(data => {
                if (!data || !data.hash) return;

                if (initialHash === null) {
                    initialHash = data.hash;
                } else if (data.hash !== initialHash) {
                    initialHash = data.hash;

                    if (isUserBusy()) {
                        showUpdateToast(false);
                    } else {
                        showUpdateToast(true);
                    }
                }
            })
            .catch(err => {
                // Abaikan kesalahan jaringan sesaat
            });
        }

        function showUpdateToast(autoReload) {
            let toastEl = document.getElementById('realtimeSyncToast');
            if (!toastEl) {
                toastEl = document.createElement('div');
                toastEl.id = 'realtimeSyncToast';
                toastEl.style.cssText = 'position: fixed; bottom: 25px; right: 25px; z-index: 9999; background: #1a3b70; color: #fff; padding: 12px 22px; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.3); font-size: 13px; display: flex; align-items: center; gap: 12px; transition: all 0.3s ease; animation: slideUp 0.3s ease-out; border-left: 4px solid #10b981;';
                document.body.appendChild(toastEl);
            }

            if (autoReload) {
                toastEl.innerHTML = `<span class="spinner-border spinner-border-sm text-info" role="status"></span><span><strong>Pembaruan Terdeteksi:</strong> Menyinkronkan halaman otomatis...</span>`;
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                toastEl.innerHTML = `<i class="fas fa-sync-alt text-warning fa-spin"></i><span><strong>Data Baru Tersedia:</strong> <a href="javascript:location.reload()" style="color:#67e8f9; font-weight:bold; text-decoration:underline; margin-left: 5px;">Klik untuk Perbarui</a></span>`;
            }
        }

        // Pengecekan perubahan setiap 5 detik
        setInterval(checkDataChanges, 5000);
    })();
</script>

@stack('scripts')
@yield('scripts')

</body>
</html>
