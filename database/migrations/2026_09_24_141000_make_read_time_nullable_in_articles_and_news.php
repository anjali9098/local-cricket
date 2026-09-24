<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->string('read_time')->nullable()->default('4 MIN READ')->change();
        });

        Schema::table('news', function (Blueprint $table) {
            $table->string('read_time')->nullable()->default('3 MIN READ')->change();
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->string('read_time')->default('4 MIN READ')->change();
        });

        Schema::table('news', function (Blueprint $table) {
            $table->string('read_time')->default('3 MIN READ')->change();
        });
    }
};
