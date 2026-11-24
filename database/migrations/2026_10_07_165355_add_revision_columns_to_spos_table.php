<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('spos', function (Blueprint $table) {
            $table->unsignedBigInteger('parent_spo_id')->nullable()->after('id');
            $table->integer('versi')->default(1)->after('parent_spo_id');
        });
    }

    public function down()
    {
        Schema::table('spos', function (Blueprint $table) {
            $table->dropColumn(['parent_spo_id', 'versi']);
        });
    }
};