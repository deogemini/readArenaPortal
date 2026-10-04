<?php

use App\Models\Book;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;

function readerContentBook(string $slug = 'content-book'): Book
{
    return Book::create([
        'title' => 'Content Book',
        'slug' => $slug,
        'page_count' => 200,
        'status' => 'published',
    ]);
}

function verifyReaderForContentBook(User $reader, Book $book): void
{
    $quiz = Quiz::create([
        'book_id' => $book->id,
        'title' => 'Verified reading quiz',
        'pass_mark' => 70,
        'attempt_limit' => 3,
        'duration_minutes' => 10,
        'status' => 'published',
    ]);

    QuizAttempt::create([
        'quiz_id' => $quiz->id,
        'user_id' => $reader->id,
        'score' => 90,
        'passed' => true,
    ]);
}

test('readers can create update and delete lessons, with verification required to publish', function () {
    $reader = User::factory()->create();
    $otherReader = User::factory()->create();
    $book = readerContentBook();

    $this->actingAs($reader)->post(route('reader.lessons.store'), [
        'book_id' => $book->id,
        'title' => 'A draft reflection',
        'content' => 'Private notes about the story.',
        'visibility' => 'private',
        'status' => 'draft',
    ])->assertRedirect(route('reader.lessons'));

    $lesson = $reader->fresh()->id;
    $lessonRecord = \App\Models\Lesson::where('user_id', $lesson)->firstOrFail();

    $this->actingAs($reader)->patch(route('reader.lessons.update', $lessonRecord), [
        'book_id' => $book->id,
        'title' => 'A public reflection',
        'content' => 'This book changed how I see memory.',
        'visibility' => 'public',
        'status' => 'published',
    ])->assertSessionHasErrors('status');

    verifyReaderForContentBook($reader, $book);

    $this->actingAs($reader)->patch(route('reader.lessons.update', $lessonRecord), [
        'book_id' => $book->id,
        'title' => 'A public reflection',
        'content' => 'This book changed how I see memory.',
        'visibility' => 'public',
        'status' => 'published',
    ])->assertRedirect(route('reader.lessons'));

    expect($lessonRecord->fresh()->status)->toBe('published')
        ->and($lessonRecord->fresh()->title)->toBe('A public reflection');

    $this->actingAs($otherReader)->delete(route('reader.lessons.destroy', $lessonRecord))->assertForbidden();
    $this->actingAs($reader)->delete(route('reader.lessons.destroy', $lessonRecord))->assertRedirect(route('reader.lessons'));
    $this->assertDatabaseMissing('lessons', ['id' => $lessonRecord->id]);
});

test('readers can create update and delete recommendations, with verification required to publish', function () {
    $reader = User::factory()->create();
    $otherReader = User::factory()->create();
    $book = readerContentBook('recommendation-book');

    $this->actingAs($reader)->post(route('reader.recommendations.store'), [
        'book_id' => $book->id,
        'message' => 'A thoughtful and memorable read.',
        'rating' => 5,
        'visibility' => 'private',
        'status' => 'draft',
    ])->assertRedirect(route('reader.recommendations'));

    $recommendation = \App\Models\Recommendation::where('user_id', $reader->id)->firstOrFail();

    $this->actingAs($reader)->patch(route('reader.recommendations.update', $recommendation), [
        'book_id' => $book->id,
        'message' => 'Everyone should read this novel.',
        'rating' => 4,
        'visibility' => 'public',
        'status' => 'published',
    ])->assertSessionHasErrors('status');

    verifyReaderForContentBook($reader, $book);

    $this->actingAs($reader)->patch(route('reader.recommendations.update', $recommendation), [
        'book_id' => $book->id,
        'message' => 'Everyone should read this novel.',
        'rating' => 4,
        'visibility' => 'public',
        'status' => 'published',
    ])->assertRedirect(route('reader.recommendations'));

    expect($recommendation->fresh()->status)->toBe('published')
        ->and($recommendation->fresh()->rating)->toBe(4);

    $this->actingAs($otherReader)->patch(route('reader.recommendations.update', $recommendation), [
        'book_id' => $book->id,
        'message' => 'Changed by someone else.',
        'rating' => 1,
        'visibility' => 'public',
        'status' => 'published',
    ])->assertForbidden();

    $this->actingAs($reader)->delete(route('reader.recommendations.destroy', $recommendation))->assertRedirect(route('reader.recommendations'));
    $this->assertDatabaseMissing('recommendations', ['id' => $recommendation->id]);
});

test('android clients can create and manage their own draft lessons and recommendations', function () {
    $reader = User::factory()->create();
    $book = readerContentBook('api-content-book');

    $lessonResponse = $this->actingAs($reader, 'sanctum')->postJson('/api/lessons', [
        'book_id' => $book->id,
        'title' => 'API lesson draft',
        'content' => 'A useful idea from the book.',
        'visibility' => 'private',
        'status' => 'draft',
    ]);
    $lessonResponse->assertCreated()->assertJsonPath('data.title', 'API lesson draft');
    $lessonId = $lessonResponse->json('data.id');

    $this->actingAs($reader, 'sanctum')->patchJson('/api/lessons/'.$lessonId, [
        'book_id' => $book->id,
        'title' => 'Updated API lesson',
        'content' => 'Revised notes.',
        'visibility' => 'private',
        'status' => 'draft',
    ])->assertOk()->assertJsonPath('data.title', 'Updated API lesson');

    $recommendationResponse = $this->actingAs($reader, 'sanctum')->postJson('/api/recommendations', [
        'book_id' => $book->id,
        'message' => 'A strong read.',
        'rating' => 4,
        'visibility' => 'private',
        'status' => 'draft',
    ]);
    $recommendationResponse->assertCreated()->assertJsonPath('data.rating', 4);
    $recommendationId = $recommendationResponse->json('data.id');

    $this->actingAs($reader, 'sanctum')->patchJson('/api/recommendations/'.$recommendationId, [
        'book_id' => $book->id,
        'message' => 'Updated recommendation.',
        'rating' => 5,
        'visibility' => 'private',
        'status' => 'draft',
    ])->assertOk()->assertJsonPath('data.rating', 5);

    $this->actingAs($reader, 'sanctum')->deleteJson('/api/lessons/'.$lessonId)->assertOk();
    $this->actingAs($reader, 'sanctum')->deleteJson('/api/recommendations/'.$recommendationId)->assertOk();
    $this->assertDatabaseMissing('lessons', ['id' => $lessonId]);
    $this->assertDatabaseMissing('recommendations', ['id' => $recommendationId]);
});
