<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add role to users if not exists
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'role')) {
                    $table->string('role')->default('user');
                }
            });
        }

        // 2. Tournaments / Series
        Schema::create('tournaments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('name');
            $table->string('short_name')->nullable();
            $table->string('format')->default('T20');
            $table->string('series_type')->default('LOCAL');
            $table->string('category')->default('international');
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('venue')->nullable();
            $table->integer('overs')->default(20);
            $table->string('type')->default('Knockout');
            $table->string('banner_url')->nullable();
            $table->string('year')->default('2026');
            $table->text('description')->nullable();
            $table->string('logo')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('status')->default('draft'); // ongoing, upcoming, completed, draft, published
            $table->integer('views_count')->default(0);
            $table->string('reward_tier')->default('ROOKIE');
            $table->timestamps();
        });

        // 3. Teams
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tournament_id')->nullable();
            $table->string('name');
            $table->string('short_name');
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->string('color_code')->default('#22c55e');
            $table->string('logo')->nullable();
            $table->string('team_type')->default('international');
            $table->timestamps();
        });

        // 4. Players
        Schema::create('players', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('team_id')->nullable();
            $table->string('name');
            $table->string('short_name')->nullable();
            $table->string('initials')->default('IND');
            $table->string('role')->default('All-Rounder');
            $table->string('batting_style')->nullable();
            $table->string('bowling_style')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('birthday_text')->nullable();
            $table->string('days_left')->default('8D');
            $table->string('nationality')->default('India');
            $table->string('profile_image')->nullable();
            $table->boolean('is_popular')->default(false);
            $table->timestamps();
        });

        // 5. Venues
        Schema::create('venues', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->integer('capacity')->nullable();
            $table->timestamps();
        });

        // 6. Matches
        Schema::create('matches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tournament_id')->nullable();
            $table->unsignedBigInteger('team1_id');
            $table->unsignedBigInteger('team2_id');
            $table->unsignedBigInteger('venue_id')->nullable();
            $table->string('match_type')->default('T20');
            $table->string('level_type')->default('INTERNATIONAL');
            $table->string('status')->default('scheduled'); // live, scheduled, completed
            $table->dateTime('match_date')->nullable();
            $table->integer('team1_score')->default(0);
            $table->integer('team1_wickets')->default(0);
            $table->decimal('team1_overs', 4, 1)->default(0.0);
            $table->integer('team2_score')->default(0);
            $table->integer('team2_wickets')->default(0);
            $table->decimal('team2_overs', 4, 1)->default(0.0);
            $table->integer('current_innings')->default(1);
            $table->string('result_text')->nullable();
            $table->string('custom_note')->default('Match in progress');
            $table->timestamps();
        });

        // 7. Predictions
        Schema::create('predictions', function (Blueprint $table) {
            $table->id();
            $table->string('match_title')->nullable();
            $table->string('tag')->default('PREDICTION');
            $table->string('title');
            $table->text('summary')->nullable();
            $table->timestamps();
        });

        // 8. Fantasy Tips
        Schema::create('fantasy_tips', function (Blueprint $table) {
            $table->id();
            $table->string('tag')->default('FANTASY');
            $table->string('title');
            $table->text('summary')->nullable();
            $table->timestamps();
        });

        // 9. Articles
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('category')->default('INTERNATIONAL');
            $table->string('title');
            $table->text('summary')->nullable();
            $table->text('content')->nullable();
            $table->string('image_url')->nullable();
            $table->string('read_time')->default('4 MIN READ');
            $table->string('published_date')->default('AUG 14');
            $table->timestamps();
        });

        // 10. News
        Schema::create('news', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('category')->default('CRICKET');
            $table->string('title');
            $table->text('summary')->nullable();
            $table->text('content')->nullable();
            $table->string('image_url')->nullable();
            $table->timestamps();
        });

        // 11. Team Rankings
        Schema::create('team_rankings', function (Blueprint $table) {
            $table->id();
            $table->integer('rank_num');
            $table->string('team_name');
            $table->integer('matches_played')->default(0);
            $table->integer('won')->default(0);
            $table->string('nrr')->default('0.00');
            $table->integer('points')->default(0);
            $table->string('category')->default('ALL');
            $table->timestamps();
        });

        // 12. Player Rankings
        Schema::create('player_rankings', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // batting, bowling
            $table->integer('rank_num');
            $table->string('badge_text')->default('IND');
            $table->string('player_name');
            $table->integer('stat_value');
            $table->timestamps();
        });

        // 13. Points Table
        Schema::create('points_table', function (Blueprint $table) {
            $table->id();
            $table->string('tournament_name')->default('ICC World Cup 2026');
            $table->string('team_code');
            $table->integer('played')->default(0);
            $table->integer('won')->default(0);
            $table->integer('points')->default(0);
            $table->timestamps();
        });

        // 14. Player Birthdays
        Schema::create('player_birthdays', function (Blueprint $table) {
            $table->id();
            $table->string('badge_text')->default('KAG');
            $table->string('player_name');
            $table->string('birthday_text');
            $table->string('days_left');
            $table->timestamps();
        });

        // 15. Web Stories
        Schema::create('web_stories', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('image_url')->nullable();
            $table->string('tag')->default('STORY');
            $table->timestamps();
        });

        // 16. Glossary Terms
        Schema::create('glossary_terms', function (Blueprint $table) {
            $table->id();
            $table->string('letter');
            $table->string('term');
            $table->text('definition');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('glossary_terms');
        Schema::dropIfExists('web_stories');
        Schema::dropIfExists('player_birthdays');
        Schema::dropIfExists('points_table');
        Schema::dropIfExists('player_rankings');
        Schema::dropIfExists('team_rankings');
        Schema::dropIfExists('news');
        Schema::dropIfExists('articles');
        Schema::dropIfExists('fantasy_tips');
        Schema::dropIfExists('predictions');
        Schema::dropIfExists('matches');
        Schema::dropIfExists('venues');
        Schema::dropIfExists('players');
        Schema::dropIfExists('teams');
        Schema::dropIfExists('tournaments');
    }
};
