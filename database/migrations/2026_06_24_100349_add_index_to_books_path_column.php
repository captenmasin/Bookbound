<?php

use Illuminate\Support\Facades\DB;
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
        if ($this->booksPathIndexExists()) {
            return;
        }

        Schema::table('books', function (Blueprint $table) {
            $table->index('path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! $this->booksPathIndexExists()) {
            return;
        }

        Schema::table('books', function (Blueprint $table) {
            $table->dropIndex(['path']);
        });
    }

    private function booksPathIndexExists(): bool
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            return DB::selectOne(
                "SELECT 1 FROM pg_indexes WHERE schemaname = 'public' AND tablename = 'books' AND indexname = 'books_path_index'"
            ) !== null;
        }

        if ($driver === 'sqlite') {
            foreach (DB::select("PRAGMA index_list('books')") as $index) {
                foreach (DB::select("PRAGMA index_info('{$index->name}')") as $column) {
                    if ($column->name === 'path') {
                        return true;
                    }
                }
            }

            return false;
        }

        return false;
    }
};
