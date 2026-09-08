<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('ball_by_ball')) {
            Schema::create('ball_by_ball', function (Blueprint $table) {
                $table->id();
                $table->foreignId('match_id')->constrained('matches')->onDelete('cascade');
                $table->integer('over_num')->default(0);
                $table->string('outcome');
                $table->string('bowler_name')->nullable();
                $table->string('batsman_name')->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('ball_by_ball');
    }
};
