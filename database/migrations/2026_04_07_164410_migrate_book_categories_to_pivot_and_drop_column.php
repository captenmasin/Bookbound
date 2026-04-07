<?php

use Illuminate\Support\Str;
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
        if (! Schema::hasColumn('books', 'categories')) {
            return;
        }

        DB::table('books')
            ->select(['id', 'categories'])
            ->whereNotNull('categories')
            ->orderBy('id')
            ->chunkById(100, function ($books): void {
                foreach ($books as $book) {
                    $categories = collect(json_decode($book->categories, true))
                        ->map(fn (mixed $category): ?string => is_string($category) ? trim($category) : null)
                        ->filter()
                        ->unique()
                        ->values();

                    foreach ($categories as $categoryName) {
                        $slug = Str::slug($categoryName);
                        $existingCategoryId = DB::table('categories')
                            ->where('slug', $slug)
                            ->value('id');

                        if (! $existingCategoryId) {
                            $existingCategoryId = DB::table('categories')->insertGetId([
                                'name' => $categoryName,
                                'slug' => $slug,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }

                        DB::table('book_category')->updateOrInsert(
                            [
                                'book_id' => $book->id,
                                'category_id' => $existingCategoryId,
                            ],
                            [
                                'created_at' => now(),
                                'updated_at' => now(),
                            ],
                        );
                    }
                }
            });

        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn('categories');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('books', 'categories')) {
            Schema::table('books', function (Blueprint $table) {
                $table->json('categories')->nullable()->after('description');
            });
        }

        DB::table('books')
            ->select('books.id')
            ->orderBy('books.id')
            ->chunkById(100, function ($books): void {
                foreach ($books as $book) {
                    $categories = DB::table('book_category')
                        ->join('categories', 'categories.id', '=', 'book_category.category_id')
                        ->where('book_category.book_id', $book->id)
                        ->orderBy('categories.name')
                        ->pluck('categories.name')
                        ->values()
                        ->all();

                    DB::table('books')
                        ->where('id', $book->id)
                        ->update([
                            'categories' => empty($categories) ? null : json_encode($categories, JSON_THROW_ON_ERROR),
                        ]);
                }
            });
    }
};
