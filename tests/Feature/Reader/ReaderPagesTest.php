<?php

use App\Models\Book;
use App\Models\User;

test('reader dashboard, library, book, duel, show, and goals pages render without demo-only data', function () {
    $reader = User::factory()->create();
    $book = Book::create([
        'title' => 'Rendered Book',
        'slug' => 'rendered-book',
        'page_count' => 90,
        'status' => 'published',
    ]);

    $this->actingAs($reader)->get(route('reader.dashboard'))->assertOk()->assertDontSee('Mina Rivera');
    $this->actingAs($reader)->get(route('reader.library'))->assertOk()->assertSee('Rendered Book');
    $this->actingAs($reader)->get(route('reader.books.show', $book->slug))->assertOk()->assertSee('Page bookmarks')->assertSee('Your shelf');
    $this->actingAs($reader)->get(route('reader.duels'))->assertOk()->assertSee('Challenge someone');
    $this->actingAs($reader)->get(route('reader.shows'))->assertOk()->assertSee('Upcoming sessions');
    $this->actingAs($reader)->get(route('reader.goals'))->assertOk()->assertSee('Set a new goal');
});
