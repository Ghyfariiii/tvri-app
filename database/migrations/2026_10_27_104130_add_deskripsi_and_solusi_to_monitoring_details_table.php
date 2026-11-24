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
        Schema::table('monitoring_details', function (Blueprint $table) {
            if (!Schema::hasColumn('monitoring_details', 'deskripsi_kendala')) {
                $table->text('deskripsi_kendala')->nullable()->after('alat_rusak_id');
            }
            if (!Schema::hasColumn('monitoring_details', 'solusi')) {
                $table->text('solusi')->nullable()->after('deskripsi_kendala');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('monitoring_details', function (Blueprint $table) {
            $table->dropColumn(['deskripsi_kendala', 'solusi']);
        });
    }
};
