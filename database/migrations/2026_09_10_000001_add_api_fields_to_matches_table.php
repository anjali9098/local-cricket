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
        Schema::table('matches', function (Blueprint $table) {
            if (!Schema::hasColumn('matches', 'api_match_id')) {
                $table->string('api_match_id', 191)->nullable()->index()->after('id');
            }
            if (!Schema::hasColumn('matches', 'is_approved')) {
                $table->boolean('is_approved')->default(true)->after('status');
            }
            if (!Schema::hasColumn('matches', 'is_api_match')) {
                $table->boolean('is_api_match')->default(false)->after('is_approved');
            }
            if (!Schema::hasColumn('matches', 'api_raw_data')) {
                $table->longText('api_raw_data')->nullable()->after('custom_note');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('matches', function (Blueprint $table) {
            if (Schema::hasColumn('matches', 'api_match_id')) {
                $table->dropColumn('api_match_id');
            }
            if (Schema::hasColumn('matches', 'is_approved')) {
                $table->dropColumn('is_approved');
            }
            if (Schema::hasColumn('matches', 'is_api_match')) {
                $table->dropColumn('is_api_match');
            }
            if (Schema::hasColumn('matches', 'api_raw_data')) {
                $table->dropColumn('api_raw_data');
            }
        });
    }
};
