<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('player_batting_stats')) {
            Schema::create('player_batting_stats', function (Blueprint $table) {
                $table->id();
                $table->foreignId('match_id')->constrained('matches')->onDelete('cascade');
                $table->string('player_name');
                $table->integer('runs')->default(0);
                $table->integer('balls')->default(0);
                $table->integer('fours')->default(0);
                $table->integer('sixes')->default(0);
                $table->decimal('strike_rate', 5, 2)->default(0.00);
                $table->string('status_text')->default('not out');
                $table->timestamp('created_at')->useCurrent();
            });
        }

        if (!Schema::hasTable('player_bowling_stats')) {
            Schema::create('player_bowling_stats', function (Blueprint $table) {
                $table->id();
                $table->foreignId('match_id')->constrained('matches')->onDelete('cascade');
                $table->string('player_name');
                $table->decimal('overs', 3, 1)->default(0.0);
                $table->integer('runs')->default(0);
                $table->integer('wickets')->default(0);
                $table->decimal('economy', 4, 2)->default(0.00);
                $table->timestamp('created_at')->useCurrent();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('player_batting_stats');
        Schema::dropIfExists('player_bowling_stats');
    }
};
