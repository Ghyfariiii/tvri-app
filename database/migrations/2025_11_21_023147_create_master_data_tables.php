<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Membuat Tabel Pegawai
        Schema::create('pegawais', function (Blueprint $table) {
            $table->id();
            $table->string('nama_lengkap');
            $table->string('nip')->nullable();
            $table->string('posisi_default')->nullable(); 
            $table->timestamps();
        });

        // 2. Membuat Tabel Alat
        Schema::create('alats', function (Blueprint $table) {
            $table->id();
            $table->string('nama_alat');
            $table->text('deskripsi')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alats');
        Schema::dropIfExists('pegawais');
    }
};