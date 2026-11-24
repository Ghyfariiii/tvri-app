<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabel Header (Satu hari satu laporan)
        // Kita cek dulu, create hanya jika tabel belum ada (biar aman)
        if (!Schema::hasTable('monitoring_harians')) {
            Schema::create('monitoring_harians', function (Blueprint $table) {
                $table->id();
                $table->date('tanggal');
                $table->foreignId('spo_id')->nullable()->constrained('spos')->onDelete('set null');
                $table->foreignId('created_by')->constrained('users')->onDelete('cascade'); 
                $table->json('kru_bertugas')->nullable(); 
                $table->timestamps();
            });
        }

        // 2. Tabel Detail (Log Kegiatan per baris)
        if (!Schema::hasTable('monitoring_details')) {
            Schema::create('monitoring_details', function (Blueprint $table) {
                $table->id();
                $table->foreignId('monitoring_harian_id')->constrained('monitoring_harians')->onDelete('cascade');
                
                $table->time('jam');
                $table->string('kegiatan'); 
                $table->string('status'); 
                
                // Fitur Baru
                $table->string('jenis_kendala')->nullable();
                $table->boolean('is_mendesak')->default(false);
                $table->foreignId('alat_rusak_id')->nullable()->constrained('alats')->onDelete('set null'); 
                
                $table->text('catatan')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('monitoring_details');
        Schema::dropIfExists('monitoring_harians');
    }
};