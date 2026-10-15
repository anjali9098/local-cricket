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
        // Drop points_table (dynamic points are calculated directly from matches & teams)
        Schema::dropIfExists('points_table');

        // Drop player_birthdays (birthdays are managed directly via players.date_of_birth)
        Schema::dropIfExists('player_birthdays');

        // Drop legacy player_rankings (replaced by dedicated icc_player_rankings table)
        Schema::dropIfExists('player_rankings');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op rollback for dropped legacy tables
    }
};
