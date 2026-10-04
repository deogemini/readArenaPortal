<?php

use App\Models\Book;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Support\Carbon;

test('leaderboards rank the best passing quiz score once per quiz', function () {
    $first = User::factory()->create(['name' => 'A Reader']);
    $second = User::factory()->create(['name' => 'B Reader']);
    $book = Book::create(['title' => 'Ranked Book', 'slug' => 'ranked-book', 'status' => 'published']);
    $quiz = Quiz::create(['book_id' => $book->id, 'title' => 'Quiz A', 'status' => 'published']);
    $secondQuiz = Quiz::create(['book_id' => $book->id, 'title' => 'Quiz B', 'status' => 'published']);

    QuizAttempt::create(['quiz_id' => $quiz->id, 'user_id' => $first->id, 'score' => 85, 'passed' => true]);
    QuizAttempt::create(['quiz_id' => $quiz->id, 'user_id' => $first->id, 'score' => 96, 'passed' => true]);
    QuizAttempt::create(['quiz_id' => $secondQuiz->id, 'user_id' => $first->id, 'score' => 90, 'passed' => true]);
    QuizAttempt::create(['quiz_id' => $quiz->id, 'user_id' => $second->id, 'score' => 100, 'passed' => true]);
    QuizAttempt::create(['quiz_id' => $secondQuiz->id, 'user_id' => $second->id, 'score' => 100, 'passed' => false]);

    $this->get(route('leaderboard', ['period' => 'all_time']))
        ->assertOk()
        ->assertSee('A Reader')
        ->assertSee('186')
        ->assertSee('B Reader');

    $this->actingAs($first, 'sanctum')->getJson('/api/leaderboard?period=all_time')
        ->assertOk()
        ->assertJsonPath('data.0.name', 'A Reader')
        ->assertJsonPath('data.0.points', 186)
        ->assertJsonPath('data.0.verified_quizzes', 2)
        ->assertJsonPath('meta.period', 'all_time');
});

test('time-based leaderboard periods only count passing quizzes inside their date range', function () {
    Carbon::setTestNow(Carbon::parse('2026-10-04 12:00:00'));
    $reader = User::factory()->create();
    $book = Book::create(['title' => 'Weekly Book', 'slug' => 'weekly-book', 'status' => 'published']);
    $quiz = Quiz::create(['book_id' => $book->id, 'title' => 'Weekly Quiz', 'status' => 'published']);
    QuizAttempt::create([
        'quiz_id' => $quiz->id,
        'user_id' => $reader->id,
        'score' => 90,
        'passed' => true,
        'created_at' => now()->subDays(10),
        'updated_at' => now()->subDays(10),
    ]);
    \Illuminate\Support\Facades\DB::table('quiz_attempts')
        ->where('user_id', $reader->id)
        ->update(['created_at' => now()->subDays(10)]);

    $this->actingAs($reader, 'sanctum')->getJson('/api/leaderboard?period=weekly')
        ->assertOk()
        ->assertJsonCount(0, 'data');

    $this->get(route('leaderboard', ['period' => 'unknown']))->assertSessionHasErrors('period');
    Carbon::setTestNow();
});
