<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Predictions table
        Schema::table('predictions', function (Blueprint $table) {
            if (!Schema::hasColumn('predictions', 'slug')) {
                $table->string('slug')->nullable()->after('title');
            }
            if (!Schema::hasColumn('predictions', 'poster_image')) {
                $table->string('poster_image')->nullable()->after('slug');
            }
            if (!Schema::hasColumn('predictions', 'meta_description')) {
                $table->text('meta_description')->nullable()->after('summary');
            }
            if (!Schema::hasColumn('predictions', 'keywords')) {
                $table->string('keywords')->nullable()->after('meta_description');
            }
            if (!Schema::hasColumn('predictions', 'full_content')) {
                $table->longText('full_content')->nullable()->after('keywords');
            }
            if (!Schema::hasColumn('predictions', 'display_order')) {
                $table->integer('display_order')->default(1)->after('full_content');
            }
            if (!Schema::hasColumn('predictions', 'is_enabled')) {
                $table->boolean('is_enabled')->default(true)->after('display_order');
            }
        });

        // 2. Fantasy Tips table
        Schema::table('fantasy_tips', function (Blueprint $table) {
            if (!Schema::hasColumn('fantasy_tips', 'slug')) {
                $table->string('slug')->nullable()->after('title');
            }
            if (!Schema::hasColumn('fantasy_tips', 'poster_image')) {
                $table->string('poster_image')->nullable()->after('slug');
            }
            if (!Schema::hasColumn('fantasy_tips', 'meta_description')) {
                $table->text('meta_description')->nullable()->after('summary');
            }
            if (!Schema::hasColumn('fantasy_tips', 'keywords')) {
                $table->string('keywords')->nullable()->after('meta_description');
            }
            if (!Schema::hasColumn('fantasy_tips', 'full_content')) {
                $table->longText('full_content')->nullable()->after('keywords');
            }
            if (!Schema::hasColumn('fantasy_tips', 'display_order')) {
                $table->integer('display_order')->default(1)->after('full_content');
            }
            if (!Schema::hasColumn('fantasy_tips', 'is_enabled')) {
                $table->boolean('is_enabled')->default(true)->after('display_order');
            }
        });

        // 3. Glossary Terms table
        Schema::table('glossary_terms', function (Blueprint $table) {
            if (!Schema::hasColumn('glossary_terms', 'slug')) {
                $table->string('slug')->nullable()->after('term');
            }
            if (!Schema::hasColumn('glossary_terms', 'poster_image')) {
                $table->string('poster_image')->nullable()->after('slug');
            }
            if (!Schema::hasColumn('glossary_terms', 'keywords')) {
                $table->string('keywords')->nullable()->after('definition');
            }
            if (!Schema::hasColumn('glossary_terms', 'display_order')) {
                $table->integer('display_order')->default(1)->after('keywords');
            }
        });

        // 4. Teams table
        Schema::table('teams', function (Blueprint $table) {
            if (!Schema::hasColumn('teams', 'slug')) {
                $table->string('slug')->nullable()->after('name');
            }
            if (!Schema::hasColumn('teams', 'display_order')) {
                $table->integer('display_order')->default(1)->after('team_type');
            }
            if (!Schema::hasColumn('teams', 'keywords')) {
                $table->string('keywords')->nullable()->after('display_order');
            }
            if (!Schema::hasColumn('teams', 'description')) {
                $table->text('description')->nullable()->after('keywords');
            }
        });

        // 5. Players table
        Schema::table('players', function (Blueprint $table) {
            if (!Schema::hasColumn('players', 'slug')) {
                $table->string('slug')->nullable()->after('name');
            }
            if (!Schema::hasColumn('players', 'display_order')) {
                $table->integer('display_order')->default(1)->after('is_popular');
            }
            if (!Schema::hasColumn('players', 'keywords')) {
                $table->string('keywords')->nullable()->after('display_order');
            }
            if (!Schema::hasColumn('players', 'description')) {
                $table->text('description')->nullable()->after('keywords');
            }
        });

        // 6. Venues table
        Schema::table('venues', function (Blueprint $table) {
            if (!Schema::hasColumn('venues', 'slug')) {
                $table->string('slug')->nullable()->after('name');
            }
            if (!Schema::hasColumn('venues', 'display_order')) {
                $table->integer('display_order')->default(1)->after('image_url');
            }
            if (!Schema::hasColumn('venues', 'keywords')) {
                $table->string('keywords')->nullable()->after('display_order');
            }
            if (!Schema::hasColumn('venues', 'description')) {
                $table->text('description')->nullable()->after('keywords');
            }
        });

        // 7. Articles table
        Schema::table('articles', function (Blueprint $table) {
            if (!Schema::hasColumn('articles', 'display_order')) {
                $table->integer('display_order')->default(1)->after('read_time');
            }
            if (!Schema::hasColumn('articles', 'is_enabled')) {
                $table->boolean('is_enabled')->default(true)->after('display_order');
            }
        });

        // 8. News table
        Schema::table('news', function (Blueprint $table) {
            if (!Schema::hasColumn('news', 'display_order')) {
                $table->integer('display_order')->default(1)->after('read_time');
            }
            if (!Schema::hasColumn('news', 'is_enabled')) {
                $table->boolean('is_enabled')->default(true)->after('display_order');
            }
        });
    }

    public function down(): void
    {
    }
};
