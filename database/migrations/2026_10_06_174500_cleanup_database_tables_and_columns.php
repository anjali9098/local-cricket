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
        // 1. Drop unused queue tables
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('failed_jobs');

        // 2. Drop display_order from all application tables
        $tablesWithDisplayOrder = [
            'players', 'tournaments', 'teams', 'venues', 'articles',
            'news', 'predictions', 'fantasy_tips', 'web_stories',
            'glossary_terms', 'icc_player_rankings', 'player_rankings', 'team_rankings'
        ];

        foreach ($tablesWithDisplayOrder as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'display_order')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->dropColumn('display_order');
                });
            }
        }

        // 3. Drop Hindi columns, odr, and sport from series table
        if (Schema::hasTable('series')) {
            Schema::table('series', function (Blueprint $t) {
                if (Schema::hasColumn('series', 'odr')) {
                    $t->dropColumn('odr');
                }
                if (Schema::hasColumn('series', 'name_hi')) {
                    $t->dropColumn('name_hi');
                }
                if (Schema::hasColumn('series', 'short_name_hi')) {
                    $t->dropColumn('short_name_hi');
                }
                if (Schema::hasColumn('series', 'detail_hi')) {
                    $t->dropColumn('detail_hi');
                }
                if (Schema::hasColumn('series', 'sport')) {
                    $t->dropColumn('sport');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op for cleanup
    }
};
