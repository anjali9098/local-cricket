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
        Schema::table('teams', function (Blueprint $table) {
            $table->longText('logo')->nullable()->change();
            $table->longText('logo_url')->nullable()->change();
        });

        Schema::table('venues', function (Blueprint $table) {
            $table->longText('image_url')->nullable()->change();
        });

        Schema::table('players', function (Blueprint $table) {
            $table->longText('profile_image')->nullable()->change();
        });

        Schema::table('news', function (Blueprint $table) {
            $table->longText('image_url')->nullable()->change();
        });

        Schema::table('predictions', function (Blueprint $table) {
            $table->longText('poster_image')->nullable()->change();
        });

        Schema::table('articles', function (Blueprint $table) {
            $table->longText('image_url')->nullable()->change();
        });

        Schema::table('web_stories', function (Blueprint $table) {
            $table->longText('image_url')->nullable()->change();
        });

        Schema::table('tournaments', function (Blueprint $table) {
            $table->longText('banner_url')->nullable()->change();
            $table->longText('logo')->nullable()->change();
            $table->longText('poster_image')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No down needed
    }
};
