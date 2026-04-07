<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('book_user', function (Blueprint $table) {
            if (! Schema::hasColumn('book_user', 'read_at')) {
                $table->timestamp('read_at')->nullable()->after('user_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('book_user', function (Blueprint $table) {
            if (Schema::hasColumn('book_user', 'read_at')) {
                $table->dropColumn('read_at');
            }
        });
    }
};
