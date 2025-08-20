<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia;
use App\Actions\Books\SearchBooksFromApi;

test('search page is displayed', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->get(route('books.search'));

    $response->assertStatus(200);
    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->component('books/Search')
        ->has('initialQuery')
        ->has('page')
        ->has('perPage')
    );
});

test('search page requires authentication', function () {
    $response = $this->get(route('books.search'));

    $response->assertRedirect(route('login'));
});

test('book search returns results', function () {
    $user = User::factory()->create();

    // Mock the SearchBooksFromApi action
    $mock = Mockery::mock(SearchBooksFromApi::class);
    $mock->shouldReceive('run')
        ->with('harry potter', null, 10, 1)
        ->andReturn([
            'items' => [
                [
                    'identifier' => '9780747532743',
                    'title' => 'Harry Potter',
                    'authors' => ['J.K. Rowling'],
                    'publishedDate' => '1997-06-26',
                    'description' => 'A young wizard embarks on an adventure.',
                    'pageCount' => 223,
                    'cover' => 'https://example.com/cover.jpg',
                    'codes' => [
                        ['type' => 'ISBN_13', 'identifier' => '9780747532743'],
                        ['type' => 'ISBN_10', 'identifier' => '0747532745'],
                    ],
                ],
            ],
            'total' => 1,
        ]);

    $this->app->instance(SearchBooksFromApi::class, $mock);

    $response = $this->actingAs($user)
        ->get(route('books.search', ['q' => 'harry potteer']));

    $response->assertStatus(200);
});
