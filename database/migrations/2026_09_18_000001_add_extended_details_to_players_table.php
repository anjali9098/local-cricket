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
            $table->string('local_name')->nullable()->after('name');
            $table->string('nickname')->nullable()->after('local_name');
            $table->string('birthplace')->nullable()->after('date_of_birth');
            $table->string('height')->nullable()->after('birthplace');
            $table->text('played_teams')->nullable()->after('bowling_style');
            
            // Family details
            $table->string('father_name')->nullable()->after('played_teams');
            $table->string('mother_name')->nullable()->after('father_name');
            $table->string('spouse_name')->nullable()->after('mother_name');
            $table->string('children')->nullable()->after('spouse_name');
            $table->string('siblings')->nullable()->after('children');
            
            // Full Biography
            $table->longText('bio')->nullable()->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('players', function (Blueprint $table) {
            $table->dropColumn([
                'local_name',
                'nickname',
                'birthplace',
                'height',
                'played_teams',
                'father_name',
                'mother_name',
                'spouse_name',
                'children',
                'siblings',
                'bio',
            ]);
        });
    }
};
