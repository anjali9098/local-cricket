<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $hasIndex = function($table, $indexName) {
            $indexes = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);
            return !empty($indexes);
        };

        if (Schema::hasTable('matches')) {
            Schema::table('matches', function (Blueprint $table) use ($hasIndex) {
                if (!$hasIndex('matches', 'idx_matches_approved_id')) {
                    $table->index(['is_approved', 'id'], 'idx_matches_approved_id');
                }
                if (!$hasIndex('matches', 'idx_matches_tournament_id')) {
                    $table->index('tournament_id', 'idx_matches_tournament_id');
                }
                if (!$hasIndex('matches', 'idx_matches_team1_id')) {
                    $table->index('team1_id', 'idx_matches_team1_id');
                }
                if (!$hasIndex('matches', 'idx_matches_team2_id')) {
                    $table->index('team2_id', 'idx_matches_team2_id');
                }
                if (!$hasIndex('matches', 'idx_matches_status')) {
                    $table->index('status', 'idx_matches_status');
                }
            });
        }

        if (Schema::hasTable('tournaments')) {
            Schema::table('tournaments', function (Blueprint $table) use ($hasIndex) {
                if (!$hasIndex('tournaments', 'idx_tournaments_approved_enabled')) {
                    $table->index(['is_approved', 'is_enabled'], 'idx_tournaments_approved_enabled');
                }
            });
        }

        if (Schema::hasTable('player_batting_stats')) {
            Schema::table('player_batting_stats', function (Blueprint $table) use ($hasIndex) {
                if (!$hasIndex('player_batting_stats', 'idx_batting_player_name')) {
                    $table->index('player_name', 'idx_batting_player_name');
                }
            });
        }

        if (Schema::hasTable('player_bowling_stats')) {
            Schema::table('player_bowling_stats', function (Blueprint $table) use ($hasIndex) {
                if (!$hasIndex('player_bowling_stats', 'idx_bowling_player_name')) {
                    $table->index('player_name', 'idx_bowling_player_name');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Safe down
    }
};
