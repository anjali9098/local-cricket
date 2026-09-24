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
            if (!Schema::hasColumn('players', 'local_name')) {
                $table->string('local_name')->nullable()->after('name');
            }
            if (!Schema::hasColumn('players', 'nickname')) {
                $table->string('nickname')->nullable()->after('local_name');
            }
            if (!Schema::hasColumn('players', 'birthplace')) {
                $table->string('birthplace')->nullable()->after('date_of_birth');
            }
            if (!Schema::hasColumn('players', 'height')) {
                $table->string('height')->nullable()->after('birthplace');
            }
            if (!Schema::hasColumn('players', 'played_teams')) {
                $table->text('played_teams')->nullable()->after('bowling_style');
            }
            
            // Family details
            if (!Schema::hasColumn('players', 'father_name')) {
                $table->string('father_name')->nullable()->after('played_teams');
            }
            if (!Schema::hasColumn('players', 'mother_name')) {
                $table->string('mother_name')->nullable()->after('father_name');
            }
            if (!Schema::hasColumn('players', 'spouse_name')) {
                $table->string('spouse_name')->nullable()->after('mother_name');
            }
            if (!Schema::hasColumn('players', 'children')) {
                $table->string('children')->nullable()->after('spouse_name');
            }
            if (!Schema::hasColumn('players', 'siblings')) {
                $table->string('siblings')->nullable()->after('children');
            }
            
            // Full Biography
            if (!Schema::hasColumn('players', 'bio')) {
                $table->longText('bio')->nullable()->after('description');
            }
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
