<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Drop extra squad tables to keep database clean as requested
        Schema::dropIfExists('squad_players');
        Schema::dropIfExists('series_squads');

        // 2. Add series_id, format_id, captain_id, and format_squads directly to players table
        Schema::table('players', function (Blueprint $table) {
            if (!Schema::hasColumn('players', 'series_id')) {
                $table->unsignedBigInteger('series_id')->nullable()->after('team_id');
            }
            if (!Schema::hasColumn('players', 'format_id')) {
                $table->string('format_id', 50)->nullable()->after('series_id'); // e.g. ODI, T20, TEST, IPL
            }
            if (!Schema::hasColumn('players', 'captain_id')) {
                $table->unsignedBigInteger('captain_id')->nullable()->after('format_id');
            }
            if (!Schema::hasColumn('players', 'format_squads')) {
                $table->longText('format_squads')->nullable()->after('captain_id'); // JSON mapping: series_id-format_id-captain_id
            }
        });
    }

    public function down(): void
    {
        Schema::table('players', function (Blueprint $table) {
            if (Schema::hasColumn('players', 'format_squads')) {
                $table->dropColumn('format_squads');
            }
            if (Schema::hasColumn('players', 'captain_id')) {
                $table->dropColumn('captain_id');
            }
            if (Schema::hasColumn('players', 'format_id')) {
                $table->dropColumn('format_id');
            }
            if (Schema::hasColumn('players', 'series_id')) {
                $table->dropColumn('series_id');
            }
        });
    }
};
