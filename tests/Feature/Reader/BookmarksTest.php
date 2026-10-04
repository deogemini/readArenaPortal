<?php

use App\Models\Book;
use App\Models\Bookmark;
use App\Models\User;

function bookmarkTestBook(string $slug): Book
{
    return Book::create([
        'title' => 'Bookmark Book',
        'slug' => $slug,
        'page_count' => 150,
        'status' => 'published',
    ]);
}

test('readers can create update and delete their page bookmarks on the web', function () {
    $reader = User::factory()->create();
    $otherReader = User::factory()->create();
    $book = bookmarkTestBook('web-bookmark-book');

    $this->actingAs($reader)->post(route('reader.books.bookmarks.store', $book->slug), [
        'page_number' => 24,
        'label' => 'Important passage',
    ])->assertRedirect(route('reader.books.show', $book->slug));
    $bookmark = Bookmark::firstOrFail();

    $this->actingAs($reader)->patch(route('reader.bookmarks.update', $bookmark), [
        'page_number' => 25,
        'label' => 'Updated passage note',
    ])->assertRedirect(route('reader.books.show', $book->slug));
    expect($bookmark->fresh()->page_number)->toBe(25)->and($bookmark->fresh()->label)->toBe('Updated passage note');

    $this->actingAs($otherReader)->patch(route('reader.bookmarks.update', $bookmark), ['page_number' => 26])->assertForbidden();
    $this->actingAs($reader)->delete(route('reader.bookmarks.destroy', $bookmark))->assertRedirect(route('reader.books.show', $book->slug));
    $this->assertDatabaseMissing('bookmarks', ['id' => $bookmark->id]);
});

test('android readers can list and manage owned page bookmarks', function () {
    $reader = User::factory()->create();
    $otherReader = User::factory()->create();
    $book = bookmarkTestBook('api-bookmark-book');

    $created = $this->actingAs($reader, 'sanctum')->postJson('/api/bookmarks', [
        'book_id' => $book->id,
        'page_number' => 18,
        'label' => 'Remember this scene',
    ])->assertCreated()->assertJsonPath('data.page_number', 18)->json('data');
    $bookmark = Bookmark::findOrFail($created['id']);

    $this->actingAs($reader, 'sanctum')->getJson('/api/bookmarks')
        ->assertOk()->assertJsonPath('data.0.book.id', $book->id);
    $this->actingAs($reader, 'sanctum')->patchJson('/api/bookmarks/'.$bookmark->id, [
        'page_number' => 30,
        'label' => 'Revised note',
    ])->assertOk()->assertJsonPath('data.page_number', 30);

    $this->actingAs($otherReader, 'sanctum')->deleteJson('/api/bookmarks/'.$bookmark->id)->assertForbidden();
    $this->actingAs($reader, 'sanctum')->deleteJson('/api/bookmarks/'.$bookmark->id)->assertOk();
    $this->assertDatabaseMissing('bookmarks', ['id' => $bookmark->id]);
});

test('bookmarks reject duplicate pages and page numbers beyond the book length', function () {
    $reader = User::factory()->create();
    $book = bookmarkTestBook('bookmark-validation-book');

    $this->actingAs($reader, 'sanctum')->postJson('/api/bookmarks', [
        'book_id' => $book->id,
        'page_number' => 151,
    ])->assertUnprocessable()->assertJsonValidationErrors('page_number');

    $this->actingAs($reader, 'sanctum')->postJson('/api/bookmarks', [
        'book_id' => $book->id,
        'page_number' => 20,
    ])->assertCreated();
    $this->actingAs($reader, 'sanctum')->postJson('/api/bookmarks', [
        'book_id' => $book->id,
        'page_number' => 20,
    ])->assertUnprocessable()->assertJsonValidationErrors('page_number');
});
