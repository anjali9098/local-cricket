<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Web Stories table
        Schema::table('web_stories', function (Blueprint $table) {
            if (!Schema::hasColumn('web_stories', 'slug')) {
                $table->string('slug')->nullable()->after('title');
            }
            if (!Schema::hasColumn('web_stories', 'display_order')) {
                $table->integer('display_order')->default(1)->after('tag');
            }
            if (!Schema::hasColumn('web_stories', 'keywords')) {
                $table->string('keywords')->nullable()->after('display_order');
            }
            if (!Schema::hasColumn('web_stories', 'is_enabled')) {
                $table->boolean('is_enabled')->default(true)->after('keywords');
            }
        });

        // 2. Team Rankings table
        Schema::table('team_rankings', function (Blueprint $table) {
            if (!Schema::hasColumn('team_rankings', 'slug')) {
                $table->string('slug')->nullable()->after('team_name');
            }
            if (!Schema::hasColumn('team_rankings', 'logo_url')) {
                $table->string('logo_url')->nullable()->after('slug');
            }
            if (!Schema::hasColumn('team_rankings', 'display_order')) {
                $table->integer('display_order')->default(1)->after('points');
            }
            if (!Schema::hasColumn('team_rankings', 'keywords')) {
                $table->string('keywords')->nullable()->after('display_order');
            }
        });

        // 3. Player Rankings table
        Schema::table('player_rankings', function (Blueprint $table) {
            if (!Schema::hasColumn('player_rankings', 'slug')) {
                $table->string('slug')->nullable()->after('player_name');
            }
            if (!Schema::hasColumn('player_rankings', 'photo_url')) {
                $table->string('photo_url')->nullable()->after('slug');
            }
            if (!Schema::hasColumn('player_rankings', 'display_order')) {
                $table->integer('display_order')->default(1)->after('stat_value');
            }
            if (!Schema::hasColumn('player_rankings', 'keywords')) {
                $table->string('keywords')->nullable()->after('display_order');
            }
        });
    }

    public function down(): void
    {
    }
};
