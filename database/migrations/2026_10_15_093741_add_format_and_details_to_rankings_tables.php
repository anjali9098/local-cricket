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
        Schema::table('team_rankings', function (Blueprint $table) {
            if (!Schema::hasColumn('team_rankings', 'format')) {
                $table->string('format', 20)->default('TEST')->after('category')->index();
            }
            if (!Schema::hasColumn('team_rankings', 'rating')) {
                $table->integer('rating')->default(0)->after('points');
            }
            if (!Schema::hasColumn('team_rankings', 'gender')) {
                $table->string('gender', 10)->default('men')->after('format')->index();
            }
        });

        Schema::table('icc_player_rankings', function (Blueprint $table) {
            if (!Schema::hasColumn('icc_player_rankings', 'photo_url')) {
                $table->longText('photo_url')->nullable()->after('player_name');
            }
            if (!Schema::hasColumn('icc_player_rankings', 'country_flag')) {
                $table->string('country_flag', 255)->nullable()->after('country');
            }
            if (!Schema::hasColumn('icc_player_rankings', 'gender')) {
                $table->string('gender', 10)->default('men')->after('format')->index();
            }
            if (!Schema::hasColumn('icc_player_rankings', 'matches')) {
                $table->integer('matches')->default(0)->after('rating');
            }
            if (!Schema::hasColumn('icc_player_rankings', 'points')) {
                $table->integer('points')->default(0)->after('matches');
            }
            if (!Schema::hasColumn('icc_player_rankings', 'slug')) {
                $table->string('slug', 255)->nullable()->after('player_name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('team_rankings', function (Blueprint $table) {
            $table->dropColumn(['format', 'rating', 'gender']);
        });

        Schema::table('icc_player_rankings', function (Blueprint $table) {
            $table->dropColumn(['photo_url', 'country_flag', 'gender', 'matches', 'points', 'slug']);
        });
    }
};
