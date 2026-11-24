<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('revisi_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('spo_id')->nullable()->constrained('spos')->onDelete('cascade');
            $table->foreignId('monitoring_id')->nullable()->constrained('monitoring_harians')->onDelete('cascade');
            $table->foreignId('diajukan_oleh')->constrained('users')->onDelete('cascade');
            $table->foreignId('disetujui_oleh')->nullable()->constrained('users')->onDelete('set null');
            $table->text('alasan_revisi');
            $table->enum('status', ['pending', 'disetujui', 'ditolak'])->default('pending');
            $table->timestamp('tanggal_pengajuan')->useCurrent();
            $table->timestamp('tanggal_persetujuan')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('revisi_requests');
    }
};
