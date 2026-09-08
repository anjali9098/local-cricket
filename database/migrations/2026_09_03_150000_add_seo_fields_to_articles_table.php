<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            if (!Schema::hasColumn('articles', 'slug')) {
                $table->string('slug')->nullable()->after('title');
            }
            if (!Schema::hasColumn('articles', 'meta_description')) {
                $table->text('meta_description')->nullable()->after('slug');
            }
            if (!Schema::hasColumn('articles', 'keywords')) {
                $table->string('keywords')->nullable()->after('meta_description');
            }
            if (!Schema::hasColumn('articles', 'h1_heading')) {
                $table->string('h1_heading')->nullable()->after('keywords');
            }
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn(['slug', 'meta_description', 'keywords', 'h1_heading']);
        });
    }
};
