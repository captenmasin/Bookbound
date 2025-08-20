<?php

use App\Models\Book;
use App\Models\User;

use function Pest\Laravel\post;
use function Pest\Laravel\delete;

use Illuminate\Http\UploadedFile;

use function Pest\Laravel\actingAs;

use Illuminate\Support\Facades\Storage;

uses(\Tests\Concerns\GiveSubscription::class);

beforeEach(function () {
    Storage::fake('public');
});

test('authenticated non-subscribed user cannot upload book cover', function () {
    $user = User::factory()->create();
    $book = Book::factory()->create();

    actingAs($user);
    $file = UploadedFile::fake()->image('cover.jpg');

    $response = post(route('cover.update', $book), [
        'cover' => $file,
    ]);

    $response->assertRedirectBack();
    $response->assertSessionHas('error', 'Your current plan does not allow adding custom covers.');
    $this->assertDatabaseMissing('covers', [
        'book_id' => $book->id,
        'user_id' => $user->id,
    ]);
});

test('authenticated subscribed user can upload book cover', function () {
    $user = User::factory()->create();
    $book = Book::factory()->create();

    $this->giveActiveSubscription($user, config('subscriptions.plans.pro.key'));

    actingAs($user);

    $file = UploadedFile::fake()->image('cover.jpg');

    $response = post(route('cover.update', $book), [
        'cover' => $file,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');
    $this->assertDatabaseHas('covers', [
        'book_id' => $book->id,
        'user_id' => $user->id,
    ]);
});

test('authenticated user can remove book cover', function () {
    $user = User::factory()->create();
    $book = Book::factory()->create();
    actingAs($user);
    $cover = $book->covers()->create(['user_id' => $user->id]);

    $response = delete(route('cover.destroy', $book));

    $response->assertStatus(200);
    $this->assertDatabaseMissing('covers', [
        'book_id' => $book->id,
        'user_id' => $user->id,
    ]);
});

test('guest cannot update book cover', function () {
    $book = Book::factory()->create();
    $file = UploadedFile::fake()->image('cover.jpg');

    $response = post(route('cover.update', $book), [
        'cover' => $file,
    ]);

    $response->assertRedirect('/login');
});

test('guest cannot remove book cover', function () {
    $book = Book::factory()->create();
    $response = delete(route('cover.destroy', $book));
    $response->assertRedirect('/login');
});

test('invalid file type is rejected', function () {
    $user = User::factory()->create();
    $book = Book::factory()->create();

    $this->giveActiveSubscription($user, config('subscriptions.plans.pro.key'));

    actingAs($user);
    $file = UploadedFile::fake()->create('cover.pdf', 100, 'application/pdf');

    post(route('cover.update', $book), [
        'cover' => $file,
    ]);

    expect(session('errors')?->getBag('bookCoverBag')->has('cover'))->toBeTrue();
});
