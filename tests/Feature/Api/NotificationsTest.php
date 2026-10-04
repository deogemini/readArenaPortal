<?php

use App\Models\ReaderNotification;
use App\Models\ReaderNotificationPreference;
use App\Models\User;
use App\Models\Book;
use App\Models\Quiz;
use App\Models\QuizAttempt;

test('android readers can list, read, and delete only their own notifications', function () {
    $reader = User::factory()->create();
    $otherReader = User::factory()->create();
    $notification = ReaderNotification::create([
        'user_id' => $reader->id,
        'type' => 'quiz_passed',
        'title' => 'Quiz passed',
        'body' => 'You passed the quiz.',
        'data' => ['quiz_id' => 12],
    ]);
    $otherNotification = ReaderNotification::create([
        'user_id' => $otherReader->id,
        'type' => 'quiz_failed',
        'title' => 'Quiz complete',
        'body' => 'Try again next time.',
    ]);

    $this->actingAs($reader, 'sanctum')
        ->getJson('/api/notifications?filter=unread')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $notification->id)
        ->assertJsonPath('meta.unread_count', 1);

    $this->actingAs($reader, 'sanctum')
        ->patchJson('/api/notifications/'.$notification->id.'/read')
        ->assertOk()
        ->assertJsonPath('data.id', $notification->id)
        ->assertJsonPath('data.read_at', fn ($readAt) => is_string($readAt));

    $this->actingAs($reader, 'sanctum')
        ->patchJson('/api/notifications/read-all')
        ->assertOk()
        ->assertJsonPath('data.updated_count', 0);

    $this->actingAs($reader, 'sanctum')
        ->deleteJson('/api/notifications/'.$otherNotification->id)
        ->assertNotFound();

    $this->actingAs($reader, 'sanctum')
        ->deleteJson('/api/notifications/'.$notification->id)
        ->assertOk();

    $this->assertDatabaseMissing('reader_notifications', ['id' => $notification->id]);
    $this->assertDatabaseHas('reader_notifications', ['id' => $otherNotification->id]);
});

test('android readers can update in-app notification preferences and disabled events are not stored', function () {
    $reader = User::factory()->create();

    $this->actingAs($reader, 'sanctum')
        ->getJson('/api/notification-preferences')
        ->assertOk()
        ->assertJsonCount(count(ReaderNotificationPreference::TYPES), 'data')
        ->assertJsonPath('data.0.in_app_enabled', true);

    $this->actingAs($reader, 'sanctum')
        ->patchJson('/api/notification-preferences', [
            'preferences' => [
                ['type' => 'quiz_passed', 'in_app_enabled' => false],
                ['type' => 'duel_invitation', 'in_app_enabled' => false],
            ],
        ])
        ->assertOk()
        ->assertJsonPath('data.0.in_app_enabled', false);

    app(\App\Services\ReaderNotificationService::class)->send($reader, 'quiz_passed', 'Quiz passed', 'Your score was 100%.');
    app(\App\Services\ReaderNotificationService::class)->send($reader, 'quiz_failed', 'Quiz complete', 'Your score was 20%.');

    $this->assertDatabaseMissing('reader_notifications', ['user_id' => $reader->id, 'type' => 'quiz_passed']);
    $this->assertDatabaseHas('reader_notifications', ['user_id' => $reader->id, 'type' => 'quiz_failed']);
    $this->assertDatabaseHas('reader_notification_preferences', ['user_id' => $reader->id, 'type' => 'duel_invitation', 'in_app_enabled' => false]);
});

test('android duel invitations and responses notify the other reader', function () {
    $challenger = User::factory()->create();
    $opponent = User::factory()->create();
    $book = Book::create([
        'title' => 'Duel Notification Book',
        'slug' => 'duel-notification-book',
        'description' => 'A book for notification tests.',
        'publication_year' => 2025,
        'page_count' => 150,
        'language' => 'en',
        'isbn' => '9780000000201',
        'featured' => false,
        'status' => 'published',
    ]);
    $quiz = Quiz::create([
        'book_id' => $book->id,
        'title' => 'Duel verification quiz',
        'instructions' => 'The quiz was passed by both readers.',
        'pass_mark' => 70,
        'attempt_limit' => 3,
        'duration_minutes' => 10,
        'status' => 'published',
    ]);
    QuizAttempt::insert([
        ['quiz_id' => $quiz->id, 'user_id' => $challenger->id, 'score' => 90, 'passed' => true, 'created_at' => now(), 'updated_at' => now()],
        ['quiz_id' => $quiz->id, 'user_id' => $opponent->id, 'score' => 85, 'passed' => true, 'created_at' => now(), 'updated_at' => now()],
    ]);

    $challenge = $this->actingAs($challenger, 'sanctum')->postJson('/api/duels', [
        'book_id' => $book->id,
        'opponent_id' => $opponent->id,
    ])->assertCreated();
    $duelId = $challenge->json('data.id');

    $this->assertDatabaseHas('reader_notifications', [
        'user_id' => $opponent->id,
        'type' => 'duel_invitation',
    ]);

    $this->actingAs($opponent, 'sanctum')->patchJson('/api/duels/'.$duelId.'/respond', ['action' => 'accept'])->assertOk();

    $this->assertDatabaseHas('reader_notifications', [
        'user_id' => $challenger->id,
        'type' => 'duel_response',
    ]);
});
