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
        try {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `ball_by_ball` MODIFY `over_num` VARCHAR(30) NOT NULL DEFAULT '0.1'");
        } catch (\Throwable $e) {
            // fallback
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `ball_by_ball` MODIFY `over_num` INT(11) NOT NULL DEFAULT 0");
        } catch (\Throwable $e) {
            // fallback
        }
    }
};
