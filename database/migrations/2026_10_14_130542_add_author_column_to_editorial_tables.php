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
        if (Schema::hasTable('articles') && !Schema::hasColumn('articles', 'author')) {
            Schema::table('articles', function (Blueprint $table) {
                $table->string('author')->nullable()->after('category');
            });
        }

        if (Schema::hasTable('news') && !Schema::hasColumn('news', 'author')) {
            Schema::table('news', function (Blueprint $table) {
                $table->string('author')->nullable()->after('user_id');
            });
        }

        if (Schema::hasTable('predictions') && !Schema::hasColumn('predictions', 'author')) {
            Schema::table('predictions', function (Blueprint $table) {
                $table->string('author')->nullable()->after('tag');
            });
        }

        if (Schema::hasTable('fantasy_tips') && !Schema::hasColumn('fantasy_tips', 'author')) {
            Schema::table('fantasy_tips', function (Blueprint $table) {
                $table->string('author')->nullable()->after('tag');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('articles') && Schema::hasColumn('articles', 'author')) {
            Schema::table('articles', function (Blueprint $table) {
                $table->dropColumn('author');
            });
        }

        if (Schema::hasTable('news') && Schema::hasColumn('news', 'author')) {
            Schema::table('news', function (Blueprint $table) {
                $table->dropColumn('author');
            });
        }

        if (Schema::hasTable('predictions') && Schema::hasColumn('predictions', 'author')) {
            Schema::table('predictions', function (Blueprint $table) {
                $table->dropColumn('author');
            });
        }

        if (Schema::hasTable('fantasy_tips') && Schema::hasColumn('fantasy_tips', 'author')) {
            Schema::table('fantasy_tips', function (Blueprint $table) {
                $table->dropColumn('author');
            });
        }
    }
};
