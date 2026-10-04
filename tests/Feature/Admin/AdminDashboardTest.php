<?php

use App\Models\Book;
use App\Models\LiveShow;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;

test('admin dashboard reports real activity instead of sample events', function () {
    $admin = User::factory()->admin()->create();
    $reader = User::factory()->create(['name' => 'Real Reader']);
    $book = Book::create(['title' => 'Real Book', 'slug' => 'real-admin-book', 'status' => 'published']);
    $quiz = Quiz::create(['book_id' => $book->id, 'title' => 'Real Quiz', 'status' => 'published']);
    QuizAttempt::create(['quiz_id' => $quiz->id, 'user_id' => $reader->id, 'score' => 90, 'passed' => true]);
    LiveShow::create(['title' => 'Real Salon', 'book_id' => $book->id, 'start_at' => now()->addWeek(), 'status' => 'scheduled']);

    $this->actingAs($admin)->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Real Reader scored 90% on Real Quiz')
        ->assertSee('Real Salon')
        ->assertDontSee('Beloved staged for review')
        ->assertDontSee('The Wednesday Salon confirmed');
});
