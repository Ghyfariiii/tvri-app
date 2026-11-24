<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. SEEDER USERS (Akun Login)
        // Akun Admin (KATIM - Rustam Tangahu)
        DB::table('users')->insert([
            'name' => 'Rustam Tangahu',
            'email' => 'admin@tvri.go.id',
            'nip' => '198501012010121001', // NIP Contoh
            'password' => Hash::make('password'), // Password: password
            'role' => 'admin',
            'telegram_chat_id' => null, // Nanti diisi saat testing Telegram
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Akun Kru (Fadli Lahati)
        DB::table('users')->insert([
            'name' => 'Fadli Lahati',
            'email' => 'kru@tvri.go.id',
            'nip' => '199002022015051002',
            'password' => Hash::make('password'), // Password: password
            'role' => 'kru',
            'telegram_chat_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. SEEDER PEGAWAI (Untuk Dropdown Form SPO)
        $pegawais = [
            ['nama_lengkap' => 'Rustam Tangahu', 'posisi_default' => 'Pengarah Teknik'],
            ['nama_lengkap' => 'Fadli Lahati', 'posisi_default' => 'Pengarah Teknik'],
            ['nama_lengkap' => 'Hendra Idris', 'posisi_default' => 'Maintenance'],
            ['nama_lengkap' => 'Dwi Haryono', 'posisi_default' => 'Maintenance'],
            ['nama_lengkap' => 'Moh Ilham Arman', 'posisi_default' => 'Operator Komputer'],
            ['nama_lengkap' => 'Deby H. Alam', 'posisi_default' => 'Operator Komputer'],
            ['nama_lengkap' => 'Rian Pitalawu', 'posisi_default' => 'Penata Aksara'],
            ['nama_lengkap' => 'Wahyudi Latif', 'posisi_default' => 'Kamerawan'],
            ['nama_lengkap' => 'Indra Uno', 'posisi_default' => 'Kamerawan'],
            ['nama_lengkap' => 'Agus Nurdin', 'posisi_default' => 'Kamerawan'],
            ['nama_lengkap' => 'Bakrun Juko', 'posisi_default' => 'Kamerawan'],
            ['nama_lengkap' => 'Moh. Yadi Bagu', 'posisi_default' => 'Dokumentasi'],
            ['nama_lengkap' => 'Haris Tubagus', 'posisi_default' => 'Dokumentasi'],
            ['nama_lengkap' => 'Hendra Supriyadi', 'posisi_default' => 'IT Support'],
        ];

        DB::table('pegawais')->insert($pegawais);

        // 3. SEEDER ALAT (Untuk Laporan Kerusakan)
        $alats = [
            ['nama_alat' => 'Kamera Studio 1 (Panasonic)', 'deskripsi' => 'Kamera Utama'],
            ['nama_alat' => 'Kamera Studio 2 (Panasonic)', 'deskripsi' => 'Kamera Pendamping'],
            ['nama_alat' => 'Kamera Studio 3 (Panasonic)', 'deskripsi' => 'Kamera Pendamping'],
            ['nama_alat' => 'Video Switcher', 'deskripsi' => 'Alat pengatur gambar utama'],
            ['nama_alat' => 'Audio Mixer Yamaha', 'deskripsi' => 'Pengatur suara ruang kendali'],
            ['nama_alat' => 'PC Playout', 'deskripsi' => 'Komputer pemutar materi siaran'],
            ['nama_alat' => 'PC CG (Character Generator)', 'deskripsi' => 'Komputer teks/grafis'],
            ['nama_alat' => 'Clip On Mic 1', 'deskripsi' => 'Mic Wireless'],
            ['nama_alat' => 'Clip On Mic 2', 'deskripsi' => 'Mic Wireless'],
            ['nama_alat' => 'Lampu Studio Set A', 'deskripsi' => 'Pencahayaan utama'],
        ];

        DB::table('alats')->insert($alats);
    }
}