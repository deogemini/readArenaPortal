<?php

use App\Models\Book;
use App\Models\Duel;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;

function makeVerifiedDuelBook(string $slug = 'duel-book'): Book
{
    return Book::create([
        'title' => 'Duel Book',
        'slug' => $slug,
        'status' => 'published',
    ]);
}

function verifyDuelReader(User $reader, Book $book, bool $passed = true): void
{
    $quiz = Quiz::create([
        'book_id' => $book->id,
        'title' => 'Duel quiz',
        'pass_mark' => 70,
        'attempt_limit' => 3,
        'duration_minutes' => 10,
        'status' => 'published',
    ]);

    QuizAttempt::create([
        'quiz_id' => $quiz->id,
        'user_id' => $reader->id,
        'score' => $passed ? 90 : 40,
        'passed' => $passed,
    ]);
}

test('verified readers can challenge and accept a reader on the same book', function () {
    $challenger = User::factory()->create();
    $opponent = User::factory()->create();
    $book = makeVerifiedDuelBook();
    verifyDuelReader($challenger, $book);
    verifyDuelReader($opponent, $book);

    $this->actingAs($challenger)->get(route('reader.duels'))
        ->assertOk()
        ->assertSee('Send invite')
        ->assertSee($opponent->name);

    $this->actingAs($challenger)->post(route('reader.duels.store'), [
        'book_id' => $book->id,
        'opponent_id' => $opponent->id,
    ])->assertRedirect(route('reader.duels'));

    $duel = Duel::firstOrFail();
    expect($duel->status)->toBe('pending');

    $this->actingAs($opponent)->patch(route('reader.duels.respond', $duel), ['action' => 'accept'])
        ->assertRedirect(route('reader.duels'));

    expect($duel->fresh()->status)->toBe('accepted');
});

test('duel invitations require both readers to pass the quiz for the selected published book', function () {
    $challenger = User::factory()->create();
    $opponent = User::factory()->create();
    $book = makeVerifiedDuelBook('duel-gate-book');
    verifyDuelReader($challenger, $book);
    verifyDuelReader($opponent, $book, false);

    $this->actingAs($challenger)->from(route('reader.duels'))->post(route('reader.duels.store'), [
        'book_id' => $book->id,
        'opponent_id' => $opponent->id,
    ])->assertSessionHasErrors('book_id');

    $this->assertDatabaseCount('duels', 0);
});

test('only the invited reader can respond and only the challenger can cancel', function () {
    $challenger = User::factory()->create();
    $opponent = User::factory()->create();
    $other = User::factory()->create();
    $book = makeVerifiedDuelBook('duel-ownership-book');
    verifyDuelReader($challenger, $book);
    verifyDuelReader($opponent, $book);
    $duel = Duel::create([
        'book_id' => $book->id,
        'challenger_id' => $challenger->id,
        'opponent_id' => $opponent->id,
        'status' => 'pending',
    ]);

    $this->actingAs($other)->patch(route('reader.duels.respond', $duel), ['action' => 'accept'])->assertForbidden();
    $this->actingAs($opponent)->patch(route('reader.duels.cancel', $duel))->assertForbidden();
    $this->actingAs($challenger)->patch(route('reader.duels.cancel', $duel))->assertRedirect(route('reader.duels'));

    expect($duel->fresh()->status)->toBe('cancelled');
});

test('android api supports duel invitation, response and cancellation', function () {
    $challenger = User::factory()->create();
    $opponent = User::factory()->create();
    $book = makeVerifiedDuelBook('duel-api-book');
    verifyDuelReader($challenger, $book);
    verifyDuelReader($opponent, $book);

    $this->actingAs($challenger, 'sanctum')->getJson('/api/duels')
        ->assertOk()
        ->assertJsonPath('meta.unlocked', true)
        ->assertJsonPath('verified_books.0.id', $book->id)
        ->assertJsonPath('verified_books.0.opponents.0.id', $opponent->id);

    $response = $this->actingAs($challenger, 'sanctum')->postJson('/api/duels', [
        'book_id' => $book->id,
        'opponent_id' => $opponent->id,
    ])->assertCreated()->assertJsonPath('data.status', 'pending');
    $duelId = $response->json('data.id');

    $this->actingAs($opponent, 'sanctum')->patchJson('/api/duels/'.$duelId.'/respond', ['action' => 'reject'])
        ->assertOk()
        ->assertJsonPath('data.status', 'rejected');

    $second = Duel::create([
        'book_id' => $book->id,
        'challenger_id' => $challenger->id,
        'opponent_id' => $opponent->id,
        'status' => 'pending',
    ]);
    $this->actingAs($challenger, 'sanctum')->patchJson('/api/duels/'.$second->id.'/cancel')
        ->assertOk()
        ->assertJsonPath('data.status', 'cancelled');
});

test('administrators can inspect duels and cancel an active invitation', function () {
    $admin = User::factory()->admin()->create();
    $challenger = User::factory()->create();
    $opponent = User::factory()->create();
    $book = makeVerifiedDuelBook('duel-admin-book');
    $duel = Duel::create([
        'book_id' => $book->id,
        'challenger_id' => $challenger->id,
        'opponent_id' => $opponent->id,
        'status' => 'pending',
    ]);

    $this->actingAs($admin)->get(route('admin.duels'))->assertOk()->assertSee($challenger->email);
    $this->actingAs($admin)->patch(route('admin.duels.update', $duel), ['status' => 'cancelled'])
        ->assertRedirect(route('admin.duels'));

    expect($duel->fresh()->status)->toBe('cancelled');
});
