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
        Schema::table('players', function (Blueprint $table) {
            if (!Schema::hasColumn('players', 'country')) {
                $table->string('country')->nullable()->after('nationality');
            }
            if (!Schema::hasColumn('players', 'jersey_number')) {
                $table->string('jersey_number')->nullable()->after('country');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('players', function (Blueprint $table) {
            if (Schema::hasColumn('players', 'jersey_number')) {
                $table->dropColumn('jersey_number');
            }
            if (Schema::hasColumn('players', 'country')) {
                $table->dropColumn('country');
            }
        });
    }
};
