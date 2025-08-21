<?php

use App\Models\Book;
use App\Models\User;
use App\Models\Cover;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Queue;
use App\Actions\Books\ImportBookCover;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

beforeEach(function () {
    Storage::fake('public');
});

describe('Book Cover Model Relationships', function () {
    it('can create a primary cover when none exists', function () {
        $book = Book::factory()->create();

        $primaryCover = $book->primaryCover();

        expect($primaryCover)->toBeInstanceOf(Cover::class);
        expect($primaryCover->is_primary)->toBeTrue();
        expect($book->covers)->toHaveCount(1);
    });

    it('returns existing primary cover when it exists', function () {
        $book = Book::factory()->create();
        $existingCover = $book->covers()->create(['is_primary' => true]);

        $primaryCover = $book->primaryCover();

        expect($primaryCover->id)->toBe($existingCover->id);
        expect($book->covers)->toHaveCount(1);
    });

    it('can have multiple covers with one primary', function () {
        $book = Book::factory()->create();
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $primaryCover = $book->covers()->create(['is_primary' => true]);
        $userCover1 = $book->covers()->create(['is_primary' => false, 'user_id' => $user1->id]);
        $userCover2 = $book->covers()->create(['is_primary' => false, 'user_id' => $user2->id]);

        expect($book->covers)->toHaveCount(3)
            ->and($book->primaryCover()->id)->toBe($primaryCover->id);
    });

    it('deletes covers when book is deleted', function () {
        $book = Book::factory()->create();
        $cover1 = $book->covers()->create(['is_primary' => true]);
        $cover2 = $book->covers()->create(['is_primary' => false]);

        $book->delete();

        expect(Cover::find($cover1->id))->toBeNull();
        expect(Cover::find($cover2->id))->toBeNull();
    });
});

describe('Primary Cover Attribute Logic', function () {
    it('returns original_cover url when no media exists', function () {
        Queue::fake();

        $originalCoverUrl = 'https://example.com/cover.jpg';
        $book = Book::factory()->create(['original_cover' => $originalCoverUrl]);

        $result = $book->primary_cover;

        expect($result)->toBe($originalCoverUrl);

        ImportBookCover::assertPushed(1);
    });

    it('returns default cover when no original_cover and no media', function () {
        Queue::fake();

        $book = Book::factory()->create(['original_cover' => null]);

        $result = $book->primary_cover;

        expect($result)->toContain('default-cover.svg');
        ImportBookCover::assertPushed(1);
    });

    it('returns media url when cover has valid media file', function () {
        Queue::fake();

        $book = Book::factory()->create();
        $cover = $book->primaryCover();

        // Create fake media file and add to cover
        Storage::disk('public')->put('test-cover.jpg', 'fake-image-content');

        $cover->addMediaFromDisk('test-cover.jpg', 'public')
            ->usingName('cover')
            ->usingFileName('cover.jpg')
            ->toMediaCollection('image');

        $result = $book->fresh()->primary_cover;

        expect($result)->toContain('cover.jpg');

        ImportBookCover::assertNotPushed();
    });

    it('falls back to original_cover when media file does not exist on disk', function () {
        Queue::fake();

        $originalCoverUrl = 'https://example.com/original-cover.jpg';
        $book = Book::factory()->create(['original_cover' => $originalCoverUrl]);
        $cover = $book->primaryCover();

        // Create fake media file but then mock File::exists to return false
        Storage::disk('public')->put('test-cover.jpg', 'fake-image-content');

        $cover->addMediaFromDisk('test-cover.jpg', 'public')
            ->usingName('cover')
            ->usingFileName('cover.jpg')
            ->toMediaCollection('image');

        // Mock File::exists to return false to simulate missing file
        File::shouldReceive('exists')
            ->once()
            ->andReturn(false);

        $result = $book->fresh()->primary_cover;

        expect($result)->toBe($originalCoverUrl);

        ImportBookCover::assertPushed(1);
    });
});

describe('ImportBookCover Action', function () {
    it('successfully imports cover from url and updates color', function () {
        $book = Book::factory()->create();
        $coverUrl = 'https://picsum.photos/300/400';

        $action = new ImportBookCover;
        $action->handle($book, $coverUrl);

        $primaryCover = $book->primaryCover();
        expect($primaryCover->hasMedia('image'))->toBeTrue();
    });

    it('handles failed cover import gracefully', function () {
        $book = Book::factory()->create();
        $invalidUrl = 'https://invalid-url-that-does-not-exist.com/cover.jpg';

        $action = new ImportBookCover;

        // Should not throw exception
        expect(function () use ($action, $book, $invalidUrl) {
            $action->handle($book, $invalidUrl);
        })->not->toThrow(Exception::class);

        $primaryCover = $book->primaryCover();
        expect($primaryCover->hasMedia('image'))->toBeFalse();
    });

    it('can import cover when null url is provided', function () {
        $book = Book::factory()->create();

        $action = new ImportBookCover;

        expect(function () use ($action, $book) {
            $action->handle($book, null);
        })->not->toThrow(Exception::class);
    });
});

describe('Cover Model', function () {
    it('returns default cover image when no media exists', function () {
        $cover = Cover::factory()->create();

        $imageUrl = $cover->image;

        expect($imageUrl)->toContain('default-cover.svg');
    });

    it('returns media url when media exists', function () {
        $cover = Cover::factory()->create();

        Storage::disk('public')->put('test-cover.jpg', 'fake-image-content');
        $cover->addMediaFromDisk('test-cover.jpg', 'public')
            ->usingName('cover')
            ->usingFileName('cover.jpg')
            ->toMediaCollection('image');

        $imageUrl = $cover->fresh()->image;

        expect($imageUrl)->not->toContain('default-cover.svg')
            ->and($imageUrl)->toContain('cover.jpg');
    });

    it('has single file media collection for images', function () {
        $cover = Cover::factory()->create();

        Storage::disk('public')->put('test-cover-1.jpg', 'fake-image-content-1');
        Storage::disk('public')->put('test-cover-2.jpg', 'fake-image-content-2');

        $cover->addMediaFromDisk('test-cover-1.jpg', 'public')
            ->usingName('cover1')
            ->usingFileName('cover1.jpg')
            ->toMediaCollection('image');
        $cover->addMediaFromDisk('test-cover-2.jpg', 'public')
            ->usingName('cover2')
            ->usingFileName('cover2.jpg')
            ->toMediaCollection('image');

        expect($cover->fresh()->getMedia('image'))->toHaveCount(1);
    });
});

describe('Book Cover Integration', function () {
    it('handles book with missing original cover gracefully', function () {
        $book = Book::factory()->create(['original_cover' => 'not-a-valid-url']);
        $cover = $book->primaryCover();

        // Create media record but simulate missing file
        Storage::disk('public')->put('corrupted.jpg', 'fake-content');
        $cover->addMediaFromDisk('corrupted.jpg', 'public')
            ->usingName('corrupted')
            ->usingFileName('does-not-exist.jpg')
            ->toMediaCollection('image');

        // Mock File::exists to return false to simulate corruption
        File::shouldReceive('exists')->once()->andReturn(false);

        $result = $book->fresh()->primary_cover;

        expect($result)->toBe('not-a-valid-url');
    });

    it('handles multiple rapid requests for primary cover gracefully', function () {
        $book = Book::factory()->create();

        $cover1 = $book->primaryCover();
        $cover2 = $book->primaryCover();
        $cover3 = $book->primaryCover();

        expect($cover1->id)->toBe($cover2->id);
        expect($cover2->id)->toBe($cover3->id);
        expect($book->covers)->toHaveCount(1);
    });
});
