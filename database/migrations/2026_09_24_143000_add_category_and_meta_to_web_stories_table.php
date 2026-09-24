<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('web_stories', function (Blueprint $table) {
            if (!Schema::hasColumn('web_stories', 'category')) {
                $table->string('category')->default('Cricket')->nullable()->after('title');
            }
            if (!Schema::hasColumn('web_stories', 'meta_description')) {
                $table->text('meta_description')->nullable()->after('slug');
            }
        });
    }

    public function down(): void
    {
        Schema::table('web_stories', function (Blueprint $table) {
            if (Schema::hasColumn('web_stories', 'category')) {
                $table->dropColumn('category');
            }
            if (!Schema::hasColumn('web_stories', 'meta_description')) {
                $table->dropColumn('meta_description');
            }
        });
    }
};
