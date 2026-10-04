<?php

use App\Models\Book;
use App\Models\ReadingGoal;
use App\Models\User;

function readingGoalBook(string $slug = 'goal-book', string $status = 'published'): Book
{
    return Book::create(['title' => 'Goal Book', 'slug' => $slug, 'status' => $status]);
}

function readingGoalPayload(array $overrides = []): array
{
    return array_merge([
        'title' => 'Read this year',
        'goal_type' => 'books',
        'book_id' => null,
        'target_value' => 12,
        'start_date' => '2026-01-01',
        'end_date' => '2026-12-31',
    ], $overrides);
}

test('readers can create update and delete their own goals on the web', function () {
    $reader = User::factory()->create();
    $otherReader = User::factory()->create();
    $book = readingGoalBook();

    $this->actingAs($reader)->post(route('reader.goals.store'), readingGoalPayload())
        ->assertRedirect(route('reader.goals'));
    $goal = ReadingGoal::where('user_id', $reader->id)->firstOrFail();

    $this->actingAs($reader)->patch(route('reader.goals.update', $goal), readingGoalPayload([
        'title' => 'Finish eighteen books',
        'goal_type' => 'pages',
        'book_id' => $book->id,
        'target_value' => 180,
    ]))->assertRedirect(route('reader.goals'));
    expect($goal->fresh()->title)->toBe('Finish eighteen books')
        ->and($goal->fresh()->book_id)->toBe($book->id)
        ->and($goal->fresh()->target_value)->toBe(180);

    $this->actingAs($otherReader)->patch(route('reader.goals.update', $goal), readingGoalPayload())->assertNotFound();
    $this->actingAs($otherReader)->delete(route('reader.goals.destroy', $goal))->assertNotFound();
    $this->actingAs($reader)->delete(route('reader.goals.destroy', $goal))->assertRedirect(route('reader.goals'));
    $this->assertDatabaseMissing('reading_goals', ['id' => $goal->id]);
});

test('android readers can create update and delete their own reading goals', function () {
    $reader = User::factory()->create();
    $otherReader = User::factory()->create();
    $book = readingGoalBook('api-goal-book');

    $created = $this->actingAs($reader, 'sanctum')->postJson('/api/goals', readingGoalPayload([
        'goal_type' => 'pages',
        'book_id' => $book->id,
    ]))->assertCreated()->json('data');
    $goal = ReadingGoal::findOrFail($created['id']);

    $this->actingAs($reader, 'sanctum')->patchJson('/api/goals/'.$goal->id, readingGoalPayload([
        'title' => 'Read 240 pages',
        'goal_type' => 'pages',
        'book_id' => $book->id,
        'target_value' => 240,
    ]))->assertOk()->assertJsonPath('data.target_value', 240);

    $this->actingAs($otherReader, 'sanctum')->deleteJson('/api/goals/'.$goal->id)->assertNotFound();
    $this->actingAs($reader, 'sanctum')->deleteJson('/api/goals/'.$goal->id)->assertOk();
    $this->assertDatabaseMissing('reading_goals', ['id' => $goal->id]);
});

test('page goals cannot be linked to unpublished books', function () {
    $reader = User::factory()->create();
    $draftBook = readingGoalBook('draft-goal-book', 'draft');

    $this->actingAs($reader, 'sanctum')->postJson('/api/goals', readingGoalPayload([
        'goal_type' => 'pages',
        'book_id' => $draftBook->id,
    ]))->assertUnprocessable()->assertJsonValidationErrors('book_id');

    $this->assertDatabaseCount('reading_goals', 0);
});
