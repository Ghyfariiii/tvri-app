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
        Schema::table('monitoring_harians', function (Blueprint $table) {
            $table->enum('status_revisi', ['none', 'pending', 'approved', 'rejected'])
                ->default('none');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('monitoring_harians', function (Blueprint $table) {
            $table->dropColumn('status_revisi');
        });
    }
};
