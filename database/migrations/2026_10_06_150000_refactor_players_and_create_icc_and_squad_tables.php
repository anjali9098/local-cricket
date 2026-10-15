<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Clean and refactor 'players' table
        Schema::table('players', function (Blueprint $table) {
            // Add meta_description if not exists
            if (!Schema::hasColumn('players', 'meta_description')) {
                $table->text('meta_description')->nullable()->after('display_order');
            }
        });

        // Copy existing description into meta_description if description exists
        if (Schema::hasColumn('players', 'description') && Schema::hasColumn('players', 'meta_description')) {
            DB::statement("UPDATE players SET meta_description = description WHERE (meta_description IS NULL OR meta_description = '') AND description IS NOT NULL");
        }

        // Drop redundant/deprecated columns from players table
        Schema::table('players', function (Blueprint $table) {
            if (Schema::hasColumn('players', 'description')) {
                $table->dropColumn('description');
            }
            if (Schema::hasColumn('players', 'initials')) {
                $table->dropColumn('initials');
            }
            if (Schema::hasColumn('players', 'local_name')) {
                $table->dropColumn('local_name');
            }
            if (Schema::hasColumn('players', 'icc_rankings')) {
                $table->dropColumn('icc_rankings');
            }
        });

        // 2. Create dedicated 'icc_player_rankings' table
        if (!Schema::hasTable('icc_player_rankings')) {
            Schema::create('icc_player_rankings', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('player_id')->nullable()->index();
                $table->string('player_name')->index();
                $table->string('country')->nullable()->default('IND');
                $table->enum('category', ['batting', 'bowling', 'all_rounder'])->default('batting')->index();
                $table->string('format', 20)->default('ODI')->index(); // TEST, ODI, T20I
                $table->string('current_rank', 20)->nullable()->default('--');
                $table->string('best_rank', 20)->nullable()->default('--');
                $table->integer('rating')->nullable()->default(0);
                $table->integer('display_order')->default(1);
                $table->timestamps();

                // Unique per player, category, format
                $table->unique(['player_id', 'category', 'format'], 'unique_player_cat_fmt');
            });
        }

        // 3. Create 'series_squads' table (Series + Team + Format + Captain unique entity)
        if (!Schema::hasTable('series_squads')) {
            Schema::create('series_squads', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('series_id')->index(); // Foreign key to tournaments.id
                $table->unsignedBigInteger('team_id')->index();   // Foreign key to teams.id
                $table->string('format', 30)->default('T20')->index(); // TEST, ODI, T20, T10, IPL, 100-BALL
                $table->unsignedBigInteger('captain_id')->nullable()->index(); // Foreign key to players.id
                $table->unsignedBigInteger('vice_captain_id')->nullable()->index(); // Foreign key to players.id
                $table->unsignedBigInteger('wicket_keeper_id')->nullable()->index(); // Foreign key to players.id
                $table->string('squad_name')->nullable(); // e.g. "India ODI Squad 2026"
                $table->text('notes')->nullable();
                $table->timestamps();

                // Unique per series, team, format
                $table->unique(['series_id', 'team_id', 'format'], 'unique_series_team_format');
            });
        }

        // 4. Create 'squad_players' table (Mapping players into the series_squad)
        if (!Schema::hasTable('squad_players')) {
            Schema::create('squad_players', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('series_squad_id')->index();
                $table->unsignedBigInteger('player_id')->index();
                $table->string('role_in_squad', 50)->nullable()->default('Player'); // Batsman, Bowler, All-Rounder, Wicketkeeper
                $table->boolean('is_captain')->default(false);
                $table->boolean('is_vice_captain')->default(false);
                $table->boolean('is_wicket_keeper')->default(false);
                $table->string('jersey_number', 20)->nullable();
                $table->timestamps();

                // Unique constraint: player can only be added once per series_squad
                $table->unique(['series_squad_id', 'player_id'], 'unique_squad_player');

                $table->foreign('series_squad_id')->references('id')->on('series_squads')->onDelete('cascade');
                $table->foreign('player_id')->references('id')->on('players')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('squad_players');
        Schema::dropIfExists('series_squads');
        Schema::dropIfExists('icc_player_rankings');

        Schema::table('players', function (Blueprint $table) {
            if (!Schema::hasColumn('players', 'initials')) {
                $table->string('initials')->default('IND');
            }
            if (!Schema::hasColumn('players', 'local_name')) {
                $table->string('local_name')->nullable();
            }
            if (!Schema::hasColumn('players', 'description')) {
                $table->text('description')->nullable();
            }
            if (!Schema::hasColumn('players', 'icc_rankings')) {
                $table->longText('icc_rankings')->nullable();
            }
            if (Schema::hasColumn('players', 'meta_description')) {
                $table->dropColumn('meta_description');
            }
        });
    }
};
