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
            // Drop the 4 separate columns
            if (Schema::hasColumn('players', 'series_id')) {
                $table->dropColumn('series_id');
            }
            if (Schema::hasColumn('players', 'format_id')) {
                $table->dropColumn('format_id');
            }
            if (Schema::hasColumn('players', 'captain_id')) {
                $table->dropColumn('captain_id');
            }
            if (Schema::hasColumn('players', 'format_squads')) {
                $table->dropColumn('format_squads');
            }
            
            // Add a single column named seriesid_captionid_formateid after team_id
            if (!Schema::hasColumn('players', 'seriesid_captionid_formateid')) {
                $table->longText('seriesid_captionid_formateid')->nullable()->after('team_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('players', function (Blueprint $table) {
            if (Schema::hasColumn('players', 'seriesid_captionid_formateid')) {
                $table->dropColumn('seriesid_captionid_formateid');
            }
            $table->unsignedBigInteger('series_id')->nullable()->after('team_id');
            $table->string('format_id', 50)->nullable()->after('series_id');
            $table->unsignedBigInteger('captain_id')->nullable()->after('format_id');
            $table->longText('format_squads')->nullable()->after('captain_id');
        });
    }
};
