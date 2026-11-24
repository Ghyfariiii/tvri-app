<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('monitoring_harians', function (Blueprint $table) {
            // Kita tambahkan kolom-kolom untuk laporan PD
            $table->string('kondisi_audio')->nullable()->after('kru_bertugas');
            $table->string('kondisi_video')->nullable()->after('kondisi_audio');
            $table->text('catatan_khusus')->nullable()->after('kondisi_video');
            $table->string('status_siaran')->default('sukses')->after('catatan_khusus');
        });
    }

    public function down(): void
    {
        Schema::table('monitoring_harians', function (Blueprint $table) {
            $table->dropColumn(['kondisi_audio', 'kondisi_video', 'catatan_khusus', 'status_siaran']);
        });
    }
};