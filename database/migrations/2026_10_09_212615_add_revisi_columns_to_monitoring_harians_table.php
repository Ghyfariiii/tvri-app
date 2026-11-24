<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('monitoring_harians', function (Blueprint $table) {
            $table->enum('status_revisi', ['none', 'pending', 'approved', 'rejected'])
                  ->default('none')
                  ->after('status_siaran');
            $table->text('alasan_revisi')->nullable()->after('status_revisi');
            $table->unsignedBigInteger('revisi_approved_by')->nullable()->after('alasan_revisi');
            $table->timestamp('revisi_approved_at')->nullable()->after('revisi_approved_by');
            $table->foreign('revisi_approved_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('monitoring_harians', function (Blueprint $table) {
            $table->dropForeign(['revisi_approved_by']);
            $table->dropColumn(['status_revisi', 'alasan_revisi', 'revisi_approved_by', 'revisi_approved_at']);
        });
    }
};
