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
        Schema::table('matches', function (Blueprint $table) {
            $table->text('custom_note')->nullable()->change();
            $table->text('result_text')->nullable()->change();
            $table->text('level_type')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('matches', function (Blueprint $table) {
            $table->string('custom_note')->nullable()->change();
            $table->string('result_text')->nullable()->change();
            $table->string('level_type')->nullable()->change();
        });
    }
};
