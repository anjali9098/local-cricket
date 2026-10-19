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
        Schema::create('head_to_heads', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('team1_id')->nullable()->index();
            $table->unsignedBigInteger('team2_id')->nullable()->index();
            $table->string('team1_name')->index();
            $table->string('team2_name')->index();
            $table->string('format', 20)->default('TEST')->index(); // TEST, ODI, T20, ALL, IPL, etc.
            
            // Match aggregates
            $table->integer('total_matches')->default(0);
            $table->integer('team1_won')->default(0);
            $table->integer('team2_won')->default(0);
            $table->integer('drawn')->default(0);
            $table->integer('tied')->default(0);
            $table->integer('no_result')->default(0);

            // Venue breakdown
            $table->integer('team1_home_won')->default(0);
            $table->integer('team2_home_won')->default(0);
            $table->integer('team1_away_won')->default(0);
            $table->integer('team2_away_won')->default(0);
            $table->integer('team1_neutral_won')->default(0);
            $table->integer('team2_neutral_won')->default(0);

            // Score highlights
            $table->integer('team1_highest_score')->default(0);
            $table->integer('team2_highest_score')->default(0);
            $table->integer('team1_lowest_score')->default(0);
            $table->integer('team2_lowest_score')->default(0);

            // Narrative summary description
            $table->text('summary_narrative')->nullable();

            $table->string('source', 50)->default('manual'); // 'manual', 'mykhel', etc.
            $table->timestamps();

            // Indexes for fast lookup
            $table->index(['team1_id', 'team2_id', 'format'], 'h2h_teams_format_idx');
            $table->index(['team1_name', 'team2_name', 'format'], 'h2h_team_names_format_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('head_to_heads');
    }
};
