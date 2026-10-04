<?php

use App\Models\Book;
use App\Models\LiveShow;
use App\Models\User;

function createPublishedShowBook(): Book
{
    return Book::create([
        'title' => 'Show Book',
        'slug' => 'show-book',
        'description' => 'A book for the live show.',
        'status' => 'published',
    ]);
}

test('administrators can create update and delete live shows', function () {
    $admin = User::factory()->admin()->create();
    $book = createPublishedShowBook();

    $this->actingAs($admin)->post(route('admin.shows.store'), [
        'title' => 'The Reading Salon',
        'description' => 'A discussion about the book.',
        'book_id' => $book->id,
        'start_at' => '2026-11-01T18:30',
        'status' => 'scheduled',
    ])->assertRedirect(route('admin.shows'));

    $show = LiveShow::where('title', 'The Reading Salon')->firstOrFail();
    expect($show->book_id)->toBe($book->id);

    $this->actingAs($admin)->patch(route('admin.shows.update', $show), [
        'title' => 'The Reading Salon Live',
        'description' => 'Now live.',
        'book_id' => $book->id,
        'start_at' => '2026-11-02T19:00',
        'status' => 'live',
    ])->assertRedirect(route('admin.shows'));

    expect($show->fresh()->title)->toBe('The Reading Salon Live')
        ->and($show->fresh()->status)->toBe('live');

    $this->actingAs($admin)->delete(route('admin.shows.destroy', $show))
        ->assertRedirect(route('admin.shows'));

    $this->assertDatabaseMissing('live_shows', ['id' => $show->id]);
});

test('readers cannot manage live shows', function () {
    $reader = User::factory()->create();

    $this->actingAs($reader)->get(route('admin.shows'))->assertForbidden();
    $this->actingAs($reader)->post(route('admin.shows.store'), [
        'title' => 'Unauthorized Show',
        'start_at' => '2026-11-01T18:30',
        'status' => 'scheduled',
    ])->assertForbidden();

    $this->assertDatabaseMissing('live_shows', ['title' => 'Unauthorized Show']);
});

test('live shows must reference published books', function () {
    $admin = User::factory()->admin()->create();
    $book = Book::create([
        'title' => 'Draft Book',
        'slug' => 'draft-show-book',
        'status' => 'draft',
    ]);

    $this->actingAs($admin)->from(route('admin.shows'))->post(route('admin.shows.store'), [
        'title' => 'Show for draft book',
        'book_id' => $book->id,
        'start_at' => '2026-11-01T18:30',
        'status' => 'scheduled',
    ])->assertSessionHasErrors('book_id');

    $this->assertDatabaseMissing('live_shows', ['title' => 'Show for draft book']);
});
