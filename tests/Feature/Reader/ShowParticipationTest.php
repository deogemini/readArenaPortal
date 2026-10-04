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
    [$book, $show] = createUpcomingParticipatoryShow();
    verifyReaderForShow($reader, $book, 92);

    $this->actingAs($reader)->post(route('reader.shows.rsvp.store', $show))->assertRedirect(route('reader.shows'));
    $this->actingAs($reader)->post(route('reader.shows.rsvp.store', $show))->assertRedirect(route('reader.shows'));
    $this->assertDatabaseCount('show_rsvps', 1);

    $this->actingAs($reader)->post(route('reader.shows.applications.store', $show), [
        'motivation' => 'I would bring a thoughtful perspective to this conversation.',
    ])->assertRedirect(route('reader.shows'));
    $application = ShowApplication::firstOrFail();
    expect($application->quiz_score)->toBe(92)->and($application->status)->toBe('pending');

    $this->actingAs($admin)->get(route('admin.shows'))->assertOk()->assertSee('Guest applications');
    $this->actingAs($admin)->patch(route('admin.show-applications.update', $application), ['status' => 'approved'])
        ->assertRedirect(route('admin.shows'));
    expect($application->fresh()->status)->toBe('approved');

    $this->actingAs($reader)->delete(route('reader.shows.rsvp.destroy', $show))->assertRedirect(route('reader.shows'));
    $this->assertDatabaseCount('show_rsvps', 0);
    $this->actingAs($otherReader)->delete(route('reader.show-applications.destroy', $application))->assertNotFound();
});

test('guest applications require a passing quiz for the show book and pending applications may be withdrawn', function () {
    $reader = User::factory()->create();
    [$book, $show] = createUpcomingParticipatoryShow('verified');

    $this->actingAs($reader)->from(route('reader.shows'))->post(route('reader.shows.applications.store', $show), [
        'motivation' => 'I have a few ideas to contribute to this session.',
    ])->assertSessionHasErrors('show');

    verifyReaderForShow($reader, $book);
    $this->actingAs($reader)->post(route('reader.shows.applications.store', $show), [
        'motivation' => 'I have a few ideas to contribute to this session.',
    ])->assertRedirect(route('reader.shows'));
    $application = ShowApplication::firstOrFail();

    $this->actingAs($reader)->delete(route('reader.show-applications.destroy', $application))->assertRedirect(route('reader.shows'));
    expect($application->fresh()->status)->toBe('withdrawn');
});

test('android api supports show RSVP and guest applications', function () {
    $reader = User::factory()->create();
    [$book, $show] = createUpcomingParticipatoryShow('api');
    verifyReaderForShow($reader, $book, 95);

    $this->actingAs($reader, 'sanctum')->getJson('/api/shows')
        ->assertOk()
        ->assertJsonPath('data.0.is_registered', false);

    $this->actingAs($reader, 'sanctum')->postJson('/api/shows/'.$show->id.'/rsvp')
        ->assertCreated()->assertJsonPath('data.show_id', $show->id);
    $this->actingAs($reader, 'sanctum')->getJson('/api/shows')
        ->assertJsonPath('data.0.is_registered', true)
        ->assertJsonPath('data.0.rsvp_count', 1);

    $applicationResponse = $this->actingAs($reader, 'sanctum')->postJson('/api/shows/'.$show->id.'/applications', [
        'motivation' => 'I would enjoy discussing how the book approaches identity.',
    ])->assertCreated()->assertJsonPath('data.quiz_score', 95);
    $applicationId = $applicationResponse->json('data.id');

    $this->actingAs($reader, 'sanctum')->getJson('/api/show-applications')
        ->assertOk()->assertJsonPath('data.0.id', $applicationId);
    $this->actingAs($reader, 'sanctum')->deleteJson('/api/show-applications/'.$applicationId)
        ->assertOk()->assertJsonPath('data.status', 'withdrawn');
    $this->actingAs($reader, 'sanctum')->deleteJson('/api/shows/'.$show->id.'/rsvp')->assertOk();
});
