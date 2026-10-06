<?php

use App\Models\Book;
use App\Models\LiveShow;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\ShowApplication;
use App\Models\User;

function createUpcomingParticipatoryShow(string $suffix = 'one'): array
{
    $book = Book::create([
        'title' => 'Discussion Book',
        'slug' => 'discussion-book-'.$suffix,
        'status' => 'published',
    ]);
    $show = LiveShow::create([
        'title' => 'Reader Discussion '.$suffix,
        'book_id' => $book->id,
        'start_at' => now()->addWeek(),
        'status' => 'scheduled',
    ]);

    return [$book, $show];
}

function verifyReaderForShow(User $reader, Book $book, int $score = 88): void
{
    $quiz = Quiz::create([
        'book_id' => $book->id,
        'title' => 'Show quiz '.$reader->id,
        'status' => 'published',
        'pass_mark' => 70,
        'attempt_limit' => 3,
        'duration_minutes' => 10,
    ]);
    QuizAttempt::create([
        'quiz_id' => $quiz->id,
        'user_id' => $reader->id,
        'score' => $score,
        'passed' => true,
    ]);
}

test('readers can RSVP and apply as verified guests, then admins can review applications', function () {
    $reader = User::factory()->create();
    $otherReader = User::factory()->create();
    $admin = User::factory()->admin()->create();
    [$showBook, $show] = createUpcomingParticipatoryShow();
    $selectedBook = Book::create(['title' => 'Selected Discussion Book', 'slug' => 'selected-discussion-book', 'status' => 'published']);
    verifyReaderForShow($reader, $selectedBook, 92);

    $this->actingAs($reader)->post(route('reader.shows.rsvp.store', $show))->assertRedirect(route('reader.shows'));
    $this->actingAs($reader)->post(route('reader.shows.rsvp.store', $show))->assertRedirect(route('reader.shows'));
    $this->assertDatabaseCount('show_rsvps', 1);
    $this->actingAs($reader)->get(route('reader.shows'))
        ->assertOk()->assertSee('name="book_id"', false)->assertSee($selectedBook->title);

    $this->actingAs($reader)->post(route('reader.shows.applications.store', $show), [
        'book_id' => $selectedBook->id,
        'motivation' => 'I would bring a thoughtful perspective to this conversation.',
    ])->assertRedirect(route('reader.shows'));
    $application = ShowApplication::firstOrFail();
    expect($show->book_id)->toBe($showBook->id)
        ->and($application->book_id)->toBe($selectedBook->id)
        ->and($application->quiz_score)->toBe(92)
        ->and($application->status)->toBe('pending');

    $this->actingAs($admin)->get(route('admin.shows'))->assertOk()->assertSee('Live show competition applications');
    $this->actingAs($admin)->patch(route('admin.show-applications.update', $application), ['status' => 'approved'])
        ->assertRedirect(route('admin.shows'));
    expect($application->fresh()->status)->toBe('approved');

    $this->actingAs($reader)->delete(route('reader.shows.rsvp.destroy', $show))->assertRedirect(route('reader.shows'));
    $this->assertDatabaseCount('show_rsvps', 0);
    $this->actingAs($otherReader)->delete(route('reader.show-applications.destroy', $application))->assertNotFound();
});

test('show applications require a passing quiz for the selected book and pending applications may be withdrawn', function () {
    $reader = User::factory()->create();
    [$book, $show] = createUpcomingParticipatoryShow('verified');

    $this->actingAs($reader)->from(route('reader.shows'))->post(route('reader.shows.applications.store', $show), [
        'book_id' => $book->id,
        'motivation' => 'I have a few ideas to contribute to this session.',
    ])->assertSessionHasErrors('book_id');

    verifyReaderForShow($reader, $book);
    $this->actingAs($reader)->post(route('reader.shows.applications.store', $show), [
        'book_id' => $book->id,
        'motivation' => 'I have a few ideas to contribute to this session.',
    ])->assertRedirect(route('reader.shows'));
    $application = ShowApplication::firstOrFail();

    $this->actingAs($reader)->delete(route('reader.show-applications.destroy', $application))->assertRedirect(route('reader.shows'));
    expect($application->fresh()->status)->toBe('withdrawn');
});

test('android api returns eligible books and accepts a selected book for a show application', function () {
    $reader = User::factory()->create();
    [$showBook, $show] = createUpcomingParticipatoryShow('api');
    $selectedBook = Book::create([
        'title' => 'Reader Selected Book',
        'slug' => 'reader-selected-show-book',
        'status' => 'published',
    ]);
    expect($selectedBook->id)->not->toBe($showBook->id);
    verifyReaderForShow($reader, $selectedBook, 95);

    $this->actingAs($reader, 'sanctum')->getJson('/api/shows')
        ->assertOk()
        ->assertJsonPath('data.0.is_registered', false);
    $this->actingAs($reader, 'sanctum')->getJson('/api/shows/'.$show->id.'/application-options')
        ->assertOk()
        ->assertJsonPath('data.show_id', $show->id)
        ->assertJsonPath('data.eligible_books.0.id', $selectedBook->id)
        ->assertJsonCount(1, 'data.eligible_books');
    $this->actingAs($reader, 'sanctum')->postJson('/api/shows/'.$show->id.'/applications', [
        'motivation' => 'I would enjoy discussing how this book approaches identity.',
    ])->assertUnprocessable()->assertJsonValidationErrors('book_id');

    $this->actingAs($reader, 'sanctum')->postJson('/api/shows/'.$show->id.'/rsvp')
        ->assertCreated()->assertJsonPath('data.show_id', $show->id);
    $this->actingAs($reader, 'sanctum')->getJson('/api/shows')
        ->assertJsonPath('data.0.is_registered', true)
        ->assertJsonPath('data.0.rsvp_count', 1);

    $applicationResponse = $this->actingAs($reader, 'sanctum')->postJson('/api/shows/'.$show->id.'/applications', [
        'book_id' => $selectedBook->id,
        'motivation' => 'I would enjoy discussing how the book approaches identity.',
    ])->assertCreated()->assertJsonPath('data.quiz_score', 95)->assertJsonPath('data.book.id', $selectedBook->id);
    $applicationId = $applicationResponse->json('data.id');

    $this->actingAs($reader, 'sanctum')->getJson('/api/show-applications')
        ->assertOk()->assertJsonPath('data.0.id', $applicationId)->assertJsonPath('data.0.book.id', $selectedBook->id);
    $this->actingAs($reader, 'sanctum')->deleteJson('/api/show-applications/'.$applicationId)
        ->assertOk()->assertJsonPath('data.status', 'withdrawn');
    $this->actingAs($reader, 'sanctum')->deleteJson('/api/shows/'.$show->id.'/rsvp')->assertOk();
});
