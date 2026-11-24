<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL; 
use Illuminate\Pagination\Paginator; 
use App\Models\User;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrap();

        // =============================================================
        // 1. KONFIGURASI URL FLEKSIBEL (AUTO-DETECT)
        // =============================================================
        // Kode ini otomatis mengecek: Apakah Anda sedang pakai Tunnel atau Localhost?
        
        try {
            // Ambil domain yang sedang diakses saat ini
            $host = request()->getHost();

            // Jika akses mengandung 'devtunnels', 'ngrok', atau IP Public (bukan lokal)
            // Maka PAKSA HTTPS agar bisa dibuka di HP/Internet
            if (str_contains($host, 'devtunnels.ms') || str_contains($host, 'ngrok') || str_contains($host, 'public')) {
                URL::forceScheme('https');
                URL::forceRootUrl("https://$host");
            }
            // Jika akses lewat Localhost (127.0.0.1), biarkan default (HTTP) agar ringan & cepat
        } catch (\Exception $e) {
            // Fallback aman jika terjadi error pembacaan request (jarang terjadi)
        }

        // =============================================================
        // 2. DEFINISI HAK AKSES (GATE)
        // =============================================================
        
        // ADMIN (IT Support)
        Gate::define('admin', function (User $user) {
            return $user->role === 'admin';
        });

        // KATIM (Penyetuju SPO)
        Gate::define('katim', function (User $user) {
            return $user->role === 'katim';
        });

        // KRU (Pembuat SPO)
        Gate::define('kru', function (User $user) {
            return $user->role === 'kru';
        });

        // TD (Technical Director / Pengarah Teknik - Penginput Monitoring)
        Gate::define('td', function (User $user) {
            return in_array($user->role, ['td', 'pd']);
        });

        // Backward compatibility gate for pd
        Gate::define('pd', function (User $user) {
            return in_array($user->role, ['td', 'pd']);
        });
    }
}