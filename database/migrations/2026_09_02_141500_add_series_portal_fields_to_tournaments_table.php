<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tournaments', function (Blueprint $table) {
            if (!Schema::hasColumn('tournaments', 'slug')) {
                $table->string('slug')->nullable()->after('name');
            }
            if (!Schema::hasColumn('tournaments', 'display_order')) {
                $table->integer('display_order')->default(1)->after('year');
            }
            if (!Schema::hasColumn('tournaments', 'meta_description')) {
                $table->text('meta_description')->nullable();
            }
            if (!Schema::hasColumn('tournaments', 'match_formats')) {
                $table->string('match_formats')->nullable();
            }
            if (!Schema::hasColumn('tournaments', 'teams_list')) {
                $table->text('teams_list')->nullable();
            }
            if (!Schema::hasColumn('tournaments', 'venues_list')) {
                $table->text('venues_list')->nullable();
            }
            if (!Schema::hasColumn('tournaments', 'hosting_country')) {
                $table->string('hosting_country')->nullable();
            }
            if (!Schema::hasColumn('tournaments', 'total_matches')) {
                $table->integer('total_matches')->default(10);
            }
            if (!Schema::hasColumn('tournaments', 'menu_order')) {
                $table->integer('menu_order')->default(0);
            }
            if (!Schema::hasColumn('tournaments', 'full_description')) {
                $table->longText('full_description')->nullable();
            }
            if (!Schema::hasColumn('tournaments', 'dream11_id')) {
                $table->string('dream11_id')->nullable();
            }
            if (!Schema::hasColumn('tournaments', 'cricbuzz_id')) {
                $table->string('cricbuzz_id')->nullable();
            }
            if (!Schema::hasColumn('tournaments', 'espn_id')) {
                $table->string('espn_id')->nullable();
            }
            if (!Schema::hasColumn('tournaments', 'icc_id')) {
                $table->string('icc_id')->nullable();
            }
            if (!Schema::hasColumn('tournaments', 'is_enabled')) {
                $table->boolean('is_enabled')->default(true);
            }
            if (!Schema::hasColumn('tournaments', 'poster_image')) {
                $table->string('poster_image')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('tournaments', function (Blueprint $table) {
            $table->dropColumn([
                'slug', 'display_order', 'meta_description', 'match_formats', 
                'teams_list', 'venues_list', 'hosting_country', 'total_matches', 
                'menu_order', 'full_description', 'dream11_id', 'cricbuzz_id', 
                'espn_id', 'icc_id', 'is_enabled', 'poster_image'
            ]);
        });
    }
};
