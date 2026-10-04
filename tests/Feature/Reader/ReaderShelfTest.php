<?php

use App\Models\Book;
use App\Models\ReaderShelf;
use App\Models\User;

function shelfBook(string $slug, string $status = 'published'): Book
{
    return Book::create(['title' => 'Shelf Book', 'slug' => $slug, 'status' => $status]);
}

test('readers can save, change and remove a book shelf status on the web', function () {
    $reader = User::factory()->create();
    $otherReader = User::factory()->create();
    $book = shelfBook('shelf-web-book');

    $this->actingAs($reader)->post(route('reader.books.shelf.update', $book->slug), ['status' => 'currently_reading'])
        ->assertRedirect(route('reader.books.show', $book->slug));
    $shelf = ReaderShelf::firstOrFail();
    expect($shelf->status)->toBe('currently_reading');

    $this->actingAs($reader)->post(route('reader.books.shelf.update', $book->slug), ['status' => 'completed'])
        ->assertRedirect(route('reader.books.show', $book->slug));
    expect($shelf->fresh()->status)->toBe('completed');

    $this->actingAs($otherReader)->delete(route('reader.books.shelf.destroy', $book->slug))
        ->assertRedirect(route('reader.books.show', $book->slug));
    expect(ReaderShelf::where('user_id', $reader->id)->where('book_id', $book->id)->exists())->toBeTrue();

    $this->actingAs($reader)->delete(route('reader.books.shelf.destroy', $book->slug))
        ->assertRedirect(route('reader.books.show', $book->slug));
    $this->assertDatabaseMissing('reader_shelves', ['id' => $shelf->id]);
});

test('android API manages the signed-in reader shelf and rejects unpublished books', function () {
    $reader = User::factory()->create();
    $book = shelfBook('shelf-api-book');
    $draft = shelfBook('shelf-api-draft', 'draft');

    $this->actingAs($reader, 'sanctum')->putJson('/api/books/'.$book->id.'/shelf', ['status' => 'want_to_read'])
        ->assertOk()->assertJsonPath('data.status', 'want_to_read');
    $this->actingAs($reader, 'sanctum')->putJson('/api/books/'.$book->id.'/shelf', ['status' => 'paused'])
        ->assertOk()->assertJsonPath('data.status', 'paused');

    $this->actingAs($reader, 'sanctum')->getJson('/api/shelf')
        ->assertOk()->assertJsonPath('data.0.status', 'paused')->assertJsonPath('data.0.book.id', $book->id);
    $this->actingAs($reader, 'sanctum')->getJson('/api/books/'.$book->id)
        ->assertOk()->assertJsonPath('data.shelf_status', 'paused');

    $this->actingAs($reader, 'sanctum')->putJson('/api/books/'.$draft->id.'/shelf', ['status' => 'completed'])
        ->assertUnprocessable()->assertJsonValidationErrors('book');
    $this->actingAs($reader, 'sanctum')->putJson('/api/books/'.$book->id.'/shelf', ['status' => 'reading'])
        ->assertUnprocessable()->assertJsonValidationErrors('status');

    $this->actingAs($reader, 'sanctum')->deleteJson('/api/books/'.$book->id.'/shelf')->assertOk();
    $this->assertDatabaseCount('reader_shelves', 0);
});
