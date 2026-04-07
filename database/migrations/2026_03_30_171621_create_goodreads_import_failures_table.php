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
        Schema::create('goodreads_import_failures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goodreads_import_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('row_number');
            $table->string('title')->nullable();
            $table->string('author')->nullable();
            $table->string('reason');
            $table->json('raw_row')->nullable();
            $table->timestamps();

            $table->index(['goodreads_import_id', 'row_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('goodreads_import_failures');
    }
};
