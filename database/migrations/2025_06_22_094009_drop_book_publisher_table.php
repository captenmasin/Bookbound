<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('book_publisher');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
