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
        Schema::table('news', function (Blueprint $table) {
            if (!Schema::hasColumn('news', 'slug')) {
                $table->string('slug')->nullable()->after('title');
            }
            if (!Schema::hasColumn('news', 'meta_description')) {
                $table->text('meta_description')->nullable()->after('summary');
            }
            if (!Schema::hasColumn('news', 'keywords')) {
                $table->string('keywords')->nullable()->after('meta_description');
            }
            if (!Schema::hasColumn('news', 'h1_heading')) {
                $table->string('h1_heading')->nullable()->after('title');
            }
            if (!Schema::hasColumn('news', 'read_time')) {
                $table->string('read_time')->default('3 MIN READ')->after('content');
            }
            if (!Schema::hasColumn('news', 'published_date')) {
                $table->string('published_date')->nullable()->after('read_time');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('news', function (Blueprint $table) {
            $table->dropColumn(['slug', 'meta_description', 'keywords', 'h1_heading', 'read_time', 'published_date']);
        });
    }
};
