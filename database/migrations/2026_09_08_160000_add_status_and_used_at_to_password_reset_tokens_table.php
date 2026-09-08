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
        Schema::table('password_reset_tokens', function (Blueprint $table) {
            if (!Schema::hasColumn('password_reset_tokens', 'status')) {
                $table->string('status')->default('PENDING')->after('token');
            }
            if (!Schema::hasColumn('password_reset_tokens', 'used_at')) {
                $table->timestamp('used_at')->nullable()->after('created_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('password_reset_tokens', function (Blueprint $table) {
            if (Schema::hasColumn('password_reset_tokens', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('password_reset_tokens', 'used_at')) {
                $table->dropColumn('used_at');
            }
        });
    }
};
