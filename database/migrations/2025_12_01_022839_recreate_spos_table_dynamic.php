<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Buat tabel baru jika belum ada (aman untuk rollback & production)
        if (!Schema::hasTable('spos')) {
            Schema::create('spos', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('katim_id')->nullable()->after('id');
                $table->string('no_surat');
                $table->date('tanggal_pelaksanaan');
                $table->string('hari');
                
                // KUNCI PERUBAHAN DISINI:
                // Kita ganti belasan kolom petugas menjadi 2 kolom JSON
                $table->json('petugas_penyiaran')->nullable(); // Menampung array: [{jabatan: '...', nama: '...'}]
                $table->json('petugas_penunjang')->nullable(); // Menampung array: [{jabatan: '...', nama: '...'}]

                // Kolom Persetujuan (Tetap Sama)
                $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
                $table->enum('status_persetujuan', ['pending', 'approved', 'rejected'])->default('pending');
                $table->foreignId('disetujui_oleh')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamp('tanggal_disetujui')->nullable();
                $table->text('alasan_penolakan')->nullable();

                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('spos');
    }

};