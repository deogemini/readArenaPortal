<?php

use App\Models\Book;
use App\Models\BookReview;
use App\Models\User;

function reviewTestBook(string $slug): Book
{
    return Book::create(['title' => 'Review Book', 'slug' => $slug, 'status' => 'published']);
}

function reviewPayload(array $overrides = []): array
{
    return array_merge([
        'rating' => 5,
        'title' => 'A thoughtful read',
        'body' => 'The characters and themes stayed with me long after the final page.',
    ], $overrides);
}

test('web reader reviews are held for moderation and can be edited or deleted by their owner', function () {
    $reader = User::factory()->create();
    $otherReader = User::factory()->create();
    $admin = User::factory()->admin()->create();
    $book = reviewTestBook('web-review-book');

    $this->actingAs($reader)->post(route('reader.books.reviews.store', $book->slug), reviewPayload())
        ->assertRedirect(route('reader.books.show', $book->slug));
    $review = BookReview::firstOrFail();
    expect($review->status)->toBe('pending');
    $this->get(route('books.show', $book->slug))->assertOk()->assertDontSee('A thoughtful read');

    $this->actingAs($admin)->patch(route('admin.reviews.update', $review), ['status' => 'published'])
        ->assertRedirect(route('admin.reviews'));
    $this->get(route('books.show', $book->slug))->assertSee('A thoughtful read');

    $this->actingAs($reader)->patch(route('reader.reviews.update', $review), reviewPayload(['rating' => 3]))
        ->assertRedirect(route('reader.books.show', $book->slug));
    expect($review->fresh()->status)->toBe('pending')->and($review->fresh()->rating)->toBe(3);

    $this->actingAs($otherReader)->delete(route('reader.reviews.destroy', $review))->assertForbidden();
    $this->actingAs($reader)->delete(route('reader.reviews.destroy', $review))->assertRedirect(route('reader.books.show', $book->slug));
    $this->assertDatabaseMissing('book_reviews', ['id' => $review->id]);
});

test('android readers can create and manage reviews while only approved reviews are public', function () {
    $reader = User::factory()->create();
    $admin = User::factory()->admin()->create();
    $book = reviewTestBook('api-review-book');

    $created = $this->actingAs($reader, 'sanctum')->postJson('/api/reviews', ['book_id' => $book->id] + reviewPayload())
        ->assertCreated()->assertJsonPath('data.status', 'pending')->json('data');
    $review = BookReview::findOrFail($created['id']);
    $this->actingAs($reader, 'sanctum')->postJson('/api/reviews', ['book_id' => $book->id] + reviewPayload())
        ->assertUnprocessable()->assertJsonValidationErrors('book_id');

    $this->actingAs($admin)->patch(route('admin.reviews.update', $review), ['status' => 'published'])->assertRedirect(route('admin.reviews'));
    $this->actingAs($reader, 'sanctum')->getJson('/api/books/'.$book->id.'/reviews')
        ->assertOk()->assertJsonPath('meta.average_rating', 5)->assertJsonPath('data.0.id', $review->id);
    $this->actingAs($reader, 'sanctum')->getJson('/api/reviews')->assertJsonPath('data.0.status', 'published');

    $this->actingAs($reader, 'sanctum')->patchJson('/api/reviews/'.$review->id, reviewPayload(['rating' => 4]))
        ->assertOk()->assertJsonPath('data.status', 'pending');
    $this->actingAs($reader, 'sanctum')->deleteJson('/api/reviews/'.$review->id)->assertOk();
    $this->assertDatabaseMissing('book_reviews', ['id' => $review->id]);
});

test('readers cannot manage the admin review queue', function () {
    $reader = User::factory()->create();
    $review = BookReview::create([
        'user_id' => $reader->id,
        'book_id' => reviewTestBook('admin-review-book')->id,
        'rating' => 4,
        'body' => 'A review waiting for moderation.',
        'status' => 'pending',
    ]);

    $this->actingAs($reader)->get(route('admin.reviews'))->assertForbidden();
    $this->actingAs($reader)->patch(route('admin.reviews.update', $review), ['status' => 'published'])->assertForbidden();
});
