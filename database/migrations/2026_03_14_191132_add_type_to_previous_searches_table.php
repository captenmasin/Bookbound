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
        if (! Schema::hasColumn('previous_searches', 'type')) {
            Schema::table('previous_searches', function (Blueprint $table) {
                $table->string('type')->default('query')->after('search_term');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('previous_searches', 'type')) {
            Schema::table('previous_searches', function (Blueprint $table) {
                $table->dropColumn('type');
            });
        }
    }
};
